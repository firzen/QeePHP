<?php
// $Id: pgsql.php 2159 2009-01-25 12:43:25Z dualface $

/**
 * 定义 QDB_Adapter_Pgsql 类
 *
 * @link http://qeephp.com/
 * @copyright Copyright (c) 2006-2009 Qeeyuan Inc. {@link http://www.qeeyuan.com}
 * @license New BSD License {@link http://qeephp.com/license/}
 * @version $Id: pgsql.php 2159 2009-01-25 12:43:25Z dualface $
 * @package database
 */

/**
 * 定义 QDB_Adapter_Pgsql 类
 *     这个是参考QDB_Adpter_Mysql 及 FLEA_Db_Driver_Pgsql 类修改而来，并根据具体情况进行了一些修改。
 *
 * @author  Abin30@163.com, yangyi.cn.gz@gmail.com
 * @version $Id: pgsql.php 2159 2009-01-25 12:43:25Z dualface $
 * @package database
 */
class QDB_Adapter_Pgsql extends QDB_Adapter_Abstract
{
    protected $_bind_enabled = false;

    protected $_savepoint_enabled = true;

    /**
     *  保存最后一次查询的资源ID
     *
     * @var  Resource
     */
    protected  $_lastrs= null ;
    function __construct($dsn, $id)
    {
        if (!is_array($dsn)) {
            $dsn = QDB::parseDSN($dsn);
        }
        parent::__construct($dsn, $id);

        if (isset($dsn['schema']) && !empty($dsn['schema'])) { $this->_schema = $dsn['schema']; }
    }

    function connect($pconnect = false, $force_new = false)
    {
        if (is_resource($this->_conn)) { return; }

        $this->_last_err = null;
        $this->_last_err_code = null;

        $dsnstring = '';
        if (isset($this->_dsn['host'])) {
            $dsnstring = 'host=' . $this->_addslashes($this->_dsn['host']);
        }
        if (isset($this->_dsn['port'])) {
            $dsnstring .= ' port=' . $this->_addslashes($this->_dsn['port']);
        }
        if (isset($this->_dsn['login'])) {
            $dsnstring .= ' user=' . $this->_addslashes($this->_dsn['login']);
        }
        if (isset($this->_dsn['password'])) {
            $dsnstring .= ' password=' . $this->_addslashes($this->_dsn['password']);
        }
        if (isset($this->_dsn['database'])) {
            $dsnstring .= ' dbname=' . $this->_addslashes($this->_dsn['database']);
        }
        //$dsnstring .= ' ';

        if ($pconnect) {
            $this->_conn = pg_pconnect($dsnstring);
        } else {
            $this->_conn = pg_connect($dsnstring);
        }
        if (!is_resource($this->_conn)) {
            throw new QDB_Exception('CONNECT DATABASE', pg_errormessage(), 0);
        }
        //if (!$this->execute("set datestyle='ISO'")) { return false; }
        $charset = $this->_dsn['charset'];
        if (strtoupper($charset) == 'GB2312') { $charset = 'GBK'; }
        if ($charset != '') {
            pg_set_client_encoding($this->_conn, $charset);
        }
    }

    function pconnect()
    {
        $this->connect(true);
    }

    function nconnect()
    {
        $this->connect(false, true);
    }

    function close()
    {
        if (is_resource($this->_conn)) { pg_close($this->_conn); }
        parent::close();
    }

    function identifier($name) {
        return ($name != '*') ? "\"{$name}\"" : '*';
    }

    function qstr($value)
    {
        if (is_array($value))
        {
            foreach ($value as $offset => $v)
            {
                $value[$offset] = $this->qstr($v);
            }
            return $value;
        }
        if (is_int($value) || is_float($value)) { return $value; }
        if (is_bool($value)) { return $value ? $this->_true_value : $this->_false_value; }
        if (is_null($value)) { return $this->_null_value; }
        return "'" . pg_escape_string($value) . "'";
    }

    function qtable($table_name, $schema = null, $alias = null)
    {
        if (strpos($table_name, '.') !== false) {
            $parts = explode('.', $table_name);
            $table_name = $parts[1];
            $schema = $parts[0];
        }
        $table_name = trim($table_name, '"');
        $schema = trim($schema, '"');
        //public 是默认的schema
        if (strtoupper($schema) == 'PUBLIC') { $schema=''; }
        $i = $schema != '' ? "\"{$schema}\".\"{$table_name}\"" : "\"{$table_name}\"";
        return empty($alias) ? $i : $i . " \"{$alias}\"";
    }

