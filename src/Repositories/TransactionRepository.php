<?php
declare (strict_types = 1);

namespace App\Repositories;

use App\Interfaces\TransactionInterface;
use App\Models\TransactionModel;

class TransactionRepository implements TransactionInterface
{
    protected $transactions = [];

    public function setDataFromFile(string $filename)
    {
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);
            $contents = str_replace("\r\n", "\n", $contents);
            $contents = explode("\n", $contents);

            foreach ($contents as $content) {
                if ($content != "") {
                    $content     = explode(',', $content);
                    $transaction = new TransactionModel();
                    $transaction->setOperationDate((string) $content[0]);
                    $transaction->setUserId((int) $content[1]);
                    $transaction->setUserType((string) $content[2]);
                    $transaction->setOperationType((string) $content[3]);
                    $transaction->setTransactionAmount((float) $content[4]);
                    $transaction->setCurrency((string) $content[5]);
                    $this->transactions[] = $transaction;
                }
            }
        } else {
            throw new \Exception("File Not Exist");
        }
    }

    public function getAllData(): array
    {
        return $this->transactions;
    }

    public function addData(object $transaction)
    {
        $this->transactions[] = $transaction;
    }

    public function getByParam(string $param, int $value): array
    {
        $userTransactions = [];
        foreach ($this->transactions as $transaction) {
            $method = 'get' . ucfirst($param);
            if (method_exists($transaction, $method)) {
                if ($transaction->$method() == $value) {
                    $userTransactions[] = $transaction;
                }
            }
        }
        return $userTransactions;
    }
}
