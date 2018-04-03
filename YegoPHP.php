<?php
/**
 * 框架核心类
 * @author Foli
 * @date 2018-3-30
 */
namespace YegoPHP;

class YegoPHP
{
    private $_config = array();
    private $_module = 'index';
    private $_controller = 'index';
    private $_action = 'index';
    private $_param = array(); //URL中非$_GET参数，param1/val1/param2/val2
    
    public function __construct()
    {
        spl_autoload_register(array($this, '_autoloadClass'));
    }
    // 自动加载类
    public function _autoloadClass($clsName)
    {
        if (strpos($clsName, '\\') !== FALSE)
        {
            //包含框架类及应用程序类
            $clsFile = APP_PATH . str_replace('\\', '/', $clsName) . '.php';
            if (is_file($clsFile))
            {
                include $clsFile;
            }else {
                return;
            }
        }else{
            return;
        }
    }
    // 运行框架
    public function _run()
    {
        $this->_loadConf();
        $this->_setEnv();
        $this->_route();
    }
    //加载外部配置文件
    public function _loadConf()
    {
        $config = require_once (CONF_PATH . 'config.php');
        $this->_config = $config;
        !isset($this->_config['APP_MODULE_LIST']) && $this->_config['APP_MODULE_LIST'] = APP_MODULE_LIST;
        !isset($this->_config['DEFAULT_MODULE']) && $this->_config['DEFAULT_MODULE'] = DEFAULT_MODULE;
        !isset($this->_config['DEFAULT_CONTROLLER']) && $this->_config['DEFAULT_CONTROLLER'] = DEFAULT_CONTROLLER;
        !isset($this->_config['DEFAULT_ACTION']) && $this->_config['DEFAULT_ACTION'] = DEFAULT_ACTION;
        !isset($this->_config['APP_SUB_DOMAIN_DEPLOY']) && $this->_config['APP_SUB_DOMAIN_DEPLOY'] = APP_SUB_DOMAIN_DEPLOY; //是否开启子域名
        //
        $this->_setMvc();
        $this->_setSession();
        $this->_setDb();
    }
    //配置环境参数
    public function _setEnv()
    {
        //调试模式
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
        /**
         * 移除全局变量可能存在的自动转义
         * @see magic_quotes_gpc特性已自 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除
         */
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET ) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST ) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }
    //移除自动转义符号
    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }
    /**
     * 配置模块-控制器-视图
     * @see 此处M指模块，本框架支持子域名多模块路由
     * @see 示例URL格式
     * http://yegophp.com/module/controller/action/param1/val1/param2/val2?param3=val3
     * http://yegophp.com/module/controller/action/?param1=val1&param2=val2
     * http://yegophp.com/controller/action/?param1=val1&param2=val2 -> 无模块，即为默认模块
     * http://yegophp.com/?param1=val1&param2=val2 -> 三无，即为默认模块，默认控制器，默认动作
     * http://m.yegophp.com/controller/action/?param1=val1&param2=val2 -> 开启子域名，m子域名按照配置文件映射规则映射到指定模块
     * @see 同时兼容/参数和?参数格式，则需要mvc路由支持自动探测module参数是否真实存在，牺牲了一些效率
     */
    public function _setMvc()
    {
        $this->_module = $this->_config['DEFAULT_MODULE']; //初始化模块默认值
        $this->_controller = $this->_config['DEFAULT_CONTROLLER'];
        $this->_action = $this->_config['DEFAULT_ACTION'];
        
        $url = explode('?', $_SERVER['REQUEST_URI'])[0];
        if ($this->_config['APP_SUB_DOMAIN_DEPLOY'])
        {
            //开启子域名配置
            if (!empty($this->_config['APP_SUB_DOMAIN_RULES']))
            {
                $domainName = $_SERVER['HTTP_HOST']; //获取当前域名（含端口号）
                $subDomainRules = $this->_config['APP_SUB_DOMAIN_RULES'];
                foreach ($subDomainRules as $sub => $mod)
                {
                    $subDomain = $sub . strstr($domainName, '.');
                    $subDomainRules [$subDomain] = $mod;
                }
                if (in_array($domainName, $subDomainRules))
                {
                    $this->_module = strtolower($mod);
                    //
                    if (!empty($url))
                    {
                        $url = trim($url, '/');
                        $urlArr = array_filter(explode('/', $url));
                        $urlArr && $this->_controller = strtolower($urlArr[0]);
                        array_shift($urlArr);
                        $urlArr && $this->_action = strtolower($urlArr[0]);
                        array_shift($urlArr);
                        $urlArr && $this->_param = $urlArr;
                    }
                    return;
                }
            }
        }
        /**
         * 未开启子域名，或找不到子域名对应模块都按未开启处理
         */
        if (!empty($url))
        {
            $url = trim($url, '/');
            $urlArr = array_filter(explode('/', $url));
            if (!empty($urlArr))
            {
                $moduleName = $urlArr [0];
                //探测模块名对应目录是否真实存在
                if (is_dir(APP_PATH . 'app/' . $moduleName))
                {
                    $this->_module = strtolower($moduleName);
                    array_shift($urlArr);
                    $urlArr && $this->_controller = strtolower($urlArr[0]);
                }else {
                    $this->_controller = $moduleName; //不存在，则采用默认模块，url第一个斜线参数设置为controller
                }
                array_shift($urlArr);
                $urlArr && $this->_action = strtolower($urlArr[0]);
                array_shift($urlArr);
                $urlArr && $this->_param = $urlArr;
            }
        }
    }
    //配置会话
    public function _setSession()
    {
        ini_set('session.auto_start', 0);
        if(isset($this->_config['SESSION_OPTIONS']['name']))            
            session_name($this->_config['SESSION_OPTIONS']['name']);
        if(isset($this->_config['SESSION_OPTIONS']['path']))
            session_save_path($this->_config['SESSION_OPTIONS']['path']);
        if(isset($this->_config['SESSION_OPTIONS']['domain']))
            ini_set('session.cookie_domain', $this->_config['SESSION_OPTIONS']['domain']);
        if(isset($this->_config['SESSION_OPTIONS']['expire']))
            ini_set('session.gc_maxlifetime', $this->_config['SESSION_OPTIONS']['expire']);
        if(isset($this->_config['SESSION_OPTIONS']['type']))
            ini_set("session.save_handler", $this->_config['SESSION_OPTIONS']['type']);
        
        session_start();
    }
    //配置数据库
    public function _setDb()
    {
        if ($this->config['DB']) {
            define('DB_HOST', $this->config['DB']['DB_HOST']);
            define('DB_NAME', $this->config['DB']['DB_NAME']);
            define('DB_USER', $this->config['DB']['DB_USER']);
            define('DB_PSWD', $this->config['DB']['DB_PSWD']);
            define('DB_PORT', $this->config['DB']['DB_PORT']);
            define('DB_PREF', $this->config['DB']['DB_PREF']);
        }
    }
    //路由
    public function _route()
    {
        //判断模块、控制器、动作是否存在
        if (!is_dir(APP_PATH . 'app/' . $this->_module))
        {
            exit($this->_module . '模块不存在');
        }
        $controllerCls = 'app\\' . $this->_module . '\\controllers\\' . ucfirst($this->_controller) . 'Controller';
        if (!class_exists($controllerCls))
        {
            exit($this->_controller . '控制器不存在');
        }
        if (!method_exists($controllerCls, $this->_action))
        {
            exit($this->_action . '动作不存在');
        }
        //路由分发
        $dispatch = new $controllerCls($this->_module, $this->_controller, $this->_action);
        call_user_func_array(array($dispatch, $this->_action), $this->_param);
    }
}