    function qfield($field_name, $table_name = null, $schema = null, $alias = null)
    {
        $field_name = trim($field_name,'"');

        if (strpos($field_name, '.') !== false) {
            $parts = explode('.', $field_name);
            if (isset($parts[2])) {
                $schema = $parts[0];
                $table_name = $parts[1];
                $field_name = $parts[2];
            } elseif (isset($parts[1])) {
                $table_name = $parts[0];
                $field_name = $parts[1];
            }
        }

        $field_name = ($field_name == '*') ? '*' : "\"{$field_name}\"";

        if (!empty($table_name)) {
            $field_name = $this->qtable($table_name, $schema) . '.' . $field_name;
        }

        return empty($alias) ? $field_name : "{$field_name} AS \"{$alias}\"";
    }

    function nextID($tablename, $fieldname , $schema = null, $start_value = 1)
    {
        $seqName = $tablename . '_' . $fieldname . '_seq';
        $next_sql = sprintf("SELECT NEXTVAL('%s')", $seqName);
        $this->insert_id = $this->execute($next_sql)->fetchOne();
        if(empty($this->insert_id)){
            if (!$this->createSeq($seqName, $start_value)) { return false; }
            $this->insert_id = $this->execute($next_sql)->fetchOne();
            if(empty($this->insert_id)) {return false; }
        }
        return $this->insert_id;
    }

    function createSeq($seqname, $start_value = 1)
    {
        return  $this->execute(sprintf('CREATE SEQUENCE %s START %s',$seqname,$start_value));
    }

    function dropSeq($seqname)
    {
        return $this->execute(sprintf('DROP SEQUENCE %s', $seqname));
    }

    function insertID()
    {
        if (!$this->isConnected()) { return false; }

        try {
            $insert_id = $this->execute('SELECT LASTVAL();')->fetchOne();
            return $insert_id;
        } catch (Exception $e) {
            return 0;
        }
    }

    function affectedRows()
    {
        return pg_affected_rows($this->_lastrs);
    }

    /**
     * 执行一个查询，返回一个查询对象或者 boolean 值，出错时抛出异常
     *
     * $sql 是要执行的 SQL 语句字符串，而 $inputarr 则是提供给 SQL 语句中参数占位符需要的值。
     *
     * 如果执行的查询是诸如 INSERT、DELETE、UPDATE 等不会返回结果集的操作，
     * 则 execute() 执行成功后会返回 true，失败时将抛出异常。
     *
     * 如果执行的查询是 SELECT 等会返回结果集的操作，
     * 则 execute() 执行成功后会返回一个 DBO_Result 对象，失败时将抛出异常。
     *
     * QDB_Result_Abstract 对象封装了查询结果句柄，而不是结果集。
     *
     * @param string $sql
     * @param array $inputarr
     *
     * @return QDB_Result_Abstract
     */
    function execute($sql, $inputarr = null)
    {
        if (is_array($inputarr)) {
            $sql = $this->_fakebind($sql, $inputarr);
        }
        if (!$this->_conn) { $this->connect(); }

        //print_R($sql);
        $this->_lastrs = @pg_query($this->_conn, $sql);
        if ($this->_log_enabled) { QLog::log($sql, QLog::DEBUG); }

        if (is_resource($this->_lastrs)) {
            Q::loadClass('Qdb_Result_Pgsql');
            return new QDB_Result_Pgsql($this->_lastrs, $this->_fetch_mode);
        } elseif ($this->_lastrs) {
            $this->_last_err = null;
            $this->_last_err_code = null;
            return $this->_lastrs;
        } else {
            $this->_last_err = pg_errormessage($this->_conn);
            $this->_last_err_code = null ;
            $this->_has_failed_query = true;
            throw new QDB_Exception($sql, $this->_last_err, $this->_last_err_code);
        }
    }

    function selectLimit($sql, $offset = 0, $length = 30, array $inputarr = null)
    {
        if (strtoupper($length) != 'ALL') { $length = (int)$length; }
        $sql = sprintf('%s LIMIT %s OFFSET %d', $sql, $length, $offset);
        return $this->execute($sql, $inputarr);
    }

    /**
     * 启动事务
     */
    function startTrans()
    {
        if (!$this->_transaction_enabled) { return false; }

        if ($this->_trans_count == 0) {
            $this->execute('BEGIN;');
            $this->_has_failed_query = false;
        } elseif ($this->_trans_count && $this->_savepoint_enabled) {
            $savepoint = 'savepoint_'. $this->_trans_count;
            $this->execute("SAVEPOINT {$savepoint};");
            array_push($this->_savepoints_stack, $savepoint);
        }

        ++$this->_trans_count;
        return true;
    }

