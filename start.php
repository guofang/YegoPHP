<?php
/**
 * 框架入口
 * @author Foli
 * @date 2018-3-30
 */
//框架根目录
defined('YEGO_PATH') or define('YEGO_PATH', __DIR__ . '/');
if(version_compare(PHP_VERSION, '5.3.0', '<'))  die('require PHP > 5.3.0 !');

defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('CONF_PATH') or define('CONF_PATH', APP_PATH . 'config/');
defined('APP_DEBUG') or define('APP_DEBUG', FALSE); // 是否调试模式，默认部署模式

//版本信息
define('YEGO_VERSION', '1.0');

//模块配置
define('APP_MODULE_LIST', 'index,admin,mobile'); //默认模块列表，增加新模块需要覆盖此项配置
define('DEFAULT_MODULE', 'index'); //默认模块
define('DEFAULT_CONTROLLER', 'index'); //默认控制器
define('DEFAULT_ACTION', 'index'); //默认控制器

/**
 * 子域名配置
 * @see 默认不开启
 * @see 如果打开，则需配置子域名到模块的映射规则，如下所示
 * 'APP_SUB_DOMAIN_RULES' => array( //子域名映射到模块规则
		'm' => 'mobile', //m.yegophp.com -> yegophp.com/mobile/
	    'admin' => 'admin', //admin.yegophp.com -> yegophp.com/admin/
	),
 */
define('APP_SUB_DOMAIN_DEPLOY', 0); //默认不开启子域名

//加载框架主文件
require APP_PATH . 'YegoPHP/YegoPHP.php';
(new YegoPHP\YegoPHP())->_run();