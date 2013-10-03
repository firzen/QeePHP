<?php
/**
 * @defgroup activerecord ActiveRecord 活动记录模型
 * @ingroup database
 * 
 * 活动记录模型用于封装领域逻辑。
 * 
 * 
 * 
 * <h2>活动记录模型介绍</h2>
 * 
 * 活动记录是 ActiveRecord 设计模式的中文名称。由于活动记录通常用于封装业务逻辑，
 * 所以在 QeePHP 中就把活动记录和 MVC 模式中的 Model （模型）所对应起来，称为活动记录模型，简称“模型”。
 * 
 * 在 QeePHP 中，一个模型对应数据表中的一个记录，记录字段则是模型的属性。
 * 例如用户数据表中的一个记录就是一个模型对象，数据表的 username 字段就是模型的 username 属性。
 * 
 * 活动记录模型与使用数组来存取数据库相比，最主要的好处是可以将数据和行为封装在一起。
 * 
 * 例如系统中的“用户”，其数据包括用户名、密码、电子邮件地址，而行为则包括登录、注销、更改密码等。
 * 使用模型，我们可以把完成这些行为的方法直接写在“用户”类中，而不是放到其他地方。
 * 
 * 由于模型的数据和行为是在一起的，所以我们在操作一个模型时，自然而然的就会受到模型行为的影响。
 * 比如“用户”模型的更改密码行为要求必须提供现有密码才能设置新密码，那么开发者就无法绕过这个规则。
 * 而这样的规则，正是构建一个完整应用程序的逻辑的一部分。
 * 
 * 因此，活动记录模型既能够将整个应用程序的逻辑分散到各个独立的逻辑区域中，又可以确保这些逻辑总是能够起作用。
 * 最终，我们可以得到一个具有清晰逻辑结构和严密规则的应用程序。
 * 
 * 有关活动记录的进一步信息，建议阅读《企业应用架构模式》等面向对象参考书籍。
 * 
 * 
 * 
 * <h2>定义一个模型</h2>
 * 
 * 如何正确的将应用程序逻辑分散到不同的对象中，是一项困难的任务。这部分的知识也许需要一本书的篇幅才能阐述清楚。
 * 因此，这里我们不深入理论基础，而是来了解如何在 QeePHP 应用中定义一个模型。
 * 
 * <strong>模型的基本组成：数据 + 行为。</strong>
 * 
 * 因此我们首先需要在数据库中创建一个表来存储某个类型模型的所有对象实例。
 * 例如“members”表存储所有“用户”模型对象的数据。而模型的行为则定义在模型的类中。
 * 
 * 在创建数据表时，可以简单的按照模型的属性名来确定数据表的字段名。
 * 这样在维护应用程序时，看到模型的属性名就能知道对应数据表的字段名，反之亦然。
 * 
 * 从实践角度出发，应该为每一个数据表添加一个主键字段。主键字段可以确保更准确的检索模型对象。
 * 比较常用的主键名有 id、user_id 等样式。
 * 
 * 
 * <h3>一个数据表示例</h3>
 * 
 * Member 模型有 username、password 和 email 三个属性，则存储 Member 模型的数据表结构可能如下：
 * 
 * <ul>
 *   <li><strong>id:</strong> INT 主键字段</li>
 *   <li><strong>username:</strong> VARCHAR(30) 存储用户名</li>
 *   <li><strong>password:</strong> VARCHAR(64) 存储用户密码</li>
 *   <li><strong>email:</strong> VARCHAR(80) 存储电子邮件</li>
 *   <li><strong>created:</strong> INT 存储对象的创建时间</li>
 *   <li><strong>updated:</strong> INT 存储对象的最后更新时间</li>
 * </ul>
 * 
 * 多出来的 created 和 updated 会由 QeePHP 自动填充值，用于记录对象的创建和更新时间。
 * 
 * 
 * <h3>创建模型的类定义文件</h3>
 * 
 * 由于模型的类定义文件内容较多，因此通常使用 QeePHP 的代码生成工具来生成，例如 WebSetup 的代码生成器。
 * 
 * 如果需要手工创建，可以按照下列格式创建模型的类定义文件：
 * 
 * @code
 * <?php
 * 
 * class Member extends QDB_ActiveRecord_Abstract
 * {
 *     static function __define()
 *     {
 *          return array
 *          (
 *              'table_name' => 'members',
 *          );
 *     }
 * 
 *     static function find()
 *     {
 *          $args = func_get_args();
 *          return QDB_ActiveRecord_Meta::instance(__CLASS__)->findByArgs($args);
 *     }
 *
 *     static function meta()
 *     {
 *          return QDB_ActiveRecord_Meta::instance(__CLASS__);
 *     }
 * }
 * 
 * @endcode
 * 
 * 在上述代码中，__define() 方法中的 'table_name' => 'members' 明确了这个模型使用哪一个数据表来存储模型对象的数据。
 * 对于一个模型，必须提供上述的代码。
 * 
 * 不过，除非有特殊需要，否则强烈建议使用代码生成器来创建模型的类定义文件。
 * 因为代码生成器会根据存储模型的数据表结构自动完成模型的验证规则等设置，减少了开发者的工作量。
 *
 * 
 * 
 * <h2>模型的创建、读取、更新和删除操作</h2>
 * 
 * 模型定义出来后，如果不能保存到数据库中，那么是没有意义的。
 * 
 * 
 * <h3>创建模型对象实例</h3>
 * 
 * 既然模型是一个对象，那么我们构造一个新的模型对象即可。
 * 
 * 示例：
 * 
 * @code
 * $post = new Post();
 * $post->title = 'new post tilte - ' . mt_rand();
 * $post->body  = 'new post body - ' . mt_rand();
 * @endcode
 * 
 * 上述代码首先构造了一个新的 Post 对象，然后修改了这个对象的 title 和 body 属性。
 * 但是此时对象只是存在于内存中，还没有写入数据库。所以程序一执行完毕，这个对象就消失了。
 * 因此，如果需要让一个对象能够“永久”存在，就需要将其保存到数据库中。
 * 
 * 要将对象保存到数据库，使用下列代码：
 * 
 * @code
 * $post->save();
 * @endcode
 * 
 * save() 方法将对象保存到数据库。至于保存时是新建一个数据库记录，还是更新已有的记录，QeePHP 会自动判断。
 * 
 * 
 * <h3>查询数据库中的模型对象实例</h3>
 * 
 * 要从数据库中查询出模型，使用模型的 QDB_ActiveRecord_Abstract::find() 方法。
 * 
 * 不过 QDB_ActiveRecord_Abstract::find() 方法本身并不完成查询操作。
 * 它仅仅是返回一个 QDB_Select 对象。而 QDB_Select 对象才执行实际的查询操作，并返回结果。
 * 
 * <strong>发起一个查询</strong>
 * 
 * 与其他大部分框架不同，在 QeePHP 中，QDB_ActiveRecord_Abstract::find() 方法是一个静态方法，所以调用方式如下：
 * 
 * @code
 * $post = Post::find(....)->query();
 * @endcode
 * 
 * QDB_ActiveRecord_Abstract::find() 方法本身可以带有参数，这些参数将作为查询条件的一部分。
 * QDB_ActiveRecord_Abstract::find() 方法返回的 QDB_Select 对象则提供了更多的方法来操作查询。
 * 例如排序、限定查询结果数量、分页等等。
 * 
 * 不管调用了 QDB_Select 对象的哪些方法，最终必须调用 QDB_Select::query() 方法来实际执行这个查询，并获得查询结果。
 * 
 * <strong>控制查询操作的行为</strong>
 * 
 * 由于 QDB_Select 支持连贯接口，所以可以采用如下的书写格式来进行查询操作：
 * 
 * @code
 * Post::find('created > ?', time() – 60 * 5)->all()->order('created DESC')->query();
 * @endcode
 * 
 * 上述代码查询所有 created 属性（既对象保存到数据库的时间）在最近 5 分钟以内的模型。
 * 并且按照 created 属性从大到小排序（最近时间的模型排在查询结果最前面）。
 * 而 QDB_Select::all() 方法则指示查询所有符合条件的模型，如果不指定该方法，则只查询第一个符合条件的模型。
 * 
 * <strong>指定查询条件</strong>
 * 
 * 这个例子中，我们为 QDB_ActiveRecord_Abstract::find() 方法提供了一些参数，这些参数将作为查询条件来限定查询的结果集。
 * 
 * find() 方法的参数是可变的，例如：
 * 
 * @code
 * Post::find('title = ?', $title)
 * Post::find('title = ? OR created > ?', $title, $created)
 * @endcode
 * 
 * find() 方法的第一个参数是查询条件。如果查询条件中有“?”，则表示此处是一个参数占位符。
 * 因此还要在 find() 方法中提供该占位符对应的参数值。
 * 
 * find() 方法还可以使用数组作为查询条件：
 * 
 * @code
 * Post::find(array('title' => $title))
 * // 这行代码等同于
 * Post::find('title = ?', $title)
 * @endcode
 * 
 * 数组中如果有多个参数，则每个查询参数之间都是“AND”关系：
 * 
 * @code
 * Post::find(array('title' => $title, 'body' => $body))
 * // 等同于
 * Post::find('title = ? AND body = ?', $title, $body)
 * @endcode
 * 
 * find() 方法还支持更复杂的查询条件格式，详细信息参考 QDB_Select::find() 方法的说明。
 * 
 * <strong>限定查询结果集大小</strong>
 * 
 * 虽然用 QDB_Select::all() 方法可以获得所有符合条件的结果。
 * 但如果我们只需要特定数量的结果，就必须使用 QDB_Select::limit() 方法。
 * 
 * QDB_Select::limit() 方法有两个参数 $count 和 $offset。分别表示要获取多少个结果和从什么位置开始返回结果。
 * 
 * 例如：
 * 
 * @code
 * // 仅查询 5 个 Post 模型
 * Post::find()->limit(5)->query();
 * // 按照 created 属性反向排序，返回从第 4 个结果开始的 5 个模型
 * Post::find()->order('created DESC')->limit(5, 3)->query();
 * @encode
 * 
 * 注意：QDB_Select::limit() 方法的 $offset 参数是从 0 开始计算的。
 * 所以 $offset = 3 时，表示从第 4 个结果开始返回。
 * 
 * 除了 QDB_Select::limit() 方法，还有 QDB_Select::limitPage() 方法可以限定结果集大小。
 * QDB_Select::limitPage($page, $page_size) 以页为单位分割查询结果集，并返回指定页的结果。
 * 
 * 例如：
 * 
 * @code
 * // 按照每页 10 个记录分割结果集，并返回第 3 页的结果
 * Post::find()->limitPage(3, 10)->query()
 * @endcode
 * 
 * 注意：QDB_Select::limitPage() 的 $page 参数是从 1 开始计算的。这点和 QDB_Select::limit() 方法的 $offset 参数不同。
 * 
 * 更多有关查询操作的内容，请参考 QDB_Select::limit() 和 QDB_Select::limitPage() 方法的说明。
 * 
 * 
 * <h3>将对模型的修改保存到数据库</h3>
 * 
 * 要更新一个模型，通常需要将模型从数据库中查询出来，然后修改模型对象的属性。
 * 最后调用 QDB_ActiveRecord_Abstract::save() 方法保存修改。
 * 
 * 示例：
 * 
 * @code
 * $post = Post::find('post_id = ?', $post_id)->query();
 * $post->title = 'new title – ' .mt_rand();
 * $post->save();
 * @endcode
 * 
 * 
 * <h3>删除数据库中的模型</h3>
 * 
 * 如果是没有保存到内存中的模型，那么在执行结束时会自动销毁。
 * 如果要从数据库中删除一个模型，可以先把模型从数据库中查询出来，然后调用模型的 QDB_ActiveRecord_Abstract::destroy() 方法。
 * 
 * 示例：
 * 
 * @code
 * $post = Post::find(...)->query();
 * $post->destroy();
 * @endcode
 * 
 * 批量销毁模型：
 * 
 * @code
 * Post::meta()->destroyWhere(....);
 * @endcode
 * 
 * QDB_ActiveRecord_Meta::destroyWhere() 方法的参数同 QDB_ActiveRecord_Meta::find() 方法，用于指定删除条件。
 * 有时候我们希望直接删除模型，而不是先查询再删除。这时可以使用下列代码：
 * 
 * @code
 * Post::meta()->deleteWhere(.....);
 * @endcode
 * 
 * QDB_ActiveRecord_Meta::deleteWhere() 和 QDB_ActiveRecord_Meta::destroyWhere() 的参数格式是完全一致的。
 * 区别在于 QDB_ActiveRecord_Meta::deleteWhere() 不会先查询模型，效率较高。
 * 不过具体应该使用哪种销毁方式，应该根据模型的实际情况来决定。
 * 
 * 
 * 
 * <h2>模型的验证</h2>
 * 
 * 要使用 ActiveRecord 的自动验证功能，必须在 QDB_ActiveRecord_Abstract 继承类的 __define() 
 * 方法中为需要验证的属性指定验证策略。
 * 
 * 验证策略的书写规范：
 * 
 * @code
 * '属性名1' => array(
 *      'allow_null'  => false,
 *      'allow_blank' => false,
 * 
 *      array('is_lower', '属性 X 只能由小写字母组成'),
 *      array('max_length', 30, '属性 X 的值，长度不能超过 30 个字符'),
 *      
 * ),
 * 
 * '属性名2' => array(
 *      array('is_int', '属性 Y 必须是一个整数'),
 *      array('equal', 5, '属性 Y 必须等于 5'),
 * ),
 * @endcode
 * 
 * 每个属性的验证策略由两部分组成：验证选项和验证规则。
 * 
 * 验证选项都是可选的，如果没有指定就使用该选项的默认值。可用的验证选项有：
 * 
 * <ul>
 *   <li><strong>allow_null:</strong> 是否允许该属性的值为 NULL，默认设置为 false。<br />
 *       该选项为 true 时，如果属性值是 null，则会忽略该属性的验证规则。
 *   </li>
 * 
 *   <li><strong>allow_blank:</strong> 是否允许该属性的值为空字符串，默认设置为 false。<br />
 *       该选项为 true 时，如果属性值是空字符串，则会忽略该属性的验证规则。
 *       要注意的是，数值 0 是不会被视为空字符串的。
 *   </li>
 * 
 *   <li><strong>check_all_rules:</strong> 指示验证时是否使用所有的验证规则，默认设置为 true。<br />
 *       如果该选项为 true，则验证时，不管该属性的验证规则中是否有验证失败的，该属性的其他验证规则都会被使用。
 *       如果该选项为 false，则写在前面的验证规则如果是没有通过，则后续的验证规则就不再使用。
 *   </li>
 * </ul>
 *
 * 验证选项之后，是多条验证规则，每个验证规则使用一个数组来表示。
 * 
 * 验证规则的书写格式是：
 * 
 * @code
 * array('验证方法', [验证方法需要的参数], ['错误信息'])
 * @endcode
 * 
 * 大多数验证方法并不需要参数，所以验证方法参数是可选的。
 * 但如果该验证方法需要参数，就必须按照该验证方法需要的参数个数来指定。
 * 
 * 所有可用的验证方法都由 QValidator 类提供。<br />
 * <strong>注意：</strong>
 * 对于 QValidator::lessOrEqual() 这样的方法名，在放入验证规则时，必须写成“less_or_equal”。
 * 
 * 每个验证规则除了验证方法和参数，还可以指定一个错误信息。当这条规则验证失败时，指定的错误信息将会被使用。 
 * 如果没有指定验证规则的错误信息，则会使用 'message' 选项指定的错误消息。
 * 
 * 验证规则可以有多个，从而实现对属性的多重验证。验证规则在验证时的使用顺序和其书写顺序一致。
 * 不过当 check_all_rules 选项为 false 时，写在前面的验证规则如果没有通过，后续的验证规则就会被忽略。
 *
 * 
 * <h3>捕获验证失败时抛出的异常</h3>
 * 
 * 验证策略会在调用 QDB_ActiveRecord_Abstract::save() 和 QDB_ActiveRecord_Abstract::validate()，
 * 以及 QDB_ActiveRecord_Meta::validate() 方法时使用。
 * 
 * 如果任何一个属性没有通过验证，就会抛出一个 QValidator_Exception_Failed 异常。
 * 该异常包含了没有通过验证的属性名、没有通过的验证规则，以及被验证的对象等信息。
 * QValidator_Exception_Failed 还提供了多个方法来确定验证失败的属性和验证规则等。
 * 
 * 所以在调用 QDB_ActiveRecord_Abstract::save() 等方法时，可以通过捕获异常的方式来处理验证错误：
 * 
 * @code
 * try {
 *     $user->save(); // 假设 $user 是一个 QDB_ActiveRecord_Abstract 继承类实例
 * } catch (QValidator_Exception_Failed $ex) {
 *     echo $ex->__toString(); // 显示验证错误信息
 * }
 * @endcode
 *
 *  
 * <h3>更灵活的自动验证</h3>
 *
 * 默认的验证策略会在 ActiveRecord 对象保存时使用。但有时候我们只需要在对象新建或更新时进行特定的验证。
 * 这时可以采用 on_create 和 on_update 选项来单独指定。
 *
 * @code
 * '属性名' => array(
 *      'allow_null' => false,
 *      'allow_blank' => false,
 * 
 *      array('is_int', '属性 X 必须是一个整数'),
 *      array('user_defined', array('User', 'checkProp'), '属性 X 必须大于 xxxx'),
 * 
 *      'on_update' => array(
 *          'allow_null' => true,
 *          'allow_blank' => true,
 *          'include' => 'is_int',
 * 
 *          array('user_defined', array('User', 'checkProp2'), '属性 X 必须 yyyy'),
 *      ),
 * ),
 * @endcode 
 * 
 * 在这个验证策略中，指定了默认的验证选项和验证规则，然后用 on_update 指定了在更新对象时要使用的验证策略。
 * 
 * on_update 验证策略中的 <strong>include</strong> 指定了要包含哪些默认验证规则。这样可以避免重复书写验证规则。
 * 如果有多个同样的验证方法（例如多个 user_defined），那么需要为验证规则指定一个名称，然后在
 * include 中需要指定包含哪些名称的验证规则。
 * 
 * @code
 * '属性名' => array(
 *      'user_defined_1' => array('user_defined', ...),
 *      array('user_defined', ...),
 *      'user_defined_3' => array('user_defined', ...),
 * 
 *      'on_update' => array(
 *          'include' => 'user_defined_1, user_defined_3',
 *      ),
 * ),
 * @endcode
 * 
 * 除了包含默认验证规则，on_update 验证策略中也可以指定更多的验证规则。而这些验证规则仅仅在对象更新时使用。
 * 
 * 
 * <h3>自定义验证方法</h3>
 * 
 * 除了使用 QValidator 提供的验证方法，还可以通过名为 user_defined 的验证方法来使用开发者自己定义的验证方法。
 * 
 * @code
 * '属性名' => array(
 *      ...
 *      array('user_defined', array('User', 'checkProp'), '属性 X 必须大于 xxxx'),
 *      ...
 * ),
 * @endcode 
 * 
 * 该条验证规则会调用 User 类的 checkProp() 静态方法对数据进行验证。
 * User::checkProp() 方法看上去应该是下面的样子：
 * 
 * @code
 * class User extends QDB_ActiveRecord_Abstract
 * {
 *
 *     static function checkProp($value)
 *     {
 *         // 自定义的验证方法
 *         return true;
 *     }
 * }
 * @endcode
 * 
 * 自定义的验证方法中，第一个参数是要验证的属性值。验证后返回 true 表示该验证通过。
 * 
 * 如果自定义的验证方法有多个参数，可以在验证规则中指定：
 * 
 * @code
 * // 具有多个参数的自定义验证方法
 * static function checkProp($value, $param1, $param2)
 * {
 *     ...
 * }
 * 
 * 
 * // 为自定义验证方法提供更多的参数
 * '属性名' => array(
 *      ...
 *      array('user_defined', array('User', 'checkProp'), 参数1, 参数2, '属性 X 必须大于 xxxx'),
 *      ...
 * ),
 * @endcode
 * 
 * 由于自定义的验证方法可以是任何类的静态方法，所以我们可以把一些常用的验证方法集中放置在一个类中。
 * 
 * @code
 * 
 * class ValidationHelper
 * {
 *     static function checkProp($value)
 *     {
 *         ...
 *     }
 * 
 *     static function checkProp2($value)
 *     {
 *         ...
 *     }
 * 
  *     static function checkProp3($value)
 *     {
 *         ...
 *     }
 * }
 * 
 * 
 * '属性名' => array(
 *      ...
 *      array('user_defined', array('ValidationHelper', 'checkProp'), '属性 X 必须大于 xxxx'),
 *      ...
 * ),
 * @endcode 
 * 
 * 
 */