    /**
     * 完成事务，根据查询是否出错决定是提交事务还是回滚事务
     *
     * 如果 $commit_on_no_errors 参数为 true，当事务中所有查询都成功完成时，则提交事务，否则回滚事务
     * 如果 $commit_on_no_errors 参数为 false，则强制回滚事务
     *
     * @param boolean $commit_on_no_errors 指示在没有错误时是否提交事务
     */
    function completeTrans($commit_on_no_errors = true)
    {
        if ($this->_trans_count == 0)
        {
            return;
        }

        -- $this->_trans_count;
        if ($this->_trans_count == 0)
        {
            if ($this->_has_failed_query == false && $commit_on_no_errors)
            {
                $this->execute('COMMIT');
            }
            else
            {
                $this->execute('ROLLBACK');
            }
        }
        elseif ($this->_savepoint_enabled)
        {
            $savepoint = array_pop($this->_savepoints_stack);
            if ($this->_has_failed_query || $commit_on_no_errors == false)
            {
                $this->execute("ROLLBACK TO SAVEPOINT {$savepoint}");
            }
            else
            {
                $this->execute("RELEASE SAVEPOINT {$savepoint}");
            }
        }
    }

    /**
     * 返回指定数据表（或者视图）的元数据
     *
     * 返回的结果是一个二维数组，每一项为一个字段的元数据。
     * 每个字段包含下列属性：
     *
     * - name:            字段名
     * - scale:           小数位数
     * - type:            字段类型
     * - ptype:           简单字段类型（与数据库无关）
     * - length:          最大长度
     * - not_null:        是否不允许保存 NULL 值
     * - pk:              是否是主键
     * - auto_incr:       是否是自动增量字段
     * - binary:          是否是二进制数据
     * - unsigned:        是否是无符号数值
     * - has_default:     是否有默认值
     * - default:         默认值
     * - desc:            字段描述
     *
     * @param string $table_name
     * @param string $schema
     *
     * @return array
     */
    function metaColumns($table_name, $schema = null)
    {
        if (strpos($table_name, '.') !== false) {
            $result = explode('.', $table_name);
            $schema = trim($result[0], '"');
            $table_name = trim($result[1], '"');
        }

        static $typeMap = array(
            'money' => 'c',
            'interval' => 'c',
            'char' => 'c',
            'character' => 'c',
            'varchar' => 'c',
            'name' => 'c',
            'bpchar' => 'c',
            '_varchar' => 'c',
            'inet' => 'c',
            'macaddr' => 'c',
            'text' => 'x',
            'image' => 'b',
            'blob' => 'b',
            'bit' => 'b',
            'varbit' => 'b',
            'bytea' => 'b',
            'bool' => 'l',
            'boolean' => 'l',
            'date' => 'd',
            'timestamp without time zone' => 't',
            'time' => 't',
            'datetime' => 't',
            'timestamp' => 't',
            'timestamptz' => 't',
            'smallint' => 'i',
            'bigint' => 'i64',
            'integer' => 'i',
            'int8' => 'i',
            'int4' => 'i',
            'int2' => 'i',
            'oid' => 'r',
            'serial' => 'r',
            'float'  => 'n',
            'float4' =>'n',
            'double' => 'n',
            'float8' =>'n',
            'uuid' => 'c',
            'xml' => 'x',
        );

        $table_name = trim($table_name, '"');
        $keys = $this->getAll(sprintf("SELECT ic.relname AS index_name, a.attname AS column_name,i.indisunique AS unique_key, i.indisprimary AS primary_key FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid AND (i.indkey[0] = a.attnum OR i.indkey[1] = a.attnum OR i.indkey[2] = a.attnum OR i.indkey[3] = a.attnum OR i.indkey[4] = a.attnum OR i.indkey[5] = a.attnum OR i.indkey[6] = a.attnum OR i.indkey[7] = a.attnum) AND a.attrelid = bc.oid AND (bc.relname = '%s' or bc.relname=lower('%s'))", $table_name, $table_name));

        $rsdefa = array();
        $sql = sprintf("SELECT d.adnum as num, d.adsrc as def from pg_attrdef d, pg_class c where d.adrelid=c.oid and (c.relname='%s' or c.relname=lower('%s')) order by d.adnum", $table_name, $table_name);
        $rsdef = $this->getAll($sql);

        if (count($rsdef)>0) {
            foreach ($rsdef as $row) {
                $num = $row['num'];
                $def = $row['def'];
                if (strpos($def, '::') === false && strpos($def, "'") === 0) {
                    $def = substr($def, 1, strlen($def) - 2);
                }
                $rsdefa[$num] = $def;
            }
            unset($rsdef);
        }
        if (!empty($schema)) {
            $rs = $this->execute(sprintf("SELECT a.attname, t.typname, a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum FROM pg_class c, pg_attribute a, pg_type t, pg_namespace n WHERE relkind in ('r','v') AND (c.relname='%s' or c.relname = lower('%s')) and c.relnamespace=n.oid and n.nspname='%s' and a.attname not like '....%%' AND a.attnum > 0 AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum", $table_name, $table_name, $schema));
        }else{
            $rs = $this->execute(sprintf("SELECT a.attname,t.typname,a.attlen,a.atttypmod,a.attnotnull,a.atthasdef,a.attnum FROM pg_class c, pg_attribute a,pg_type t WHERE relkind in ('r','v') AND (c.relname='%s' or c.relname = lower('%s')) and a.attname not like '....%%' AND a.attnum > 0 AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum ", $table_name, $table_name));
        }
        /* @var $rs QDB_Result_Abstract */
        $retarr = array();
        $cnt111 = 0;
        $rs->fetchMode = QDB::FETCH_MODE_ARRAY;
        while ($row = $rs->fetchRow()) {
            $field = array();
            $field['default'] = '';
            $field['name'] = $row['attname'];
            $field['type'] = strtolower($row['typname']);
            $field['length'] = $row['attlen'];
            $field['attnum'] = $row['attnum'];
            if ($field['length'] <= 0) {
                $field['length'] = $row['atttypmod'] - 4;
            }
            if ($field['length'] <= 0) {
                $field['length'] = -1;
            }
            $field['scale'] = null;
            if ($field['type'] == 'numeric') {
                $field['scale'] = $field['length'] & 0xFFFF;
                $field['length'] >>= 16;
            }

            $field['has_default'] = ($row['atthasdef'] == 't');
            if ($field['has_default']) {
                $field['default'] = $rsdefa[$row['attnum']];
            }
            else
            $field['default'] = null;
            $field['not_null'] = ($row['attnotnull'] == 't');
            $field['pk'] = false;
            $field['unique'] = false;
            if (is_array($keys)) {
                foreach($keys as $key) {
                    if ($field['name'] == $key['column_name'])
                        $field['pk']=($key['primary_key'] == 't');
                    if ($field['name'] == $key['column_name'] )
                        $field['unique'] = ( $key['unique_key'] == 't');
                }
            }
            // 这里要对几种特殊的类型的默认值进行处理
            $field['ptype'] = $typeMap[strtolower($field['type'])];
            // 这里是为了配合解决无法取得InsertID的情况。
            if ($field['ptype'] == 'r' || ($field['ptype'] == 'i' && strpos($field['default'],'nextval') !== false)) {
                $field['has_default'] = false;
                $field['default'] = null;
            }

            $field['auto_incr'] = false;
            $field['binary'] = ($field['ptype']=='b');
            $field['unsigned'] = false ;
            if (!$field['binary'] ) {
                $d = $field['default'];
                if ($d != '' && $d != 'NULL') {
                    $field['has_default'] = true;
                    $field['default'] = $d;
                } else {
                    $field['has_default'] = false;
                }
            }
            $field['desc'] = '';
            $retarr[strtolower($field['name'])] = $field;
        }
        return $retarr;
    }

