# Ghi chú

## Phím tắt VS Code

- **Ctrl + Alt + I**: Mở cửa sổ AI của VS Code.
## PHP

- `PHP_EOL`: Hằng xuống dòng theo hệ điều hành. Ví dụ: `echo 'Xin chào' . PHP_EOL;`.

- `number_format()` chỉ dùng để **hiển thị**. Giá và kết quả tính toán vẫn giữ dạng số nguyên.

- `strict_types=1` không tự kiểm tra kiểu của các phần tử bên trong mảng; vì vậy vẫn cần `is_int()`.

- `require_once __DIR__ . '/buoi2.php';`: Nạp file `buoi2.php` từ cùng thư mục với file hiện tại; `__DIR__` là đường dẫn thư mục chứa file hiện tại.
- `require_once` dùng để bắt buộc nạp một file PHP và chỉ nạp file đó một lần. Nếu file không tồn tại, PHP báo lỗi và dừng thực thi. Câu lệnh này là cách nạp file thủ công, không phải cơ chế Composer autoload.

- `final class`: Ngăn class khác kế thừa `OrderLine`.

- `private`: Bảo vệ các thuộc tính khỏi việc sửa trực tiếp bên ngoài.

- `readonly`: Không cho phép thay đổi đơn giá sau khi khởi tạo.

- `__construct()`: Kiểm tra dữ liệu ngay lúc tạo đối tượng.

- `instanceof`: Kiểm tra một biến có phải là đối tượng thuộc một class cụ thể hay không; trả về `true` hoặc `false`. Ví dụ: `if (!$line instanceof OrderLine)` kiểm tra `$line` không phải là đối tượng `OrderLine`.

- `$argv`: Mảng chứa các đối số khi chạy PHP từ dòng lệnh. `$argv[0]` là tên file đang chạy, `$argv[1]` là đối số đầu tiên được truyền vào.

## Laravel

- `php artisan tinker`: Mở REPL để chạy và thử các câu lệnh PHP trong ứng dụng Laravel.
- `php artisan config:clear`: Xóa cache cấu hình để Laravel đọc lại cấu hình hiện tại.
- `php artisan migrate`: Chạy các migration chưa được áp dụng để cập nhật cấu trúc cơ sở dữ liệu.
- `php artisan db:seed --class=CustomerSeeder`: Chạy riêng seeder `CustomerSeeder` để thêm dữ liệu khách hàng mẫu vào cơ sở dữ liệu.

- Tạo model cùng file migration tương ứng:

```sh
php artisan make:model Customer -m
php artisan make:model Product -m
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

  - `php artisan make:model`: Lệnh Artisan tạo một Eloquent model.
  - `Customer`, `Product`, `Order`, `OrderItem`: Tên các model được tạo, thường đại diện cho các thực thể trong ứng dụng.
  - `-m` (`--migration`): Tạo thêm một file migration để định nghĩa cấu trúc bảng cơ sở dữ liệu tương ứng. Migration cần được chạy bằng `php artisan migrate` để áp dụng vào cơ sở dữ liệu.

- Dùng `DB::transaction()` để xóa dữ liệu trong các bảng theo thứ tự, trong một transaction:
```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    DB::table('order_items')->delete();
    DB::table('orders')->delete();
    DB::table('products')->delete();
    DB::table('customers')->delete();
});
```

- Tắt tạm kiểm tra khóa ngoại để `truncate()` các bảng, rồi bật lại trong `finally` để bảo đảm được bật lại kể cả khi có lỗi, Xóa dữ liệu và đặt lại ID về 1.
    ```php
    use Illuminate\Support\Facades\DB;

    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    try {
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('products')->truncate();
        DB::table('customers')->truncate();
        DB::table('users')->truncate();
    } finally {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
    ```

- Tạo dữ liệu mẫu và token kiểm thử trong Tinker:
    ```php
    use App\Models\Customer;
    use App\Models\Product;
    use App\Models\Order;
    use App\Models\OrderItem;
    use App\Models\User;

    // Khách hàng
    $customer = Customer::create([
        'name' => 'Nguyen Van A',
        'email' => 'customer@example.com',
        'phone' => '0901234567',
    ]);

    // Sản phẩm
    $product = Product::create([
        'name' => 'Laptop Dell',
        'price' => 15000000,
        'stock' => 8,
    ]);

    // Đơn hàng
    $order = Order::create([
        'customer_id' => $customer->id,
        'status' => 'pending',
    ]);

    // Chi tiết đơn hàng
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // Tài khoản kiểm thử
    $user = User::create([
        'name' => 'API Tester',
        'email' => 'tester@example.com',
        'password' => bcrypt('TestPassword123!'),
    ]);

    $token = $user->createToken('postman-test')->plainTextToken;

    echo $token;
    ```
