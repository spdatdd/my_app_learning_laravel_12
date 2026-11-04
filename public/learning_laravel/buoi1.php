<?php
declare(strict_types=1); // Bật chế độ kiểm tra kiểu dữ liệu nghiêm ngặt, nếu không đúng kiểu dữ liệu sẽ báo lỗi.

var_dump(2 == '2');  // true
var_dump(2 === '2'); // false

function calculateLineTotal(array $item):int {
    // Kiểm tra giá và số Lượng, sau đó tính thành tiền.
    $price = $item['price'] ?? null;
    $quantity = $item['quantity'] ?? null;

    if (!is_int($price) || $price < 0) {
        throw new InvalidArgumentException('Giá phải là một số nguyên không âm.');
    }
    if (!is_int($quantity) || $quantity <= 0) {
        throw new InvalidArgumentException('Số lượng phải là một số nguyên không âm.');
    }
    return $price * $quantity;
}

function calculateOrderTotal(array $item):int {
    // Cộng thành tiền của tất cả dòng hàng.
    $total = 0;
    foreach ($item as $value) {
        $total += calculateLineTotal($value);
    }
    return $total;
}

$items =  [
    ['name' => 'Sản phẩm 1', 'price' => 100000, 'quantity' => 2],
    ['name' => 'Sản phẩm 2', 'price' => 50000, 'quantity' => 3],
    ['name' => 'Sản phẩm 3', 'price' => 15000, 'quantity' => 1],
];

try {
    foreach ($items as $item) {
        $lineTotal = calculateLineTotal($item);
        echo $item['name'] . ' - Thành tiền: ' . number_format($lineTotal, 0, ',', '.') . ' VNĐ'.PHP_EOL;
    }

    echo 'Tổng đơn hàng: ' . number_format(calculateOrderTotal($items), 0, ',', '.') . ' VNĐ'.PHP_EOL;
} catch (InvalidArgumentException $e) {
    echo 'Lỗi: ' . $e->getMessage();
}

foreach ([0, -1, 2.5, '2'] as $quantity) {
    try {
        calculateLineTotal([
            'price_vnd' => 100000,
            'quantity' => $quantity,
        ]);

        echo 'CHƯA ĐẠT: đã nhận quantity = '
            . var_export($quantity, true)
            . PHP_EOL;
    } catch (InvalidArgumentException $exception) {
        echo 'ĐẠT: từ chối quantity = '
            . var_export($quantity, true)
            . PHP_EOL;
    }
}