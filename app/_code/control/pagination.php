<?php
class Control_Pagination extends QUI_Control_Abstract
{
    function render()
    {
        $pagination = $this->pagination;
        $udi        = $this->get('udi', $this->_context->requestUDI());
        $length     = $this->get('length', 9);
        $slider     = $this->get('slider', 2);
        $prev_label = $this->get('prev_label', '');
        $next_label = $this->get('prev_label', '');
        $url_args   = $this->get('url_args');
        $attr		= $this->get('attr');
        
        if (is_null($url_args)){
        	$url_args=array_merge($_GET,$_POST);
        	unset($url_args[QContext::UDI_CONTROLLER]);
        	unset($url_args[QContext::UDI_ACTION]);
        	unset($url_args[QContext::UDI_MODULE]);
        	unset($url_args['page']);
        }
        if ($this->_extract('nofloat')){
        	$out = "<div class=\"pagination\">\n";
        }else {
        	$out = "<br><div class=\"pagination \">\n";
        }

        if ($this->get('show_count'))
        {
            $out .= "<p>共 {$pagination['record_count']} 个条目</p>\n";
        }
        $out .= '<ul id="' . h($this->id()) . "\">\n";

        $url_args = (array)$url_args;
        if ($pagination['current'] == $pagination['first'])
        {
            $out .= "<li class=\"disabled\"><a>&#171; {$prev_label}</a></li>\n";
        }
        else
        {
            $url_args['page'] = $pagination['prev'];
            $url = url($udi, $url_args);
            $out .= "<li><a href=\"{$url}\" {$attr}>&#171; {$prev_label}</a></li>\n";
        }

        $base = $pagination['first'];
        $current = $pagination['current'];

        $mid = intval($length / 2);
        if ($current < $pagination['first'])
        {
            $current = $pagination['first'];
        }
        if ($current > $pagination['last'])
        {
            $current = $pagination['last'];
        }

        $begin = $current - $mid;
        if ($begin < $pagination['first'])
        {
            $begin = $pagination['first'];
        }
        $end = $begin + $length - 1;
        if ($end >= $pagination['last'])
        {
            $end = $pagination['last'];
            $begin = $end - $length + 1;
            if ($begin < $pagination['first'])
            {
                $begin = $pagination['first'];
            }
        }

        if ($begin > $pagination['first'])
        {
            for ($i = $pagination['first']; $i < $pagination['first'] + $slider && $i < $begin; $i ++)
            {
                $url_args['page'] = $i;
                $in = $i + 1 - $base;
                $url = url($udi, $url_args);
                $out .= "<li><a href=\"{$url}\" {$attr}>{$in}</a></li>\n";
            }

            if ($i < $begin)
            {
                $out .= "<li class=\"none\"><a>...</a></li>\n";
            }
        }

        for ($i = $begin; $i <= $end; $i ++)
        {
            $url_args['page'] = $i;
            $in = $i + 1 - $base;
            if ($i == $pagination['current'])
            {
                $out .= "<li class=\"active\"><a>{$in}</a></li>\n";
            }
            else
            {
                $url = url($udi, $url_args);
                $out .= "<li><a href=\"{$url}\" {$attr}>{$in}</a></li>\n";
            }
        }

        if ($pagination['last'] - $end > $slider)
        {
            $out .= "<li class=\"none\">...</li>\n";
            $end = $pagination['last'] - $slider;
        }

        for ($i = $end + 1; $i <= $pagination['last']; $i ++)
        {
            $url_args['page'] = $i;
            $in = $i + 1 - $base;
            $url = url($udi, $url_args);
            $out .= "<li><a href=\"{$url}\" {$attr}>{$in}</a></li>\n";
        }

        if ($pagination['current'] == $pagination['last'])
        {
            $out .= "<li class=\"disabled\"><a>{$next_label} &#187;</a></li>\n";
        }
        else
        {
            $url_args['page'] = $pagination['next'];
            $url = url($udi, $url_args);
            $out .= "<li><a href=\"{$url}\" {$attr}>{$next_label} &#187;</a></li>\n";
        }
		$out .="<li class=disabled><a>Total: {$pagination['record_count']}</a></li>";
        $out .= "</ul></div>\n";
        return $out;
    }
}
