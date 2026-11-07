<?php

trait LoggerTrait
{
    public function log($message)
    {
        echo '[LOG]: '.$message.PHP_EOL;
    }
}

class OrderService
{
    use LoggerTrait;

    public function createOrder(): void
    {
        // Logic to create an order
        $this->log('Order has been created.');
    }
}

$service = new OrderService;
$service->createOrder();
