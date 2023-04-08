<?php

namespace App\Test\Service;

use App\Models\TransactionModel;
use App\Repositories\TransactionRepository;
use App\Service\MoneyTransactionServices;
use App\Traits\CommissionCalculation;
use App\Traits\OnlineCurrency;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MoneyTransactionServicesTest extends TestCase
{
    use CommissionCalculation, OnlineCurrency;

    private $config;
    private $services;
    private $transactionModel;

    /**
     * MoneyTransactionServicesTest constructor.
     */

    public function __construct()
    {
        parent::__construct();
        $this->config           = include './config.php';
        $this->services         = new MoneyTransactionServices(new TransactionRepository(), $this->config);
        $this->transactionModel = new TransactionModel();
    }

    public function testDepositInCommissionEUR()
    {
        $method = self::getMethod('App\Service\MoneyTransactionServices', 'depositCommission');
        $this->transactionModel->setOperationDate("2016-01-10");
        $this->transactionModel->setUserId("2");
        $this->transactionModel->setUserType("business");
        $this->transactionModel->setOperationType("deposit");
        $this->transactionModel->setTransactionAmount("10000.00");
        $this->transactionModel->setCurrency("EUR");
        $result    = $method->invokeArgs($this->services, [$this->transactionModel, $this->config]);
        $precision = $this->config['offlineCurrencyConversion'][$this->transactionModel->getCurrency()]['precision'];
        $this->assertEquals(3.00, $this->roundUp($result, $this->config, $precision));
    }

    protected static function getMethod($class, $name)
    {
        $class  = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testDepositCommissionUSD()
    {
        $method = self::getMethod('App\Service\MoneyTransactionServices', 'depositCommission');
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("private");
        $this->transactionModel->setOperationType("deposit");
        $this->transactionModel->setTransactionAmount("100.00");
        $this->transactionModel->setCurrency("USD");
        $result    = $method->invokeArgs($this->services, [$this->transactionModel, $this->config]);
        $precision = $this->config['offlineCurrencyConversion'][$this->transactionModel->getCurrency()]['precision'];
        $this->assertEquals(0.04, $this->roundUp($result, $this->config, $precision));
    }

    public function testDepositCommissionJPY()
    {
        $method = self::getMethod('App\Service\MoneyTransactionServices', 'depositCommission');
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("private");
        $this->transactionModel->setOperationType("deposit");
        $this->transactionModel->setTransactionAmount("10000");
        $this->transactionModel->setCurrency("JPY");
        $result    = $method->invokeArgs($this->services, [$this->transactionModel, $this->config]);
        $precision = $this->config['offlineCurrencyConversion'][$this->transactionModel->getCurrency()]['precision'];
        $this->assertEquals(393, $this->roundUp($result, $this->config, $precision));
    }

    public function testConvertCurrencyToEUR()
    {
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("private");
        $this->transactionModel->setOperationType("deposit");
        $this->transactionModel->setTransactionAmount("10000");
        $this->transactionModel->setCurrency("JPY");
        $result    = $this->convertCurrency($this->transactionModel, $this->config);
        $precision = $this->config['offlineCurrencyConversion'][$this->transactionModel->getCurrency()]['precision'];
        $this->assertEquals(77, $this->roundUp($result, $this->config, $precision));
    }

    public function testConvertCurrencyFromEUR()
    {
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("private");
        $this->transactionModel->setOperationType("deposit");
        $this->transactionModel->setTransactionAmount("10000");
        $this->transactionModel->setCurrency("JPY");
        $result    = $this->convertCurrency($this->transactionModel, $this->config, 100);
        $precision = $this->config['offlineCurrencyConversion'][$this->transactionModel->getCurrency()]['precision'];
        $this->assertEquals(13087, $this->roundUp($result, $this->config, $precision));
    }

    public function testWithdrawCommissionTransactionPrivate()
    {
        $method = self::getMethod('App\Service\MoneyTransactionServices', 'withdrawCommission');
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("private");
        $this->transactionModel->setOperationType("withdraw");
        $this->transactionModel->setTransactionAmount("1100");
        $this->transactionModel->setCurrency("EUR");
        $result = $method->invokeArgs($this->services, [$this->transactionModel, $this->config]);
        $this->assertEquals(0.3, $result);
    }

    public function testWithdrawCommissionTransactionBusiness()
    {
        $method = self::getMethod('App\Service\MoneyTransactionServices', 'withdrawCommission');
        $this->transactionModel->setOperationDate("2016-01-05");
        $this->transactionModel->setUserId("1");
        $this->transactionModel->setUserType("business");
        $this->transactionModel->setOperationType("withdraw");
        $this->transactionModel->setTransactionAmount("50");
        $this->transactionModel->setCurrency("EUR");
        $result = $method->invokeArgs($this->services, [$this->transactionModel, $this->config]);
        $this->assertEquals(0.25, $result);
    }
}
