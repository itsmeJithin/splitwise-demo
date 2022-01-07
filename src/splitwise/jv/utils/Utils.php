<?php


namespace com\jv\testProject\utils;



class Utils
{
    public static function getRandomString($length = 17)
    {
        $alphaNumericText = "";
        $unMistakableCharacterString = "23456789ABCDEFGHJKLMNPQRSTWXYZabcdefghijkmnopqrstuvwxyz";
        $max = strlen($unMistakableCharacterString);

        for ($i = 0; $i < $length; $i++) {
            $alphaNumericText .= $unMistakableCharacterString[random_int(0, $max - 1)];
        }

        return $alphaNumericText;
    }

    /**
     * @param $result
     * @return array
     */
    public static function fetchObjectList($result)
    {
        $objectList = [];
        if (self::hasRows($result->sqlResult)) {
            while ($obj = self::fetchObject($result->sqlResult)) {
                $objectList[] = $obj;
            }
        }
        return $objectList;
    }

    /**
     * @param $result
     * @return null
     */
    public static function fetchObject($result)
    {
        return mysqli_fetch_object($result);
    }

    /**
     * @param $sqlResult
     * @return bool
     */
    public static function hasRows($sqlResult)
    {
        return self::getNumOfRows($sqlResult) > 0 ? true : false;
    }

    /**
     * @param $sqlResult
     * @return int|string
     */
    public static function getNumOfRows($sqlResult)
    {
        return mysqli_num_rows($sqlResult);
    }
}