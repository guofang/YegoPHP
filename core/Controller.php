<?php
namespace YegoPHP\core;

use YegoPHP\core\View;
/**
 * 控制器基类
 */
class Controller
{
    protected $_module;
    protected $_controller;
    protected $_action;
    protected $_view;
    private $_params;
    // 构造函数，初始化属性，并实例化对应模型
    public function __construct($module, $controller, $action, $params)
    {
        $this->_module = $module;
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_initRequestParams($params);
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
    /**
     * 请求参数初始化
     * @see 根据请求url和post自动初始化，禁止子类及外部调用
     * @see 合并pathinfo格式参数对、$_GET、$_POST参数数组
     */
    private function _initRequestParams($params)
    {
        $paramsArr = array();
        if (!empty($params))
        {
            while ($params)
            {
                $params_key = array_shift($params);
                $params_val = array_shift($params);
                $paramsArr[$params_key] = $params_val;
            }
        }
        $this->_params = array_merge($paramsArr, array_merge($_GET, $_POST));
    }
    /**
     * 参数赋值
     * @param unknown $name
     * @param unknown $value
     */
    protected function _setParam($name, $value)
    {
        $this->_params [$name] = $value;
    }
    /**
     * 查询全部参数
     * @return Ambigous <unknown, multitype:>
     */
    protected function _getParams()
    {
        return $this->_params;
    }
    /**
     * 获取指定参数
     * @param unknown $name
     * @param string $default 默认值
     * @return string
     */
    protected function _getParam($name, $default=NULL)
    {
        return isset($this->_params [$name]) ? $this->_params [$name] : $default;
    }
}