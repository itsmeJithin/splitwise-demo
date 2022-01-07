<?php


namespace com\jv\testProject\service;


use com\jv\testProject\dto\User;
use com\jv\testProject\exception\Exception;
use com\jv\testProject\request\AddExpenseRequest;
use com\jv\testProject\request\CreateUserRequest;
use com\jv\testProject\utils\Utils;

class UserService extends BaseService
{
    public static $_instance = NULL;

    /**
     * preventing outside instance creation
     * UserService constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Preventing outside cloning
     */
    protected function __clone()
    {
    }

    /**
     * @return UserService|null
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     *
     * Creating user
     * @param CreateUserRequest $request
     * @return mixed|string
     * @throws Exception
     */
    public function saveUserDetails(CreateUserRequest $request)
    {
//        if (empty($request->name))
//            throw new Exception("Invalid user name", "INVALID_USER_NAME");
        if (empty($request->mobileNumber))
            throw new Exception("Invalid mobile number", "INVALID_MOBILE_NUMBER");

        $userId = Utils::getRandomString(17);
        $sql = "INSERT INTO `users` (`user_id`, `name`, `phone_number`) 
                VALUES ('$userId','$request->name','$request->mobileNumber');";
        $result = $this->executeQuery($sql, true);
        return $userId;
    }

    public function getUserId($mobileNumber)
    {
        $sql = "SELECT user_id,name FROM users WHERE phone_number ='$mobileNumber'";
        $result = $this->executeQueryForObject($sql);
        $user = new User();
        $user->userId = $result->user_id;
        $user->userName = $result->name;
        return $user;
    }

    /**
     * @throws Exception
     */
    public function addExpense(AddExpenseRequest $request)
    {
        $owedUserId = null;
        if (empty($request->owedUserPhoneNumber)) {
            throw new Exception("Invalid owed user phone number", "INVALID_OWED_USER_PHONE_NUMBER");
        }
        if (empty($request->userId)) {
            throw new Exception("Authentication failed", "AUTHENTICATION_FAILED");
        }
        try {
            $user = $this->getUserId($request->owedUserPhoneNumber);
            if (empty($user->userId)) {
                $createRequest = new CreateUserRequest();
                $createRequest->mobileNumber = $request->owedUserPhoneNumber;
                $owedUserId = $this->saveUserDetails($createRequest);
            } else
                $owedUserId = $user->userId;
            $sql = "INSERT INTO user_expenses(user_id,owed_user_id,total_amount,description,user_owe_amount) 
                    VALUES ('$request->userId','$owedUserId',$request->totalAmount,'$request->description',
                    $request->sharedAmount)";
            $this->executeQuery($sql);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function settleWithOtherUser()
    {

    }

    /**
     * @param $userId
     * @return array|User[]
     * @throws Exception
     */
    public function getAllDebitsWithOtherUsers($userId)
    {
        if (empty($userId)) {
            throw new Exception("Authentication failed", "AUTHENTICATION_FAILED");
        }
        $sql = "SELECT u.user_id,u.name,u.phone_number, SUM(user_owe_amount) as owed_amount 
                FROM user_expenses as ue
                INNER JOIN users u ON u.user_id = ue.owed_user_id
                WHERE ue.user_id = '$userId' AND ue.is_settled = 0
                GROUP BY u.user_id";
        try {
            $results = $this->executeQueryForList($sql);
            $users = [];
            foreach ($results as $result) {
                $user = new User();
                $user->userId = $result->user_id;
                $user->phoneNumber = $result->phone_number;
                $user->userName = $result->name;
                $user->totalAmount = $result->owed_amount;
                $users[] = $user;
            }
            return $users;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function getAllBorrowed($userId)
    {
        if (empty($userId)) {
            throw new Exception("Authentication failed", "AUTHENTICATION_FAILED");
        }
        $sql = "SELECT u.user_id,u.name,u.phone_number, SUM(user_owe_amount) as owed_amount 
                FROM user_expenses as ue
                INNER JOIN users u ON u.user_id = ue.user_id
                WHERE ue.owed_user_id = '$userId' AND ue.is_settled = 0
                GROUP BY u.user_id";
        try {
            $results = $this->executeQueryForList($sql);
            $users = [];
            foreach ($results as $result) {
                $user = new User();
                $user->userId = $result->user_id;
                $user->phoneNumber = $result->phone_number;
                $user->userName = $result->name;
                $user->totalAmount = $result->owed_amount;
                $users[] = $user;
            }
            return $users;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $userId
     * @param $userName
     * @throws \Exception
     */
    public function updateUserName($userId, $userName)
    {
        if (empty($userId)) {
            throw new Exception("Authentication failed", "AUTHENTICATION_FAILED");
        }
        if (empty($userName)) {
            throw new Exception("User name should not be empty", "INVALID_USER_NAME");
        }
        $sql = "UPDATE users SET name= '$userName' WHERE user_id ='$userId'";
        try {
            $this->executeQuery($sql);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $userId
     * @param $phoneNumber
     * @return User
     * @throws Exception
     */
    public function getUserBalanceAmount($userId, $phoneNumber)
    {
        if (empty($userId)) {
            throw new Exception("Authentication failed", "AUTHENTICATION_FAILED");
        }
        if (empty($phoneNumber)) {
            throw new Exception("Invalid phone number", "INVALID_PHONE_NUMBER");
        }
        $sql = "SELECT u.user_id,u.name,u.phone_number, SUM(user_owe_amount) as owed_amount 
                FROM user_expenses as ue
                INNER JOIN users u ON u.user_id = ue.owed_user_id AND u.phone_number= '$phoneNumber'
                WHERE ue.user_id = '$userId' AND ue.is_settled = 0";
        try {
            $result = $this->executeQueryForObject($sql);
            $user = new User();
            $user->phoneNumber = $result->phone_number;
            $user->userName = $result->name;
            $user->userId = $result->user_id;
            $user->totalAmount = $result->owed_amount;
            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

}