<?php
require_once "../vendor/autoload.php";

use com\jv\testProject\request\AddExpenseRequest;
use com\jv\testProject\request\CreateUserRequest;
use com\jv\testProject\service\UserService;

$userId = null;
echo "Welcome to Splitwise" . PHP_EOL;
echo "*******************************" . PHP_EOL;
echo "Select from following option" . PHP_EOL;
echo "1. login" . PHP_EOL;
echo "2. Signup" . PHP_EOL;
$input = readline("");
if ($input == 2) {
    $request = new CreateUserRequest();
    $request->name = readline("Enter your name: ");
    $request->mobileNumber = readPhoneNumber();
    $userId = UserService::getInstance()->saveUserDetails($request);
    echo "Hi $request->name !,Your account is created successfully." . PHP_EOL;
} elseif ($input == 1) {
    $userId = login();
}
showUserOptions($userId);

/**
 * login function that returns user id
 * @return string|null
 */
function login()
{
    $mobileNumber = readPhoneNumber();
    try {
        $user = UserService::getInstance()->getUserId($mobileNumber);
        if (empty($user->userId)) {
            echo "We couldn't find a valid user." . PHP_EOL;
            login();
        }
        if (empty($user->userName)) {
            $userName = readline("Enter your name: ");
            UserService::getInstance()->updateUserName($user->userId, $userName);
        }
        echo "Welcome back $user->userName!" . PHP_EOL;
        return $user->userId;
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    return null;
}

/**
 * shows the user options
 * @param $userId
 * @throws \com\jv\testProject\exception\Exception
 */
function showUserOptions($userId)
{
    echo "1. Add Expense" . PHP_EOL;
    echo "2. You lent" . PHP_EOL;
    echo "3. You borrowed" . PHP_EOL;
    echo "4. Settle up" . PHP_EOL;
    $option = readline("Select from the above options: ");
    switch ($option) {
        case "1":
            addExpense($userId);
            break;
        case "2":
            try {
                $users = UserService::getInstance()->getAllDebitsWithOtherUsers($userId);
                $mask = "|%20.20s |%-30.30s | %-15.15s |\n";
                echo "\n*************************************************************************" . PHP_EOL;
                printf($mask, "Phone Number", "Name", "Amount lent");
                foreach ($users as $user) {
                    printf($mask, $user->phoneNumber, $user->userName, $user->totalAmount);
                }
                echo "*************************************************************************\n" . PHP_EOL;
                showUserOptions($userId);
            } catch (\com\jv\testProject\exception\Exception $e) {
                echo "Error: " . $e->getMessage() . PHP_EOL;
            }
            break;
        case "3":
            $users = UserService::getInstance()->getAllBorrowed($userId);
            $mask = "|%20.20s |%-30.30s | %-15.15s |\n";
            echo "\n*************************************************************************" . PHP_EOL;
            printf($mask, "Phone Number", "Name", "Amount owed");
            foreach ($users as $user) {
                printf($mask, $user->phoneNumber, $user->userName, $user->totalAmount);
            }
            echo "*************************************************************************\n" . PHP_EOL;
            showUserOptions($userId);
            break;
        case 4:
            $phoneNumber = readline("Enter the user phone number to settle the balance amount: ");
            $user = UserService::getInstance()->getUserBalanceAmount($userId, $phoneNumber);
            if (empty($user->userId)) {
                echo "Invalid user details given" . PHP_EOL;
            } else {
                echo "User Name: " . $user->userName ? $user->userName : '-' . PHP_EOL;
                echo "Balance Amount: " . $user->totalAmount ? $user->totalAmount : '0' . PHP_EOL;
                if (!$user->totalAmount) {
                    echo "There is not pending balance" . PHP_EOL;
                    showUserOptions($userId);
                }
            }
            break;
        default:
            echo "Select a valid option";
            showUserOptions($userId);
    }
}

/**
 * This function will manage the expenses
 *
 * @param $userId
 */
function addExpense($userId)
{
    $user = readline("Enter the user phone number:");
    $description = readline("Enter the expense description:");
    $amount = readline("Enter the amount:");
    echo "Select splitting option:" . PHP_EOL;
    echo "1. Percentage to share" . PHP_EOL;
    echo "2. Exact amount to share" . PHP_EOL;
    $percentage = null;
    $sharedAmount = null;
    $splitOption = readline("Enter your splitting option: ");
    switch ($splitOption) {
        case "1":
            $percentage = readline("Enter the percentage you want to share:");
            $sharedAmount = $amount * ($percentage / 100);
            break;
        case "2":
            $sharedAmount = readline("Enter the amount you want to share:");
            break;
        default:
            echo "Invalid option";
    }
    $expenseRequest = new AddExpenseRequest();
    $expenseRequest->userId = $userId;
    $expenseRequest->sharedAmount = $sharedAmount;
    $expenseRequest->totalAmount = $amount;
    $expenseRequest->owedUserPhoneNumber = $user;
    $expenseRequest->description = $description;
    try {
        UserService::getInstance()->addExpense($expenseRequest);
        echo "Your expenses added successfully" . PHP_EOL;
        echo "*********************************" . PHP_EOL;
        showUserOptions($userId);
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }


}

/**
 * reading phone number and validating
 * @return false|string
 */
function readPhoneNumber()
{
    $mobileNumber = readline("Enter a valid phone number: ");
    if (!$mobileNumber)
        readPhoneNumber();
    return $mobileNumber;
}