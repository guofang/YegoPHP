<?php
namespace YegoPHP\drivers;

/**
 * 数据库驱动类
 * @author Foli
 * @date 2018-4-3
 */
class Pdo{
    private static $_pdo = NULL;
    /**
     * 单例模式返回PDO连接对象
     * @return \PDO
     */
    public static function getInstance(){
        if (self::$_pdo === NULL)
        {
            try {
                $options = array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHAR,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                );
                return self::$_pdo = new \PDO(DB_DSN, DB_USER, DB_PSWD, $options);
            }catch (\PDOException $e){
                exit($e->getMessage());
            }
        }
        return self::$_pdo;
    }
}