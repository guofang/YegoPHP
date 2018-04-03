<?php
namespace YegoPHP\base;
/**
 * 控制器基类
 */
class Controller
{
    protected $_module;
    protected $_controller;
    protected $_action;
    protected $_view;
    // 构造函数，初始化属性，并实例化对应模型
    public function __construct($module, $controller, $action)
    {
        $this->_module = $module;
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = new View($module, $controller, $action);
    }
    // 变量赋值
    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }
    
    // 渲染视图
    public function render()
    {
        $this->_view->render();
    }
}