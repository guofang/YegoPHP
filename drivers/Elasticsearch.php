<?php
namespace YegoPHP\drivers;

class Elasticsearch{
	private function __construct(){} //禁止外部实例化
    private function __clone(){} //防止对象被克隆
    private static $_es = NULL;
    /**
     * 单例模式返回Es连接对象
     */
    public static function getInstance(){
    	if(self::$_es == NULL)
    	{
    		try{
    			require (VENDOR_PATH . 'autoload.php');
    			$config = require (CONF_PATH . 'config.php');
        		return \Elasticsearch\ClientBuilder::create()->setHosts($config['ES'])->build();
    		}catch(Exception $e){
    			exit($e->getMessage());
    		}
    	}
    	return self::$_es;
    }
}