# YegoPHP
PHP5.3+
框架入口已对magic_quotes_gpc做了兼容处理，该特性已自 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除。

本框架：
1、支持多模块子域名路由
2、参数支持目录分割参数对或传统?&参数对格式
3、session支持除file类型存储外的memcache等外置集中式存储
4、默认采用PDO执行底层数据库连接操作，支持mysql等多种数据库连接

目录结构：
project                 WEB部署根目录
├─app                   应用目录
│  ├─index              默认模块目录
│      ├─controllers        控制器目录
│      ├─models             模块目录
│      ├─views              视图目录
│  ├─admin              管理后台模块目录
│      ├─controllers        控制器目录
│      ├─models             模块目录
│      ├─views              视图目录
│  ├─mobile             WAP模块目录
│      ├─controllers        控制器目录
│      ├─models             模块目录
│      ├─views              视图目录
├─config                配置文件目录
├─YegoPHP               框架核心目录
│ ├─base                MVC基类目录
│ ├─YegoPHP.php         框架主文件  
├─static                静态文件目录
├─index.php             入口文件


index.php示例：
<?php
//项目根目录
define('APP_PATH', __DIR__ . '/');
//项目配置文件目录
define('CONF_PATH', APP_PATH . 'config/');
// 调试模式
define('APP_DEBUG', TRUE);
// 加载框架启动文件
include (APP_PATH . 'YegoPHP/start.php');


.htaccess示例：
<IfModule mod_rewrite.c>
    # 打开Rerite功能
    RewriteEngine On

    # 如果请求的是真实存在的文件或目录，直接访问
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # 如果访问的文件或目录不是真事存在，分发请求至 index.php
    RewriteRule . index.php
</IfModule>


config.php示例：
<?php
// 基础配置
$config = array(
//     'APP_MODULE_LIST' => 'index,admin,mobile', //模块
//     'DEFAULT_MODULE' => 'index', //默认模块
//     'DEFAULT_CONTROLLER' => 'index', //默认控制器
//     'DEFAULT_ACTION' => 'index', //默认控制器
	'APP_SUB_DOMAIN_DEPLOY' => 1, //子域名控制
	'APP_SUB_DOMAIN_RULES' => array( //子域名映射到模块规则
		'm' => 'mobile', //m.yegophp.com -> yegophp.com/mobile/
	    'admin' => 'admin', //admin.yegophp.com -> yegophp.com/admin/
	),
    'SESSION_OPTIONS' => array('domain' => '.yegophp.com'), //session配置，可配置name、path、domain、expire、type
);
// 数据库配置
$config ['DB'] = array (
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'yegophp',
    'DB_USER' => 'root',
    'DB_PSWD' => '123456',
    'DB_PORT' => '3306',
    'DB_PREF' => 'yego_',
);

return $config;


