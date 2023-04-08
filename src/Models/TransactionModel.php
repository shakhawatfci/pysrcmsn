<?php
declare (strict_types = 1);

namespace App\Models;

class TransactionModel
{
    const DEPOSIT      = "deposit";
    const WITHDRAW     = "withdraw";
    const PRIVETUSER   = "private";
    const BUSINESSUSER = "business";

    private $id;

    private $date;

    private $userId;

    private $userType;

    private $transactionType;

    private $transactionAmount;

    private $currency;

    public function __construct()
    {
        $this->id = uniqid();
    }

    public function getOperationDate(): string
    {
        return $this->date;
    }

    public function setOperationDate(string $date)
    {
        $this->date = $date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType)
    {
        $this->userType = $userType;
    }

    public function getOperationType(): string
    {
        return $this->transactionType;
    }

    public function setOperationType(string $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    public function getOperationAmount(): float
    {
        return $this->transactionAmount;
    }

    public function setTransactionAmount(float $transactionAmount)
    {
        $this->transactionAmount = $transactionAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    public function getId(): string
    {
        return $this->id;
    }

}
