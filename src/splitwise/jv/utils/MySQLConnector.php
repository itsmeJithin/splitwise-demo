<?php


namespace com\jv\testProject\utils;


use com\jv\testProject\exception\Exception;

class MySQLConnector
{
    private static $_instance = null;

    public $connection = null;

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * @return MySQLConnector|null
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $host
     * @param $userName
     * @param $password
     * @param $db
     * @return \mysqli
     * @throws Exception
     */
    public function getConnection($host, $userName, $password, $db)
    {
        if ($this->connection == NULL) {
            $this->connection = new \mysqli($host, $userName, $password, $db);
        }
        if ($this->connection->connect_error) {
            $this->connection = NULL;
            throw new Exception("CONNECTION_FAILED", $this->connection->connect_error);
        }
        return $this->connection;

    }


}