<?php
declare (strict_types = 1);

namespace App\Service;

use App\Interfaces\TransactionInterface;
use App\Models\TransactionModel;
use App\Traits\CommissionCalculation;
use App\Traits\OnlineCurrency;

class MoneyTransactionServices
{
    use CommissionCalculation, OnlineCurrency;

    /**
     * @var object
     */
    protected $transactionRepository;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $onlineCurrencies = [];

    public function __construct(TransactionInterface $repository, array $config)
    {
        $this->transactionRepository = $repository;
        $this->config                = $config;
        $this->onlineCurrencies      = $this->getOnlineCurrencyRateList($this->config['onlineCurrencyUrl']);
    }

    public function index(string $filename)
    {
        $this->transactionRepository->setDataFromFile($filename);
        $getTransactionData = $this->transactionRepository->getAllData();
        $this->checkCommission($getTransactionData);
    }

    private function checkCommission(array $transactions)
    {
        if (is_array($transactions) && count($transactions) > 0) {
            foreach ($transactions as $transaction) {
                if ($transaction->getOperationType() == TransactionModel::DEPOSIT) {
                    $commission = $this->depositCommission($transaction, $this->config);
                } else {
                    $commission = $this->withdrawCommission($transaction, $this->config);
                }
                $commissionData['amount']    = $commission ?? null;
                $commissionData['setting']   = $this->config ?? null;
                $commissionData['precision'] = $this->config['offlineCurrencyConversion'][$transaction->getCurrency()]['precision'] or null;
                $this->printCommission($commissionData);
            }
        } else {
            print_r("Data Not Found\n");
        }
    }

    private function depositCommission(TransactionModel $transaction, array $config)
    {
        $commission      = $transaction->getOperationAmount() * $config['depositCommissionPercent'];
        $convertedAmount = $this->convertCurrency($transaction, $config, $commission, $this->onlineCurrencies);
        return $convertedAmount;
    }

    private function withdrawCommission(TransactionModel $transaction, array $config)
    {
        if ($transaction->getUserType() == TransactionModel::PRIVETUSER) {
            $week                      = date("oW", strtotime($transaction->getOperationDate()));
            $userTransactions          = $this->transactionRepository->getByParam('userId', $transaction->getUserId());
            $transactionsPerWeek       = 0;
            $transactionsPerWeekAmount = 0;

            foreach ($userTransactions as $userTransaction) {
                $currentDate = date("oW", strtotime($userTransaction->getOperationDate()));
                if (
                    $week == $currentDate &&
                    $userTransaction->getOperationType() == TransactionModel::WITHDRAW
                ) {
                    if ($userTransaction->getId() == $transaction->getId()) {
                        break;
                    }
                    $transactionsPerWeek++;
                    $transactionsPerWeekAmount += $this->convertCurrency($userTransaction, $config);
                }
            }

            if ($transactionsPerWeek >= $config['withdrawCommissionCommonFreeTransactionsLimit']) {
                $commission = $transaction->getOperationAmount() * $config['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
                return $commission;
            } elseif ($transactionsPerWeekAmount >= $config['withdrawCommissionCommonDiscount']) {
                $commission = $transaction->getOperationAmount() * $config['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
                return $commission;
            } else {

                $convertedAmount = $this->convertCurrency($transaction, $config);
                $exceededAmount  = $transactionsPerWeekAmount + $convertedAmount - $config['withdrawCommissionCommonDiscount'];
                $amount          = $exceededAmount > 0 ? $exceededAmount : 0;
                $commission      = $amount * $config['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
                return $this->convertCurrency($transaction, $config, $commission, $this->onlineCurrencies);
            }
        } else {
            $commission      = $transaction->getOperationAmount() * $config['user_types'][$transaction->getUserType()]['withdrawCommissionPercent'];
            $convertedAmount = $this->convertCurrency($transaction, $config, $commission, $this->onlineCurrencies);
            return $convertedAmount;
        }
    }

}
