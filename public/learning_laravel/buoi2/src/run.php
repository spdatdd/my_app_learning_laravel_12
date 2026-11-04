<?php 

declare(strict_types=1);

require_once __DIR__ . '/OrderLine.php';
require_once __DIR__ . '/OrderCalculator.php';

use App\Orders\OrderLine;
use App\Orders\OrderCalculator;

$calculator = new OrderCalculator();

$test = $argv[1] ?? 'invalid';

switch ($test) {
    case 'valid':
        $line1 = new OrderLine(25000, 2);
        $line2 = new OrderLine(15000, 3);

        echo "Dòng 1: " . $line1->subtotal() . PHP_EOL;
        echo "Dòng 2: " . $line2->subtotal() . PHP_EOL;
        echo "Tổng: " . $calculator->total([$line1, $line2]) . PHP_EOL;
        break;

    case 'invalid':
        try {
            $line = new OrderLine(25000, 0);
            echo $line->subtotal();
        } catch (InvalidArgumentException $e) {
            echo "Lỗi: " . $e->getMessage() . PHP_EOL;
        }
        break;

    case 'independent':
        $line1 = new OrderLine(12000, 2);
        $line2 = new OrderLine(12000, 2);

        $line1->setquantity(5);

        echo "Dòng 1: " . $line1->subtotal() . PHP_EOL;
        echo "Dòng 2: " . $line2->subtotal() . PHP_EOL;
        echo "Tổng: " . $calculator->total([$line1, $line2]) . PHP_EOL;
        break;

    default:
        echo "Ví dụ: php run.php valid" . PHP_EOL; 
}