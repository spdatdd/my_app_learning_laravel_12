<?php

interface PaymentInterface
{
    public function pay(float $amount):string;
}

class CashPayment implements PaymentInterface
{
    public function pay(float $amount):string
    {
        return "Thanh toán bằng tiền mặt: $amount VND". PHP_EOL;
    }
}

class BankPayment implements PaymentInterface 
{
    public function pay(float $amount):string
    {
        return "Thanh toán bằng chuyển khoản: $amount VND". PHP_EOL;
    }
}

$payment = new BankPayment();

echo $payment->pay(1000000);

abstract class Payment
{
    public function validateAmount(float $amount):void
    {
        if ($amount <=0) {
            throw new InvalidAmountException(
                "Số tiền thanh toán phải lớn hơn 0"
            );
        }
    }

    abstract public function pay(float $amount):string;
}

class MonoPayment extends Payment
{
    public function pay(float $amount):string
    {
        $this->validateAmount($amount);
        return "Thanh toán bằng Mono: $amount VND". PHP_EOL;
    }
}

$payment = new MonoPayment();

echo $payment->pay(1000000);


trait LoggerTrait
{
    public function log($message)
    {
        echo "[LOG]: " . $message . PHP_EOL;
    }
}

class OrderService
{
    use LoggerTrait;

    public function createOrder():void
    {
        // Logic to create an order
        $this->log("Order has been created.");
    }
}

$service = new OrderService();
$service->createOrder();

class PaymentService
{
    use LoggerTrait;
    public function __construct(
        private PaymentInterface $payment
    ){}

    public function checkout(float $amount): string
    {
        if ($amount <= 0 ) {
            throw new InvalidArgumentException(
                "Số tiền không hợp lệ"
            );
        }
        $this->log("Payment has been created");
        return $this->payment->pay($amount);
    }
}

// Có thể thay BankPayment bằng CashPayment hoặc MonoPayment
$service = new PaymentService(new BankPayment());

echo $service->checkout(1000000);