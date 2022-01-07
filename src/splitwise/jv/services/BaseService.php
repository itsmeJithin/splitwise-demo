<?php


namespace com\jv\testProject\service;


use com\jv\testProject\dto\MysqlObject;
use com\jv\testProject\exception\Exception;
use com\jv\testProject\utils\MySQLConnector;
use com\jv\testProject\utils\Utils;
use com\linways\base\util\ObjectUtil;

class BaseService
{
    public $mySqlConnector;

    public $connection;

    /**
     * @throws Exception
     */
    public function establishConnection()
    {
        $this->mySqlConnector = MySQLConnector::getInstance();
        if (!$this->mySqlConnector->connection) {
            $this->connection = $this->mySqlConnector->getConnection("localhost", "root", "root@mac", "sample");
            if ($this->connection->connect_error) {
                $this->connection = NULL;
                throw new Exception("CONNECTION_FAILED", $this->connection->connect_error);
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function executeQuery($sql, $isReturnKey = FALSE)
    {
        $sqlResult = NULL;
        $this->establishConnection();
        $result = mysqli_query($this->connection, $sql);
        try {
            $sqlResult = $this->processResult($result, $isReturnKey);
        } catch (\Exception $e) {
            throw $e;
        }
        return $sqlResult;
    }


    /**
     * @throws Exception
     */
    private function processResult($mysqlResult, $isReturnKey = FALSE)
    {
        $sqlResult = NULL;
        if (!$mysqlResult) {
            throw $this->parseSqlException();
        }
        $sqlResult = new MysqlObject();
        $sqlResult->sqlResult = $mysqlResult;
        $sqlResult->id = $isReturnKey ? mysqli_insert_id($this->connection) : NULL;
        return $sqlResult;
    }

    public function executeQueryForObject($sql)
    {
        $sqlResult = NULL;
        $this->establishConnection();
        $result = mysqli_query($this->connection, $sql);
        try {
            $sqlResult = $this->processResult($result);
        } catch (\Exception $e) {
            throw $e;
        }
        return mysqli_fetch_object($sqlResult->sqlResult);
    }

    public function executeQueryForList($sql)
    {
        $sqlResult = NULL;
        $objectList = [];
        $this->establishConnection();
        $result = mysqli_query($this->connection, $sql);
        try {
            $sqlResult = $this->processResult($result);
            $objectList = Utils::fetchObjectList($sqlResult);
        } catch (\Exception $e) {
            throw $e;
        }
        return $objectList;
    }

    /**
     * @return Exception
     */
    private function parseSqlException()
    {
        $errorCode = NULL;
        $errorMessage = mysqli_error($this->connection);
        switch (mysqli_errno($this->connection)) {
            case 1062:
                $errorCode = "DUPLICATE_ENTRY";
                break;
            case 1215:
                $errorCode = "CANNOT_ADD_FOREIGN_KEY";
                break;
            case 1451:
                $errorCode = "CANNOT_DELETE_OR_UPDATE_ROW_FOREIGN_KEY_FAILED";
                break;
            default:
                $errorCode = mysqli_errno($this->connection);
                break;
        }
        return new Exception($errorCode, $errorMessage);
    }

}