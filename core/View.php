<?php
namespace YegoPHP\core;
/**
 * 视图基类
 */
class View
{
    protected $variables = array();
    protected $_module;
    protected $_controller;
    protected $_action;
    function __construct($module, $controller, $action)
    {
        $this->_module = strtolower($module);
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
    }
    // 变量赋值
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }
    // 渲染展示
    public function render()
    {
        extract($this->variables);
        $moduleHeader = APP_PATH . 'app/' . $this->_module . '/views/header.php';
        $moduleFooter = APP_PATH . 'app/' . $this->_module . '/views/footer.php';

        $controllerHeader = APP_PATH . 'app/' . $this->_module . '/views/' . $this->_controller . '/header.php';
        $controllerFooter = APP_PATH . 'app/' . $this->_module . '/views/' . $this->_controller . '/footer.php';
        $controllerLayout = APP_PATH . 'app/' . $this->_module . '/views/' . $this->_controller . '/' . $this->_action . '.php';

        // 公共页头文件
        if (is_file($controllerHeader)) {
            include ($controllerHeader);
        } elseif (is_file($moduleHeader)) {
            include ($moduleHeader);
        }

        //判断视图文件是否存在
        if (is_file($controllerLayout)) {
            include ($controllerLayout);
        } else {
            echo "<h1>无法找到视图文件</h1>";
        }
        
        // 公共页脚文件
        if (is_file($controllerFooter)) {
            include ($controllerFooter);
        } elseif (is_file($moduleFooter)) {
            include ($moduleFooter);
        }
    }
}