<?php

declare(strict_types=1);

namespace App\Orders;

use InvalidArgumentException;

final class OrderCalculator
{
    /**
     * @param  list<OrderLine>  $lines
     */
    public function total(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            if (! $line instanceof OrderLine) {
                throw new InvalidArgumentException(
                    'Danh sách chứa dòng không hợp lệ.'
                );
            }
            $total += $line->subtotal();
        }

        return $total;
    }
}
