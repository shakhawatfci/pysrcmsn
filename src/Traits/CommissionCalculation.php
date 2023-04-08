<?php

namespace App\Traits;

use App\Models\TransactionModel;

trait CommissionCalculation
{

    public function printCommission(array $commissionData): void
    {
        $roundUp = $this->roundUp($commissionData['amount'], $commissionData['setting'], $commissionData['precision']);
        fwrite(STDOUT, print_r($roundUp . "\n", true));
    }

    public function roundUp(float $amount, array $config, int $precision): string
    {
        $smallest_unit  = 1 / pow(10, $precision);
        $rounded_amount = ceil($amount / $smallest_unit) * $smallest_unit;
        return number_format($rounded_amount, $precision, '.', '');
    }

    private function convertCurrency(TransactionModel $transaction, array $config, float $amount = -1, array $onlineCurrencies = []): float
    {
        if ($onlineCurrencies && $onlineCurrencies[$transaction->getCurrency()]) {
            $currencyRate = $onlineCurrencies[$transaction->getCurrency()];
        } else {
            $currencyRate = $config['offlineCurrencyConversion'][$transaction->getCurrency()]['rate'];
        }

        if ($amount < 0) {
            $converted = $transaction->getOperationAmount() / $currencyRate;
        } else {
            $converted = $amount * $currencyRate;
        }
        return $converted;
    }
}
