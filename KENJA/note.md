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

- `vendor/bin/pint --dirty --format agent`: Chạy Laravel Pint để tự sửa định dạng mã PHP trước khi hoàn tất thay đổi; `--dirty` chỉ xử lý các file có thay đổi chưa commit trong Git, còn `--format agent` xuất kết quả dạng JSON gọn cho công cụ AI. Lệnh chỉnh sửa file trực tiếp; không kiểm thử logic ứng dụng.
- `php artisan tinker`: Mở REPL để chạy và thử các câu lệnh PHP trong ứng dụng Laravel.
- `php artisan config:clear`: Xóa cache cấu hình để Laravel đọc lại cấu hình hiện tại.
- `php artisan migrate`: Chạy các migration chưa được áp dụng để cập nhật cấu trúc cơ sở dữ liệu.
- `php artisan migrate:status`: Xem trạng thái các migration trên kết nối cơ sở dữ liệu hiện tại; `Ran` là đã chạy, `Pending` là chưa chạy, `Batch` là số đợt chạy migration, được dùng khi rollback theo đợt. Dùng để kiểm tra trước hoặc sau khi chạy `php artisan migrate`; lệnh chỉ kiểm tra trạng thái, không áp dụng hay hoàn tác migration.
- `php artisan db:seed --class=CustomerSeeder`: Chạy riêng seeder `CustomerSeeder` để thêm dữ liệu khách hàng mẫu vào cơ sở dữ liệu.

### Kiểm thử

- `curl.exe -i http://127.0.0.1:8000/api/v1/debug-error`: Gửi request GET đến API local; `-i` hiển thị cả status, response headers và body để kiểm tra lỗi. Server phải đang chạy và route thử phải tồn tại, chủ động ném exception; khi `APP_DEBUG=false`, mong đợi status `500`, JSON `{"message":"Server Error"}` và header `X-Request-ID` để đối chiếu log. Nếu nhận `404`, kiểm tra URL và khai báo route; trong nhóm `prefix('v1')`, chỉ khai báo `/debug-error` để tránh lặp `v1`. Xóa route thử lỗi sau khi thực hành.
- `Select-String -Path storage/logs/laravel.log -SimpleMatch "UUID-vua-copy"`: Chạy trong PowerShell tại thư mục gốc dự án để tìm các dòng log chứa request ID. Thay `UUID-vua-copy` bằng giá trị header `X-Request-ID` của lần gọi API cần kiểm tra; `-Path` chỉ định file log, `-SimpleMatch` tìm chuỗi nguyên văn thay vì biểu thức chính quy. Lệnh chỉ đọc file; nếu không có kết quả, kiểm tra UUID, việc ghi log và file log đang dùng.
- `php artisan make:test --phpunit FirstApiFeatureTest --no-interaction`: Tạo class test PHPUnit tại `tests/Feature/FirstApiFeatureTest.php`; `--phpunit` chọn PHPUnit, `FirstApiFeatureTest` là tên class, `--no-interaction` không hỏi tương tác. Mặc định tạo feature test; thêm `--unit` nếu muốn tạo unit test trong `tests/Unit`. Lệnh chỉ tạo file test, không chạy test.
- `php artisan test --compact tests/Feature/FirstApiFeatureTest.php`: Chạy các test trong riêng file `tests/Feature/FirstApiFeatureTest.php`; `--compact` hiển thị kết quả rút gọn. Dùng sau khi tạo hoặc sửa test để kiểm tra test xanh hay đỏ; file test phải tồn tại trước khi chạy.

### Form Request

- `php artisan make:request TenRequest --no-interaction`: Tạo class Form Request trong `app/Http/Requests` để tách validation và kiểm tra quyền khỏi controller; `TenRequest` là tên class cần tạo, `--no-interaction` không hỏi tương tác khi chạy lệnh.

```sh
php artisan make:request StoreCustomerRequest --no-interaction
php artisan make:request UpdateCustomerRequest --no-interaction
```

- `authorize(): bool`: Quyết định request có được phép thực hiện hay không. Class mới tạo mặc định trả `false`, khiến request nhận lỗi 403; trong giai đoạn chưa học phân quyền, tạm đổi thành `return true;`.
- `rules(): array`: Khai báo quy tắc validation. Khai báo kiểu tham số như `StoreCustomerRequest $request` trong action để Laravel tự kiểm tra trước khi chạy action; không cần lặp validation trong controller.
- `$request->validated()`: Lấy mảng dữ liệu đã qua validation. Với các trường đơn của khách, chỉ khai báo `name`, `email`, `phone` trong rules để `created_by` và `role` do client gửi không lọt vào mảng xử lý. Không dùng `$request->all()` để ghi dữ liệu; trường hệ thống phải do server gán.
- `sometimes`: Chỉ validate trường khi trường đó có trong payload, phù hợp cập nhật từng phần bằng PATCH. Ví dụ `'name' => ['sometimes', 'required', 'string', 'max:150']`: không gửi thì bỏ qua, nhưng gửi tên rỗng hoặc `null` thì báo lỗi. Với email có thể xóa giá trị, dùng `'email' => ['sometimes', 'nullable', 'email', 'max:255']`.
- PATCH phải chỉ áp dụng các key có trong dữ liệu đã validate: không gửi trường thì giữ nguyên giá trị cũ; gửi `email: null` thì chủ động xóa email. Form Request chỉ kiểm tra đầu vào, không tự lưu hay cập nhật database.

### Model và dữ liệu

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