    function metaTables($pattern = null, $schema = null)
    {
        if (!empty($schema)) {
            $sql = sprintf("select relname from pg_class c, pg_namespace n WHERE c.relname !~ '^(pg_|sql_)' and c.relkind = 'r' and c.relnamespace = n.oid and n.nspname = %s", $this->qstr($schema));
        }else{
            $sql = "select relname from pg_class as c WHERE relkind = 'r' and relname !~ '^(pg_|sql_)'";
        }

        if (!empty($pattern)) {
            $sql .= sprintf(' AND (c.relname like %s or c.relname like %s)', $this->qstr($pattern), $this->qstr(strtolower($pattern)));
        }

        return $this->getCol($sql);
    }

    protected function _fakebind($sql, $inputarr)
    {
        $arr = explode('?', $sql);
        $sql = array_shift($arr);
        foreach ($inputarr as $value) {
            if (isset($arr[0])) {
                $sql .= $this->qstr($value) . array_shift($arr);
            }
        }
        return $sql;
    }

    /**
     *  按照 PostgreSQL 的要求转义 DSN 字符串参数
     *
     * @param string $s
     *
     * @return string
     */
    protected function _addslashes($s)
    {
        $len = strlen($s);
        if ($len == 0) return "''";
        if (strncmp($s,"'",1) === 0 && substr($s,$len-1) == "'") return $s; // already quoted
        return "'".addslashes($s)."'";
    }

}

