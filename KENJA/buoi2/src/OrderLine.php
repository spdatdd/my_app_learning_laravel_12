<?php

declare(strict_types=1);

namespace App\Orders;

use InvalidArgumentException;

final class OrderLine
{
    private int $quantity;

    public function __construct(
        private readonly int $unitPrice,
        int $quantity
    ) {
        if ($unitPrice < 0) {
            throw new InvalidArgumentException(
                'Đơn giá không được âm.'
            );
        }

        $this->setQuantity($quantity);
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Số lượng phải lớn hơn 0.'
            );
        }

        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function subtotal(): int
    {
        return $this->unitPrice * $this->quantity;
    }
}
