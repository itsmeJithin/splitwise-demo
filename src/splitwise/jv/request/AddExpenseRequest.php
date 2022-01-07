<?php


namespace com\jv\testProject\request;


class AddExpenseRequest
{
    /**
     * @var float
     */
    public $totalAmount;
    /**
     * @var float
     */
    public $sharedAmount;
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $owedUserPhoneNumber;

    /**
     * @var string
     */
    public $description;
}