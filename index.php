<?php
declare (strict_types = 1);

use App\Repositories\TransactionRepository;
use App\Service\MoneyTransactionServices;

require_once __DIR__ . '/vendor/autoload.php';

if ($argc != 2) {
    die('File not specified');
}

$config = require 'config.php';

try {
    $transactionController = new MoneyTransactionServices(new TransactionRepository(), $config);
    $transactionController->index($argv[1]);
} catch (Exception $ex) {
    echo $ex->getMessage();
    exit(1);
}
