# Lộ trình Laravel 12 — Sales Management API

**12 tuần · 6 ngày/tuần · 2 giờ/ngày · 72 buổi · 144 giờ**

Tài liệu tự học bằng tiếng Việt. Mỗi ngày hoàn thành một buổi theo thứ tự; ngày thứ 7 nghỉ. Không cần gắn lịch với ngày cụ thể. Đây là kế hoạch học và hướng giải, không phải mã nguồn dự án đã được triển khai hoặc kiểm thử.

## 1. Cách sử dụng

**Đầu vào:** đã biết biến, mảng, vòng lặp, hàm trong PHP; hiểu HTML cơ bản và câu lệnh SQL SELECT/INSERT/UPDATE. Tuần 1 ôn PHP hướng đối tượng và HTTP, không thay thế khóa PHP nhập môn. Nếu chưa có nền tảng này, học bổ sung trước khi bắt đầu để giữ đúng ngân sách 144 giờ.

**Nhịp cố định mỗi buổi (120 phút):** 10 phút ôn và chạy lại phần cũ; 25 phút đọc kiến thức; 65 phút làm bài; 15 phút kiểm tra theo tiêu chí; 5 phút ghi nhật ký và commit. Các buổi tổng kết dùng 25 phút đọc để rà thiết kế, 65 phút để sửa hoặc trình diễn. Chỉ làm phần mở rộng nếu đã hoàn thành phần bắt buộc trong thời gian này.

**Bằng chứng cuối mỗi buổi:** một commit hoặc thay đổi tài liệu, request/response mẫu hoặc kết quả test, và 3 dòng nhật ký: đã hiểu gì, còn lỗi gì, bước tiếp theo. Trước khi có test tự động, kiểm tra bằng PHP CLI và HTTP client; từ buổi 10 bắt đầu dùng feature test.

**Chấm mỗi buổi /10:** hiểu và giải thích được kiến thức 2 điểm; bài tập chạy đúng 4 điểm; chứng minh được các tiêu chí riêng của buổi 3 điểm; nhật ký/commit 1 điểm. Đạt từ 8/10 và không còn lỗi nghiêm trọng. Lỗi nghiêm trọng gồm vượt quyền, lộ bí mật, sai tiền, âm tồn kho, thay đổi dở dang hoặc tạo giao dịch trùng. Tiêu chí buổi đầu chỉ áp dụng cho phần chức năng đã xây; xác thực đầy đủ bắt đầu tuần 5. Nếu chưa đạt, thu nhỏ phần phụ và sửa trong buổi tổng kết tuần; không cộng thêm giờ học bắt buộc.

**Công cụ:** PHP 8.2 trở lên phù hợp với Laravel 12 và các gói đã khóa, Composer 2, Git, editor, HTTP client (Postman/Bruno hoặc curl), MySQL 8. Dùng MySQL cho phát triển và một database MySQL riêng cho kiểm thử, đặc biệt bài khóa dòng. Không dùng database thật để chạy test. API thuần JSON nên không bắt buộc học frontend, Vite hoặc Node trong lộ trình này.

**Khởi tạo đúng phiên bản:** dùng `composer create-project laravel/laravel sales-management-api "^12.0"`; kiểm tra `php artisan --version`; giữ `composer.lock` trong Git. Dùng `php artisan install:api` khi thêm API/Sanctum; chạy migration cần thiết. Máy chủ học tập chạy bằng `php artisan serve`. Không dùng lệnh tạo mặc định rồi mặc nhiên cho rằng đó là Laravel 12. Trong cấu trúc Laravel 12 mặc định, cấu hình middleware/exception thường ở `bootstrap/app.php`, lịch ở `routes/console.php`; tránh chép nguyên hướng dẫn phiên bản cũ yêu cầu sửa Kernel không tồn tại.

## 2. Đặc tả dự án xuyên suốt

Một doanh nghiệp, một kho, tiền VND nguyên đồng, không thuế/chiết khấu/phí vận chuyển, không thanh toán thật, không đa tiền tệ. Đơn hàng xác nhận được coi là doanh số ghi nhận; báo cáo không mang nghĩa doanh thu kế toán hay tiền đã thu.

### Vai trò và quyền

| Chức năng | admin | sales |
|---|---|---|
| Xem sản phẩm đang hoạt động | Có | Có |
| Tạo/sửa/ngừng bán sản phẩm, điều chỉnh kho | Có | Không |
| Quản lý khách hàng | Tất cả | Chỉ khách do mình tạo |
| Tạo đơn | Cho mọi khách hợp lệ | Chỉ khách do mình tạo |
| Xem/sửa/xác nhận/hủy đơn | Tất cả theo trạng thái | Chỉ đơn mình tạo theo trạng thái |
| Báo cáo doanh số | Toàn bộ | Chỉ đơn mình tạo |
| Quản lý tài khoản | Seed/CLI trong phạm vi khóa học | Không |

Không có đăng ký công khai hoặc API tự đổi vai trò. Admin cũng không được bỏ qua quy tắc trạng thái/tồn kho. Sales không được truyền `created_by` để nhận quyền của người khác. Dùng 404 cho tài nguyên ngoài phạm vi được xem; 403 cho thao tác không được phép trên tài nguyên thấy được.

### Dữ liệu tối thiểu

| Bảng | Trường chính và ràng buộc |
|---|---|
| users | name, email unique, password hash, role: admin/sales |
| customers | name, email nullable, phone, created_by FK; không ép email khách duy nhất |
| products | sku unique, name, price_vnd số nguyên không âm, stock số nguyên không âm, is_active |
| orders | customer_id FK, created_by FK, status, total_vnd, confirmed_at nullable, cancelled_at nullable |
| order_items | order_id FK, product_id FK, sku_snapshot, name_snapshot, unit_price_vnd, quantity, line_total_vnd; unique(order_id, product_id) |
| stock_movements | product_id, order_id nullable, actor_id, delta có dấu, reason, created_at |
| idempotency_requests | user_id, key, request_hash, order_id; unique(user_id, key) |

Các bảng framework cho token, jobs và failed jobs được thêm khi cần. Không cho xóa sản phẩm/khách đã được đơn tham chiếu; trả 409. Có thể ngừng bán sản phẩm bằng `is_active=false`. Không cần soft delete trong bản bắt buộc.

### Quy tắc nghiệp vụ bắt buộc

1. `draft → confirmed → cancelled` hoặc `draft → cancelled`; không mở lại đơn đã hủy. Chỉ sửa dòng hàng khi draft.
2. Tạo/sửa draft lấy giá hiện tại từ database và ghi snapshot; tổng do server tính. Mỗi lần thay toàn bộ dòng draft, cập nhật lại snapshot. Xác nhận giữ nguyên giá đã chốt trong draft, kiểm tra lại sản phẩm đang bán và tồn kho. Giới hạn bài học: tối đa 50 dòng/đơn, số lượng mỗi dòng 1–1.000; giá mỗi sản phẩm tối đa 1.000.000.000 VND; dùng số nguyên 64 bit cho phép tính/tổng tiền.
3. Draft chưa trừ kho. Confirm trừ kho và ghi stock movement trong cùng transaction. Hủy đơn đã confirm hoàn kho đúng một lần; hủy draft không hoàn kho. Xác nhận lần hai/hủy lần hai trả 409, không thay đổi dữ liệu.
4. Không tin `price`, `total`, `role`, `stock`, `created_by` do client gửi ở endpoint không cho phép. Trường hệ thống được server gán; không nhận mass assignment từ toàn bộ request.
5. Khi confirm/cancel: khóa đơn trước, khóa các sản phẩm theo thứ tự ID tăng dần; kiểm tra lại trạng thái và tồn trong transaction. Điều chỉnh kho cũng khóa sản phẩm. Test đồng thời trên MySQL; test chạy tuần tự không chứng minh được khóa hoạt động.
6. Tạo đơn dùng `Idempotency-Key`: cùng user, cùng key, cùng payload chuẩn hóa trả lại đơn cũ; key cũ với payload khác trả 409. Bản bài học lưu liên kết đơn trong cùng transaction; unique constraint xử lý tranh chấp. Ghi rõ cách chuẩn hóa payload và thời hạn lưu key trong tài liệu.
7. Báo cáo chỉ tính đơn đang confirmed; đơn cancelled bị loại, kể cả từng confirmed. Lọc theo `confirmed_at`. Khoảng ngày nhập theo Asia/Ho_Chi_Minh, đổi sang UTC và dùng cận đầu bao gồm/cận cuối loại trừ. Đây là báo cáo trạng thái hiện tại, không phải sổ doanh thu bất biến.

### API mục tiêu

Tiền tố `/api/v1`; request gửi `Accept: application/json`. Thành công trả resource `{data: ...}`; danh sách có `data`, `links`, `meta`. Lỗi validation dùng `{message, errors}`; lỗi nghiệp vụ thêm `code` ổn định, ví dụ `INSUFFICIENT_STOCK`. Không buộc mọi lỗi có cùng cấu trúc với danh sách.

| Nhóm | Endpoint |
|---|---|
| Auth | POST /auth/login; DELETE /auth/token; GET /me |
| Customers | GET/POST /customers; GET/PATCH/DELETE /customers/{id} |
| Products | GET/POST /products; GET/PATCH/DELETE /products/{id} |
| Kho | POST /products/{id}/stock-adjustments; GET /products/{id}/stock-movements |
| Orders | GET/POST /orders; GET /orders/{id}; PUT /orders/{id}/items |
| Trạng thái | POST /orders/{id}/confirm; POST /orders/{id}/cancel |
| Báo cáo | GET /reports/sales; GET /reports/top-products |
| Xuất báo cáo | POST /report-exports; GET /report-exports/{id}; GET /report-exports/{id}/download |

201 khi tạo tài nguyên, 200 khi đọc/cập nhật hoặc replay idempotency, 202 khi nhận yêu cầu xuất file, 204 khi xóa thành công, 401 thiếu/sai token, 403 thiếu quyền thao tác, 404 không tìm thấy/ngoài phạm vi, 409 xung đột nghiệp vụ, 422 dữ liệu sai, 429 quá giới hạn. Endpoint xuất báo cáo sẽ có bảng `report_exports` ở tuần 9.

## 3. Tổng quan 12 tuần

| Tuần | Buổi | Kết quả cuối tuần |
|---|---|---|
| 1 | 01–06 | PHP/OOP/HTTP, môi trường Laravel 12, đặc tả |
| 2 | 07–12 | Route, controller, validation, JSON và test đầu tiên |
| 3 | 13–18 | Schema, migration, Eloquent, seed và truy vấn |
| 4 | 19–24 | CRUD sản phẩm/khách, tìm kiếm, phân trang |
| 5 | 25–30 | Sanctum, policy, quyền sở hữu, test vượt quyền |
| 6 | 31–36 | Tạo và sửa đơn nháp, snapshot giá, transaction |
| 7 | 37–42 | Confirm/cancel an toàn, tồn kho và idempotency |
| 8 | 43–48 | Kiểm thử hồi quy, kiến trúc vừa đủ, hiệu năng |
| 9 | 49–54 | Queue, thông báo, xuất CSV, scheduler |
| 10 | 55–60 | Báo cáo, timezone, cache, API contract |
| 11 | 61–66 | Bảo mật, CI, staging, vận hành và khôi phục |
| 12 | 67–72 | Nghiệm thu, sửa lỗi, tài liệu, demo cuối khóa |

## 4. Lịch chi tiết 72 buổi

### Tuần 1 — Nền tảng và môi trường

#### Buổi 01 · Ngày 1 — PHP cho bài toán bán hàng
- **Kiến thức:** kiểu dữ liệu, associative array, hàm, strict comparison, tiền nguyên đồng; phân biệt dữ liệu và kết quả tính toán.
- **Bài tập:** viết script PHP tính tổng 3 dòng hàng; từ chối quantity 0, âm hoặc không nguyên. Thử giá 100.000, số lượng 2 và giá 50.000, số lượng 3.
- **Đáp án định hướng:** mỗi dòng bằng giá nguyên × số lượng nguyên; tổng bằng tổng các dòng. Tách `calculateLineTotal` và `calculateOrderTotal`; kiểm tra đầu vào trước khi tính, không dùng float cho VND.
- **Tiêu chí đánh giá:** ví dụ cho 350.000 VND; cả 3 đầu vào quantity sai bị từ chối; giải thích được vì sao không nhận tổng tiền client.

#### Buổi 02 · Ngày 2 — OOP, namespace và exception
- **Kiến thức:** class, constructor, visibility, type declaration, namespace, exception; dữ liệu hợp lệ ngay sau khởi tạo.
- **Bài tập:** tạo `OrderLine` có giá/số lượng và phương thức tính thành tiền; tạo lớp tính tổng nhận nhiều dòng. Viết 3 ví dụ chạy bằng CLI.
- **Đáp án định hướng:** thuộc tính có kiểu, constructor kiểm tra số lượng; lớp tính tổng duyệt danh sách `OrderLine`; báo lỗi đầu vào bằng exception có thông điệp rõ.
- **Tiêu chí đánh giá:** 2 dòng đúng cho tổng đúng; dòng không hợp lệ không tạo được; thay đổi một dòng không tác động các dòng còn lại.

#### Buổi 03 · Ngày 3 — HTTP và hợp đồng API
- **Kiến thức:** method, URL, header, JSON, status code; tính khác nhau giữa xác thực và phân quyền; REST cơ bản.
- **Bài tập:** viết tài liệu request/response cho tạo khách, xem sản phẩm và xác nhận đơn; thêm ví dụ 401, 404, 422, 409.
- **Đáp án định hướng:** POST tạo trả 201 cùng ID; GET trả 200; 422 cho sai dữ liệu, 409 cho trạng thái/tồn kho xung đột. Body lỗi có message và mã nghiệp vụ khi phù hợp.
- **Tiêu chí đánh giá:** 3 request có method/header/body rõ; đủ 4 tình huống lỗi; không dùng GET để thay đổi kho hoặc xác nhận đơn.

#### Buổi 04 · Ngày 4 — Cài Laravel 12 và Git
- **Kiến thức:** Composer, Artisan, cấu trúc thư mục, `.env`, `.env.example`, dependency lock; vòng đời request ở mức tổng quan.
- **Bài tập:** tạo đúng Laravel 12, cấu hình MySQL database học tập, chạy app, khởi tạo Git và README hướng dẫn khởi động.
- **Đáp án định hướng:** dùng lệnh khởi tạo ở phần 1, kiểm tra phiên bản, cấu hình DB và chạy migration mặc định. Giữ `.env` ngoài Git; `.env.example` chỉ có giá trị mẫu không bí mật.
- **Tiêu chí đánh giá:** xác minh version 12.x; app phản hồi HTTP; migration thành công; repository không chứa mật khẩu/token thật.

#### Buổi 05 · Ngày 5 — SQL và mô hình dữ liệu
- **Kiến thức:** primary key, foreign key, unique, quan hệ 1–n; tách order và order_items; dữ liệu snapshot.
- **Bài tập:** vẽ ERD bằng bảng hoặc sơ đồ cho 7 bảng nghiệp vụ; viết SQL giả cho lấy đơn cùng khách và dòng hàng.
- **Đáp án định hướng:** order thuộc customer/user, item thuộc order/product; snapshot giữ lịch sử tên/giá. `stock_movements.delta` có dấu; không lưu nhiều ID sản phẩm trong một chuỗi.
- **Tiêu chí đánh giá:** nêu được cardinality từng quan hệ; chỉ ra unique SKU và unique dòng sản phẩm; giải thích được vì sao sửa giá sản phẩm không đổi đơn cũ.

#### Buổi 06 · Ngày 6 — Chốt phạm vi và kiểm tra nền tảng
- **Kiến thức:** tiêu chí nghiệm thu, chia tính năng, trạng thái đơn; phân biệt yêu cầu bắt buộc và mở rộng.
- **Bài tập:** tạo backlog theo 12 tuần; viết 8 kịch bản chấp nhận: quyền sở hữu, sửa draft, confirm, thiếu kho, hủy, giá snapshot, replay, báo cáo.
- **Đáp án định hướng:** dùng dạng Given/When/Then với dữ liệu cụ thể; đánh dấu frontend, thuế, thanh toán thật và nhiều kho ngoài phạm vi. Chạy lại script PHP và app.
- **Tiêu chí đánh giá:** đủ 8 kịch bản có kết quả đo được; giải thích được 2 giờ/ngày và đầu ra từng tuần; môi trường sẵn sàng cho tuần 2.

### Tuần 2 — Luồng HTTP trong Laravel

#### Buổi 07 · Ngày 1 — Route và controller API
- **Kiến thức:** `routes/api.php`, route group, prefix, controller, `route:list`; `install:api` tạo nền tảng API/Sanctum.
- **Bài tập:** cài API, thêm `GET /api/v1/health` và `GET /api/v1/products` trả dữ liệu mẫu qua controller.
- **Đáp án định hướng:** group prefix `v1` bên trong API routes, tránh lặp `/api`; controller trả JSON; chưa cần DB cho danh sách mẫu.
- **Tiêu chí đánh giá:** hai endpoint trả 200 với JSON; route:list đúng URL/method; truy cập method sai nhận 405.

#### Buổi 08 · Ngày 2 — Request và validation
- **Kiến thức:** lấy JSON/query, rules, lỗi 422; phân biệt trường bắt buộc và nullable.
- **Bài tập:** thêm endpoint tạo khách tạm thời chưa lưu DB; validate name, email tùy chọn, phone. Gửi 4 payload gồm hợp lệ, thiếu name, email sai, chuỗi quá dài.
- **Đáp án định hướng:** name required/string/max:150, email nullable/email/max:255, phone required/string/max:30; chỉ lấy dữ liệu đã validate để trả ví dụ 201.
- **Tiêu chí đánh giá:** payload đúng trả 201; 3 payload sai trả 422 với tên trường; ghi rõ dữ liệu tạm chưa tồn tại sau request.

#### Buổi 09 · Ngày 3 — Form Request và giới hạn dữ liệu
- **Kiến thức:** Form Request, `validated()`, `authorize()`, PUT/PATCH; đầu vào cho phép và trường do server quản lý.
- **Bài tập:** chuyển validation khách vào request class; thêm request cập nhật dùng `sometimes`; thử gửi `created_by` và `role` kèm payload.
- **Đáp án định hướng:** tạm cho authorize true trước tuần phân quyền; controller chỉ dùng allowlist từ validated. Không lấy `$request->all()` để ghi dữ liệu.
- **Tiêu chí đánh giá:** PATCH chỉ đổi trường gửi lên; trường hệ thống không lọt vào dữ liệu xử lý; validation không lặp lại trong controller.

#### Buổi 10 · Ngày 4 — Feature test đầu tiên
- **Kiến thức:** Arrange/Act/Assert, PHPUnit hoặc Pest; HTTP JSON assertions; phân biệt unit test và feature test.
- **Bài tập:** chọn một framework test và viết 4 test cho health, danh sách mẫu, tạo khách đúng, thiếu name.
- **Đáp án định hướng:** dùng `getJson`/`postJson`, kiểm tra status và JSON path/errors; tạm thời chưa cần RefreshDatabase khi chưa ghi DB. Test nghiệp vụ HTTP đặt trong Feature.
- **Tiêu chí đánh giá:** 4 test xanh; cố ý đổi rule required làm test đỏ rồi khôi phục; không chỉ kiểm tra status mà có kiểm tra body.

#### Buổi 11 · Ngày 5 — Middleware và xử lý lỗi
- **Kiến thức:** pipeline middleware, JSON exception response, 404/405/500, logging an toàn.
- **Bài tập:** tạo middleware gắn request ID vào response; bảo đảm route API không tồn tại trả JSON; ghi một log thử có request ID.
- **Đáp án định hướng:** dùng UUID, header `X-Request-ID`; cấu hình middleware/exception theo skeleton Laravel 12; không ghi Authorization header hoặc password vào log.
- **Tiêu chí đánh giá:** response thành công và lỗi 404 có JSON phù hợp; request ID truy được trong log ở luồng đã gắn middleware; không hiển thị stack trace khi debug tắt.

#### Buổi 12 · Ngày 6 — Tổng kết luồng request
- **Kiến thức:** route → middleware → request → controller → response; đọc lỗi từ status và log.
- **Bài tập:** hoàn thiện collection HTTP gồm 6 request; sửa một lỗi prefix, một lỗi validation và một lỗi JSON cố ý tạo; cập nhật README.
- **Đáp án định hướng:** đối chiếu route:list trước, đọc response/log sau; chạy test sau mỗi lần sửa. Ghi flow vào README bằng 5 bước.
- **Tiêu chí đánh giá:** toàn bộ test tuần 2 xanh; 6 request chạy lại được; giải thích đúng vị trí đặt validation, phân quyền và nghiệp vụ về sau.

### Tuần 3 — Database và Eloquent

#### Buổi 13 · Ngày 1 — Migration users, customers, products
- **Kiến thức:** migration, kiểu số nguyên, index, foreign key; ràng buộc DB hỗ trợ tính đúng.
- **Bài tập:** bổ sung role users; tạo customers/products theo đặc tả; chạy migrate và rollback trong DB học tập.
- **Đáp án định hướng:** bigint cho tiền, stock không âm qua validation và ràng buộc DB phù hợp; unique SKU ở DB; created_by tham chiếu users. Không dùng cascade xóa user làm mất lịch sử khách.
- **Tiêu chí đánh giá:** migrate/rollback/migrate chạy được; SKU trùng bị DB từ chối; khách tham chiếu user không tồn tại không được lưu.

#### Buổi 14 · Ngày 2 — Migration orders và tồn kho
- **Kiến thức:** bảng chi tiết, FK restrict, unique composite, index phục vụ truy vấn.
- **Bài tập:** tạo orders, order_items, stock_movements; bổ sung index `(created_by, status)` và confirmed_at phù hợp truy vấn dự kiến.
- **Đáp án định hướng:** items có snapshot và tổng dòng; movement.delta là số có dấu; không cascade từ product/customer sang đơn. Dùng enum PHP/cast sau, cột status có thể là string.
- **Tiêu chí đánh giá:** không tạo được item mồ côi hoặc hai dòng cùng product trong một đơn; xóa product đã tham chiếu bị chặn; toàn bộ migration dựng lại được trên DB bỏ đi.

#### Buổi 15 · Ngày 3 — Model, fillable và casts
- **Kiến thức:** Eloquent CRUD, mass assignment, boolean/integer/datetime casts; model không thay thế validation.
- **Bài tập:** tạo model cho các bảng; lưu và đọc product/customer bằng Tinker; bổ sung factory cơ bản cho user.
- **Đáp án định hướng:** allowlist fillable có chủ đích; role/stock chỉ cập nhật trong luồng cho phép. `price_vnd`, `stock` cast integer, is_active boolean, thời điểm cast datetime.
- **Tiêu chí đánh giá:** giá/stock đọc đúng kiểu; không vô tình ghi trường hệ thống từ payload mẫu; giải thích được fillable không phải cơ chế phân quyền.

#### Buổi 16 · Ngày 4 — Quan hệ Eloquent
- **Kiến thức:** belongsTo, hasMany, eager loading, N+1.
- **Bài tập:** khai báo customer.orders, order.customer/creator/items, item.product, product.stockMovements; lấy một đơn cùng toàn bộ quan hệ cần thiết.
- **Đáp án định hướng:** chỉ định FK `created_by` cho creator; dùng `with(['customer','items.product'])` khi cần. Response lịch sử dùng snapshot thay vì tên/giá hiện tại.
- **Tiêu chí đánh giá:** truy cập đúng cả hai chiều quan hệ; 10 đơn không phát sinh một query sản phẩm cho từng item; đổi tên product không làm mất snapshot.

#### Buổi 17 · Ngày 5 — Factory, seeder và test database
- **Kiến thức:** dữ liệu mẫu có quan hệ, seed có chủ đích, `.env.testing`, RefreshDatabase.
- **Bài tập:** seed 1 admin, 2 sales, 10 khách, 20 sản phẩm; viết 3 test DB cho quan hệ, unique SKU và factory đơn nháp.
- **Đáp án định hướng:** dùng state theo role, gắn khách vào đúng creator; mật khẩu demo chỉ dùng local. Test kết nối DB riêng; RefreshDatabase cô lập dữ liệu test.
- **Tiêu chí đánh giá:** seed tái tạo được từ DB local trống; test không phụ thuộc ID cố định; xác minh DB test khác DB phát triển trước khi chạy.

#### Buổi 18 · Ngày 6 — SQL, query builder và tổng kết schema
- **Kiến thức:** where, join, groupBy, aggregate; index và EXPLAIN ở mức nhập môn.
- **Bài tập:** lấy sản phẩm sắp hết hàng, số khách theo sales, tổng dòng của một đơn; đối chiếu SQL với Eloquent.
- **Đáp án định hướng:** lọc stock dưới ngưỡng, group theo created_by, SUM(line_total_vnd) theo order_id. Không tải toàn bảng rồi lọc bằng PHP khi DB làm được.
- **Tiêu chí đánh giá:** 3 truy vấn đúng trên dữ liệu biết trước; giải thích được index hỗ trợ truy vấn nào; ERD và migration khớp nhau.

### Tuần 4 — CRUD hoàn chỉnh

#### Buổi 19 · Ngày 1 — Tạo và xem sản phẩm
- **Kiến thức:** resource controller, route model binding, API Resource.
- **Bài tập:** chuyển products từ dữ liệu mẫu sang DB; xây POST, GET danh sách và GET chi tiết; trả ProductResource.
- **Đáp án định hướng:** create request chỉ nhận sku/name/price_vnd/is_active, stock khởi đầu 0; stock nhập qua điều chỉnh tuần 7. Resource chỉ công khai các trường cần thiết.
- **Tiêu chí đánh giá:** POST 201; GET ID đúng 200 và ID thiếu 404; giá âm hoặc SKU trùng trả 422; có ít nhất 4 feature test.

#### Buổi 20 · Ngày 2 — Cập nhật và xóa sản phẩm
- **Kiến thức:** PATCH, unique khi cập nhật, xung đột FK, ngừng bán.
- **Bài tập:** thêm PATCH/DELETE; SKU của chính bản ghi vẫn hợp lệ; chặn xóa sản phẩm có item/movement; cập nhật is_active.
- **Đáp án định hướng:** `Rule::unique(...)->ignore($product)` với model đã resolve, không lấy ID ignore từ payload; kiểm tra tham chiếu và ánh xạ xung đột DB đã biết sang 409.
- **Tiêu chí đánh giá:** PATCH một trường giữ nguyên trường còn lại; xóa chưa dùng trả 204; xóa đã tham chiếu trả 409; stock không đổi qua PATCH.

#### Buổi 21 · Ngày 3 — CRUD khách hàng
- **Kiến thức:** tái sử dụng mô hình request/resource; FK và lịch sử giao dịch.
- **Bài tập:** xây đủ CRUD khách hàng, có name/email/phone; tạm dùng user seed cố định ở môi trường học trước tuần xác thực.
- **Đáp án định hướng:** tập trung gán creator tạm tại một chỗ và ghi TODO xóa ở buổi 28; không cho client chọn creator. Khách đã có order không được xóa.
- **Tiêu chí đánh giá:** đủ luồng tạo/xem/sửa/xóa; email rỗng được chấp nhận; khách có đơn trả 409 khi xóa; không triển khai API tạm này ra internet.

#### Buổi 22 · Ngày 4 — Tìm kiếm và phân trang
- **Kiến thức:** query parameters, allowlist sort, pagination, giới hạn kích thước trang.
- **Bài tập:** products hỗ trợ q, is_active, sort, per_page; customers tìm name/phone; per_page từ 1 đến 100, mặc định 15.
- **Đáp án định hướng:** validate query, map sort từ allowlist, thêm ID làm thứ tự phụ; nhóm các điều kiện OR tìm kiếm trong closure để sau này không phá scope quyền.
- **Tiêu chí đánh giá:** 35 sản phẩm chia trang đúng; sort trái phép trả 422; tìm kiếm rỗng không lỗi; query tìm kiếm không làm mất điều kiện lọc active.

#### Buổi 23 · Ngày 5 — Chuẩn hóa response và test CRUD
- **Kiến thức:** resource collection, links/meta, test JSON contract, dữ liệu nhạy cảm.
- **Bài tập:** hoàn thiện resource khách/sản phẩm và tối thiểu 8 test CRUD/phân trang; ghi ví dụ response vào tài liệu.
- **Đáp án định hướng:** dùng resource collection trên paginator; assert JSON path/structure và DB state; không trả password, token hay trường debug.
- **Tiêu chí đánh giá:** 8 test độc lập xanh; danh sách có data/links/meta; validation có errors; response của user nếu xuất hiện không lộ password hash.

#### Buổi 24 · Ngày 6 — Demo CRUD và sửa lỗi
- **Kiến thức:** kiểm tra tính nhất quán từ API tới DB; refactor nhỏ có test bảo vệ.
- **Bài tập:** demo tạo sản phẩm → tìm → sửa → ngừng bán; tạo khách → sửa → xóa; rà code lặp và TODO.
- **Đáp án định hướng:** chỉ tách phần lặp rõ ràng, không tạo generic repository để bọc mọi thao tác Eloquent; lưu collection và README cách seed.
- **Tiêu chí đánh giá:** người khác chạy được demo từ README; toàn bộ test xanh; có danh sách endpoint cần bảo vệ ở tuần 5.

### Tuần 5 — Xác thực và phân quyền

#### Buổi 25 · Ngày 1 — Đăng nhập bằng Sanctum
- **Kiến thức:** password hashing, personal access token, bearer token, 401; token API khác cookie/session SPA.
- **Bài tập:** xây login, GET /me; thêm HasApiTokens nếu cần; giới hạn endpoint login theo cả IP và định danh tài khoản.
- **Đáp án định hướng:** dùng Hash::check, thông báo sai thông tin chung; createToken trả plain token một lần; protect /me bằng auth:sanctum. Đây là API cho HTTP client, không phải bài cấu hình first-party SPA.
- **Tiêu chí đánh giá:** đúng tài khoản nhận token; sai mật khẩu 401; /me thiếu token 401; log không chứa mật khẩu/token.

#### Buổi 26 · Ngày 2 — Đăng xuất và vòng đời token
- **Kiến thức:** revoke token hiện tại, token hết hạn, nhiều thiết bị; thông tin bí mật trong môi trường test.
- **Bài tập:** DELETE /auth/token xóa token đang dùng; thêm test đăng nhập → /me → logout → /me.
- **Đáp án định hướng:** xóa `currentAccessToken()` trong luồng bearer token; cấu hình chính sách hết hạn rõ trong tài liệu. Không xóa tất cả token nếu yêu cầu chỉ đăng xuất thiết bị hiện tại.
- **Tiêu chí đánh giá:** token đã logout nhận 401; token thiết bị thứ hai vẫn hoạt động; endpoint logout không có token trả 401.

#### Buổi 27 · Ngày 3 — Policy cho sản phẩm
- **Kiến thức:** Gate/Policy, 403, authorize; role và token ability không đồng nghĩa quyền sở hữu.
- **Bài tập:** ProductPolicy: admin CRUD, sales chỉ xem sản phẩm active; đưa tất cả routes nghiệp vụ sau auth:sanctum.
- **Đáp án định hướng:** scope danh sách sales chỉ active; detail inactive trả 404 cho sales; thao tác ghi của sales trả 403. Policy kiểm tra quyền, không thay validation.
- **Tiêu chí đánh giá:** admin ghi được, sales không ghi được, guest 401; sales không xem detail inactive bằng cách đoán ID; test đủ 3 vai trò.

#### Buổi 28 · Ngày 4 — Quyền sở hữu khách hàng
- **Kiến thức:** authorization cấp bản ghi, IDOR/BOLA, scope truy vấn và nhóm OR.
- **Bài tập:** CustomerPolicy và scope cho index/show/update/delete; xóa creator tạm, gán từ user đăng nhập; thử user A truy cập khách B.
- **Đáp án định hướng:** admin xem tất cả, sales query trong tập khách mình sở hữu; ngoài phạm vi trả 404. Gộp q trong ngoặc logic để `OR phone LIKE` không vượt scope.
- **Tiêu chí đánh giá:** A không đọc/sửa/xóa khách B; không tìm ra B qua q; giả mạo created_by không đổi chủ sở hữu.

#### Buổi 29 · Ngày 5 — Ma trận test bảo mật
- **Kiến thức:** test quyền theo role × action × ownership; mass assignment và privilege escalation.
- **Bài tập:** viết ít nhất 12 test gồm guest, sales A/B, admin; thử gửi role, stock, created_by, password trong các payload không cho phép.
- **Đáp án định hướng:** tạo fixtures riêng từng test, dùng Sanctum::actingAs cho đa số test quyền; giữ test HTTP login thật riêng để kiểm chứng phát token.
- **Tiêu chí đánh giá:** đủ 12 test với cả status và DB không đổi khi bị chặn; admin cũng không sửa stock qua PATCH product; không endpoint công khai đổi role.

#### Buổi 30 · Ngày 6 — Kiểm tra xác thực/phân quyền toàn API
- **Kiến thức:** phân biệt 401/403/404; quyền ở danh sách và quan hệ lồng nhau.
- **Bài tập:** rà mọi route, demo hai sales; cập nhật collection dùng biến token; thêm tài liệu ma trận quyền.
- **Đáp án định hướng:** đối chiếu route:list với middleware và policy; kiểm tra response quan hệ không vô tình lộ khách ngoài scope. Health có thể công khai nhưng không lộ cấu hình.
- **Tiêu chí đánh giá:** mọi route nghiệp vụ cần token; 2 sales thấy tập dữ liệu đúng; toàn bộ test cũ được cập nhật xác thực và chạy xanh.

### Tuần 6 — Đơn hàng nháp và transaction

#### Buổi 31 · Ngày 1 — Hợp đồng tạo đơn và trạng thái
- **Kiến thức:** state machine, dữ liệu lồng nhau, PHP backed enum; validation hình dạng và kiểm tra nghiệp vụ.
- **Bài tập:** tạo OrderStatus; thiết kế POST /orders nhận customer_id và items[{product_id, quantity}]; viết 5 test acceptance ban đầu.
- **Đáp án định hướng:** items từ 1–50, product_id distinct, quantity integer 1–1.000; giá/tổng/creator không nhận từ client. Kiểm tra customer trong phạm vi user và product active.
- **Tiêu chí đánh giá:** hợp đồng có ví dụ đúng/sai; duplicate product bị từ chối; thiếu quyền khách không tạo được đơn; state diagram đúng đặc tả.

#### Buổi 32 · Ngày 2 — Tạo draft nguyên tử
- **Kiến thức:** DB::transaction, rollback, tạo model liên quan; tách action nghiệp vụ khỏi controller.
- **Bài tập:** viết CreateDraftOrder action; lưu order và items, snapshot giá/tên/SKU, tính tổng phía server.
- **Đáp án định hướng:** lấy các product một lượt, map theo ID; tính từng line bằng integer; tạo mọi bản ghi trong transaction. Trước tuần 7 endpoint chưa có bảo đảm idempotency.
- **Tiêu chí đánh giá:** 2 dòng 100.000×2 và 50.000×3 ra 350.000; total giả không có tác dụng; lỗi ở dòng thứ hai không để lại order/item dở dang.

#### Buổi 33 · Ngày 3 — Xem và lọc đơn hàng
- **Kiến thức:** nested resources, eager loading có chọn lọc, scoping quyền đơn.
- **Bài tập:** GET /orders và /orders/{id}; lọc status/customer_id; sales chỉ thấy đơn mình tạo; admin thấy tất cả.
- **Đáp án định hướng:** OrderPolicy và scope creator; Resource trả customer tóm tắt, item snapshot, total và status; eager load các quan hệ thực sự dùng.
- **Tiêu chí đánh giá:** sales không lấy được đơn khác kể cả qua filter; detail không tính lại giá từ product; danh sách có phân trang và không N+1 theo số đơn.

#### Buổi 34 · Ngày 4 — Thay dòng hàng draft
- **Kiến thức:** semantics PUT thay toàn bộ tập con, đồng bộ items, khóa bản ghi bảo vệ trạng thái.
- **Bài tập:** PUT /orders/{id}/items thay toàn bộ dòng và tính lại tổng; chỉ cho draft.
- **Đáp án định hướng:** transaction khóa order, kiểm tra quyền/trạng thái lại, validate và đọc giá hiện tại rồi thay items; cùng quy tắc khóa order được giữ ở confirm/cancel về sau.
- **Tiêu chí đánh giá:** dòng bỏ khỏi payload biến mất; giá snapshot được cập nhật theo lần sửa; request lỗi không xóa mất dòng cũ; đơn không draft trả 409.

#### Buổi 35 · Ngày 5 — Tách phép tính và test biên
- **Kiến thức:** pure function, unit test cho phép tính, feature test cho DB/HTTP; giới hạn số và tổng.
- **Bài tập:** tách bộ tính tổng; viết test quantity 1/1.000, giá 0/1 tỷ, 50 dòng và payload vượt giới hạn.
- **Đáp án định hướng:** unit test không cần boot app cho phép tính thuần; feature test kiểm validation và transaction. Tiền/tổng lưu bigint, môi trường PHP 64 bit.
- **Tiêu chí đánh giá:** biên hợp lệ cho tổng đúng; quantity 1.001, 51 dòng và giá âm bị chặn; không có làm tròn float; test không cần database cho hàm thuần.

#### Buổi 36 · Ngày 6 — Nghiệm thu đơn nháp
- **Kiến thức:** review invariant: total bằng tổng line, draft không trừ kho, dữ liệu lịch sử ổn định.
- **Bài tập:** demo tạo draft, sửa giá product, đọc đơn, sửa dòng draft; bổ sung test rollback và quyền sở hữu còn thiếu.
- **Đáp án định hướng:** đọc đơn sau đổi giá vẫn dùng snapshot cũ; chỉ lần PUT items mới lấy giá mới. Kho giữ nguyên trong tất cả thao tác draft.
- **Tiêu chí đánh giá:** chứng minh đủ 4 invariant bằng test/request; không thể sửa đơn người khác; controller chỉ điều phối request/action/resource.

### Tuần 7 — Tồn kho, cạnh tranh và chống trùng

#### Buổi 37 · Ngày 1 — Điều chỉnh tồn kho
- **Kiến thức:** ledger stock movements, delta có dấu, lockForUpdate, transaction.
- **Bài tập:** xây POST stock-adjustments cho admin với delta và reason; GET lịch sử có phân trang chỉ admin.
- **Đáp án định hướng:** khóa product, tính stock mới; nếu âm trả 409; update stock và insert movement trong cùng transaction. Không tự gán một con số stock mới bỏ qua lịch sử.
- **Tiêu chí đánh giá:** nhập +10 rồi -3 còn 7; giảm -8 bị chặn và không tạo movement; sales nhận 403; delta=0 bị từ chối.

#### Buổi 38 · Ngày 2 — Xác nhận đơn an toàn
- **Kiến thức:** kiểm tra lại dưới khóa, thứ tự khóa, transaction nhiều bảng.
- **Bài tập:** ConfirmOrder: khóa order, khóa products theo ID tăng dần, kiểm tra draft/active/stock, trừ kho và ghi movement.
- **Đáp án định hướng:** kiểm tra toàn bộ dòng trước khi cập nhật; dùng quantity đã lưu, không nhận quantity mới ở confirm. Thiếu một sản phẩm rollback toàn bộ; gán confirmed_at sau khi hợp lệ.
- **Tiêu chí đánh giá:** tồn đủ giảm đúng; một dòng thiếu kho khiến tất cả kho/trạng thái không đổi; confirm lần hai 409; tổng/giá snapshot giữ nguyên.

#### Buổi 39 · Ngày 3 — Hủy và hoàn kho một lần
- **Kiến thức:** chuyển trạng thái, at-most-once bằng khóa và trạng thái; tính nhất quán movement.
- **Bài tập:** CancelOrder hỗ trợ draft và confirmed; khóa theo cùng thứ tự với confirm; ghi cancelled_at.
- **Đáp án định hướng:** draft chỉ đổi trạng thái; confirmed hoàn quantity từng dòng và tạo movement dương; cancelled trả 409. Luồng hủy không yêu cầu product còn active để hoàn kho.
- **Tiêu chí đánh giá:** hủy draft không đổi stock; hủy confirmed hoàn đúng lượng; hủy lại không tăng stock; lỗi giữa chừng rollback toàn bộ.

#### Buổi 40 · Ngày 4 — Chống tạo đơn trùng
- **Kiến thức:** idempotency key, payload hash, unique constraint, tranh chấp insert.
- **Bài tập:** thêm bảng idempotency_requests và yêu cầu header khi POST /orders; test replay và cùng key khác payload.
- **Đáp án định hướng:** chuẩn hóa customer_id và items sắp theo product_id trước khi hash; unique(user_id,key). Lưu đơn và key cùng transaction; khi unique conflict, rollback rồi đọc bản ghi đã commit để trả đơn cũ/409. Không coi mọi QueryException là key trùng.
- **Tiêu chí đánh giá:** replay trả cùng ID và chỉ một order; payload khác 409; hai user dùng cùng key không va nhau; key thiếu/sai định dạng 422; ghi chính sách lưu key tối thiểu 30 ngày.

#### Buổi 41 · Ngày 5 — Thử nghiệm request đồng thời
- **Kiến thức:** lost update, overselling, transaction isolation; một test tuần tự không tạo cạnh tranh thật.
- **Bài tập:** chuẩn bị 2 draft cùng cần 4 sản phẩm trong kho 5; gửi confirm bằng hai tiến trình/kết nối MySQL đồng thời. Thử thêm hai create cùng idempotency key.
- **Đáp án định hướng:** dùng database integration riêng, fixtures đã commit, không bọc toàn thí nghiệm trong transaction test của tiến trình cha. Dùng điểm đồng bộ bắt đầu; ghi thời điểm/status và kiểm DB cuối cùng.
- **Tiêu chí đánh giá:** đúng 1 confirm thành công, request còn lại 409, stock cuối 1; 2 create cùng key tạo đúng 1 đơn; lưu cách chạy để tái hiện thí nghiệm.

#### Buổi 42 · Ngày 6 — Đối soát kho và tổng kết
- **Kiến thức:** invariant ledger; giả định stock bắt đầu 0 và mọi thay đổi đi qua movement.
- **Bài tập:** viết truy vấn đối soát product.stock với SUM(delta); chạy chuỗi nhập kho → confirm → cancel; bổ sung tối thiểu 10 test cho tuần 7.
- **Đáp án định hướng:** seed tồn bằng action điều chỉnh hoặc tạo opening movement tương ứng; không sửa stock trực tiếp trong demo đối soát. Điều tra chênh lệch theo order_id/reason.
- **Tiêu chí đánh giá:** mọi product khớp ledger; 10 test bao gồm lỗi và lặp request; chạy lại thí nghiệm cạnh tranh đạt; không có âm kho hay hoàn hai lần.

### Tuần 8 — Chất lượng mã và kiểm thử hồi quy

#### Buổi 43 · Ngày 1 — Ma trận kiểm thử nghiệp vụ
- **Kiến thức:** test theo rủi ro, happy path, negative path, trạng thái và ownership.
- **Bài tập:** lập ma trận tối thiểu 20 ca cho login, quyền, draft, confirm, cancel, kho, replay; triển khai các ca còn thiếu quan trọng nhất.
- **Đáp án định hướng:** mỗi ca nêu precondition, request và DB kỳ vọng; ưu tiên sai tiền, vượt quyền, thiếu kho, rollback. Test lỗi phải chứng minh dữ liệu không đổi.
- **Tiêu chí đánh giá:** 20 ca có liên kết tới test hoặc ghi rõ chưa tự động hóa; không ca nghiêm trọng nào còn thiếu bằng chứng; test không phụ thuộc thứ tự chạy.

#### Buổi 44 · Ngày 2 — Tổ chức action và dependency injection
- **Kiến thức:** controller mỏng, action nghiệp vụ, service container; tránh abstraction chưa cần.
- **Bài tập:** rà CreateDraftOrder, ReplaceDraftItems, ConfirmOrder, CancelOrder, AdjustStock; inject qua constructor/method; tách lỗi nghiệp vụ có code.
- **Đáp án định hướng:** mỗi action giữ transaction của thao tác; controller xử lý HTTP/resource; dùng Eloquent trực tiếp nếu đủ. Không tạo interface/repository chỉ để bọc lại mọi câu query.
- **Tiêu chí đánh giá:** test cũ xanh sau refactor; không lặp logic trừ/hoàn kho giữa controller; vị trí chịu trách nhiệm transaction rõ ràng.

#### Buổi 45 · Ngày 3 — Factory states và test dễ đọc
- **Kiến thức:** factory states có invariant, helper test, giảm fixture thừa.
- **Bài tập:** tạo helpers/fixtures draft, confirmed, cancelled; refactor 5 test dài hoặc phụ thuộc seed chung.
- **Đáp án định hướng:** fixture confirmed phải có stock/movements nhất quán, có thể đi qua action; test chuyển trạng thái tự thực hiện hành động đang kiểm chứng. Không mock phần DB cần chứng minh.
- **Tiêu chí đánh giá:** chạy riêng từng test vẫn xanh; fixture không tạo kho âm/lịch sử giả; tên test mô tả hành vi thất bại hoặc thành công rõ ràng.

#### Buổi 46 · Ngày 4 — Đo truy vấn và sửa N+1
- **Kiến thức:** query log/listener ở local, eager load, pagination; đo trước/sau.
- **Bài tập:** đo GET /orders với 5 và 50 đơn, mỗi đơn 3 items; sửa N+1; ghi số query và thời gian tham khảo trên cùng máy.
- **Đáp án định hướng:** eager load customer/items theo response; không load mọi movement hoặc product không dùng. Số query nên gần như cố định theo số đơn trong một trang.
- **Tiêu chí đánh giá:** có bảng đo trước/sau; query không tăng tuyến tính với từng item; response vẫn đúng; không giữ log SQL chứa dữ liệu nhạy cảm ở production.

#### Buổi 47 · Ngày 5 — Index và dữ liệu lớn hơn
- **Kiến thức:** EXPLAIN, index chọn lọc, giới hạn input; phân biệt tối ưu có bằng chứng và suy đoán.
- **Bài tập:** sinh khoảng 1.000 đơn, xem kế hoạch lọc theo creator/status/date; thêm một index có lý do nếu còn thiếu.
- **Đáp án định hướng:** chọn composite index dựa điều kiện thực tế; kiểm tra migration không thêm index trùng. Phân trang và allowlist sort giữ nguyên.
- **Tiêu chí đánh giá:** lưu EXPLAIN và giải thích index được chọn hoặc vì sao DB vẫn scan; không tải 1.000 đơn vào PHP để phân trang; test kết quả không thay đổi.

#### Buổi 48 · Ngày 6 — Mốc chất lượng giữa khóa
- **Kiến thức:** regression, formatting, nhận diện test không có giá trị.
- **Bài tập:** chạy suite, kiểm format bằng Laravel Pint nếu dự án có; sửa lỗi còn lại; demo toàn bộ vòng đời đơn dưới hai tài khoản.
- **Đáp án định hướng:** ưu tiên hoàn thiện tính đúng trước thêm tính năng; ghi danh sách giới hạn đã biết và số test thực có, không lấy số lượng test thay chất lượng.
- **Tiêu chí đánh giá:** suite xanh, format đạt, không TODO nghiêm trọng ở auth/kho/tiền; có bản demo dự án tới hết tuần 8.

### Tuần 9 — Tác vụ nền, xuất file và lịch chạy

#### Buổi 49 · Ngày 1 — Queue database và worker
- **Kiến thức:** job, queue connection, worker, retry, failed_jobs; phân biệt sync và database queue.
- **Bài tập:** cấu hình database queue, kiểm tra migration jobs đã có trước khi tạo thêm; tạo job log thông báo thử, chạy worker.
- **Đáp án định hướng:** request chỉ dispatch; job xử lý bởi worker riêng. Cố ý tạo lỗi để xem failed job; đặt số lần thử/timeout hữu hạn phù hợp bài học.
- **Tiêu chí đánh giá:** dừng worker thì job chờ, bật worker thì xử lý; job lỗi thấy được và retry được; không nhầm request thành công với job đã hoàn tất.

#### Buổi 50 · Ngày 2 — Event sau commit và thông báo
- **Kiến thức:** event/listener, dispatch sau commit, nguy cơ gửi trước rollback; giao hàng job có thể lặp.
- **Bài tập:** phát OrderConfirmed và gửi thông báo qua mail log hoặc notification log trong queue; test transaction rollback không phát thông báo có hiệu lực.
- **Đáp án định hướng:** dispatch event/job sau commit; chỉ truyền ID và đọc dữ liệu cần thiết trong worker. Ghi rõ thông báo có thể lặp khi retry; chưa tuyên bố exactly-once cho hệ thống mail ngoài.
- **Tiêu chí đánh giá:** confirm hợp lệ tạo thông báo; confirm lỗi không gửi; response confirm không chờ mail server; test fake được bổ sung một lần chạy worker thật.

#### Buổi 51 · Ngày 3 — Yêu cầu xuất CSV bất đồng bộ
- **Kiến thức:** trạng thái queued/processing/completed/failed, private storage, ownership của tác vụ.
- **Bài tập:** tạo report_exports gồm user_id, filters, status, path, error_message; POST export và GET trạng thái.
- **Đáp án định hướng:** validate bộ lọc, lưu creator và scope quyền lúc tạo; dispatch job sau commit; trả 202 và ID. User chỉ xem export của mình; admin không mặc nhiên công khai file của người khác.
- **Tiêu chí đánh giá:** tạo task trả 202; worker dừng vẫn xem được queued; user khác nhận 404; không trả đường dẫn nội bộ hoặc chi tiết lỗi nhạy cảm.

#### Buổi 52 · Ngày 4 — Sinh và tải CSV an toàn
- **Kiến thức:** đọc theo chunk, CSV escaping, formula injection, streaming/private download.
- **Bài tập:** job xuất đơn confirmed theo bộ lọc và quyền user; thêm download endpoint; giới hạn xuất tối đa 31 ngày trong bản học.
- **Đáp án định hướng:** dùng fputcsv, xử lý ô text bắt đầu bằng `=`, `+`, `-`, `@` theo chính sách chống công thức; ghi file tạm riêng rồi công bố path khi xong. Endpoint kiểm ownership và completed trước khi tải.
- **Tiêu chí đánh giá:** CSV đọc đúng tiếng Việt và dấu phẩy; user A không tải file B; task chưa xong trả 409; retry không công bố file dang dở; kiểm tra ít nhất một text có tiền tố công thức.

#### Buổi 53 · Ngày 5 — Scheduler dọn dữ liệu có giới hạn
- **Kiến thức:** Artisan command, scheduling trong routes/console.php, timezone, khóa chống chạy chồng.
- **Bài tập:** command xóa export/file quá 7 ngày, dọn idempotency key quá 30 ngày; lịch hằng ngày; thêm dry-run và giới hạn batch.
- **Đáp án định hướng:** chỉ chọn bản ghi hết hạn, không dọn export đang chạy; xóa file đúng disk/path đã quản lý; ghi tổng số xử lý. Cảnh báo trong tài liệu: replay sau khi key bị dọn có thể tạo đơn mới.
- **Tiêu chí đánh giá:** dry-run không xóa; chỉ bản ghi đủ tuổi bị dọn; file ngoài phạm vi không bị đụng; schedule:list hiển thị lịch đúng múi giờ đã chọn.

#### Buổi 54 · Ngày 6 — Thử lỗi và khôi phục queue
- **Kiến thức:** vận hành worker, retry an toàn, quyền truy cập file; test fake so với tích hợp thật.
- **Bài tập:** tạo một export lỗi, sửa nguyên nhân rồi retry; dừng/bật worker; viết hướng dẫn xử lý task stuck và failed.
- **Đáp án định hướng:** ghi trạng thái failed cùng lỗi đã làm sạch; retry có quy tắc đưa trạng thái về processing; kiểm tra trước khi ghi đè file, không tạo bản công bố trùng.
- **Tiêu chí đánh giá:** task hoàn thành sau retry và tải được; không lộ CSV công khai; hướng dẫn nêu rõ worker phải chạy và cách xác định job lỗi.

### Tuần 10 — Báo cáo và hợp đồng API

#### Buổi 55 · Ngày 1 — Báo cáo doanh số cơ bản
- **Kiến thức:** SUM/COUNT, group theo ngày, định nghĩa chỉ số; lọc trước aggregate.
- **Bài tập:** GET /reports/sales nhận from/to; trả số đơn, doanh số, giá trị trung bình; dùng bộ dữ liệu 2 confirmed, 1 draft, 1 cancelled.
- **Đáp án định hướng:** chỉ status confirmed, thời điểm confirmed_at; sales scope created_by trước SUM; trung bình bằng total/count với chính sách làm tròn VND ghi rõ; không có đơn thì total/count=0, trung bình=0.
- **Tiêu chí đánh giá:** kết quả khớp phép tính tay; draft/cancelled không đóng góp; sales không cộng doanh số user khác; khoảng ngày sai trả 422.

#### Buổi 56 · Ngày 2 — Top sản phẩm và biên thời gian
- **Kiến thức:** join item/order, tổng quantity/line total, UTC và ngày địa phương, cận thời gian.
- **Bài tập:** /reports/top-products xếp theo số lượng, hòa thì doanh số rồi product_id; test đơn lúc 23:59 và 00:00 giờ Việt Nam.
- **Đáp án định hướng:** convert đầu ngày from và đầu ngày sau to sang UTC; dùng >= start và < end. Nhóm theo product_id, dùng snapshot để giải thích lịch sử; quy định tên hiển thị nhất quán.
- **Tiêu chí đánh giá:** đơn đúng biên chỉ vào một ngày; không nhân đôi tổng do join sai; thứ tự hòa ổn định; export tuần 9 dùng lại cùng bộ lọc/thời gian/quyền.

#### Buổi 57 · Ngày 3 — Cache báo cáo có scope
- **Kiến thức:** cache key, TTL, dữ liệu cũ, rò rỉ qua cache dùng chung.
- **Bài tập:** cache báo cáo trong 60 giây; key chứa version, role, user hoặc global scope admin, from/to và tham số sort/limit đã chuẩn hóa.
- **Đáp án định hướng:** chấp nhận chậm tối đa 60 giây và ghi vào contract; test warm cache bằng sales A rồi đọc với B. Không dùng một key chung cho mọi người.
- **Tiêu chí đánh giá:** A/B không nhận dữ liệu nhau; cùng query có cache hit; hết TTL thấy thay đổi; tài liệu nói rõ độ trễ thay vì hứa thời gian thực.

#### Buổi 58 · Ngày 4 — Rate limit và giới hạn tài nguyên
- **Kiến thức:** throttle theo identity/IP, 429, giới hạn export và bộ lọc để tránh truy vấn quá nặng.
- **Bài tập:** thiết lập giới hạn login, API thông thường và export riêng; thêm test 429 cho ít nhất một nhóm; giới hạn range báo cáo tối đa 366 ngày.
- **Đáp án định hướng:** ví dụ học tập login 5/phút theo email chuẩn hóa + IP và thêm trần IP, API 120/phút/user, export 3/giờ/user; đưa ngưỡng vào cấu hình, không coi đây là con số tối ưu cho mọi hệ thống.
- **Tiêu chí đánh giá:** vượt ngưỡng nhận 429; user khác không bị dùng chung quota ngoài chủ ý; range quá dài 422; reset test không phụ thuộc lần chạy trước.

#### Buổi 59 · Ngày 5 — Tài liệu OpenAPI tối thiểu
- **Kiến thức:** paths, schemas, security scheme bearer, examples; tài liệu là hợp đồng có thể đối chiếu.
- **Bài tập:** viết OpenAPI cho login, products, orders, confirm/cancel, reports và exports; ghi các endpoint customers/kho còn lại bằng cùng mẫu.
- **Đáp án định hướng:** tách request/response schema; khai báo Idempotency-Key và ví dụ lỗi 409/422; integer cho tiền, enum cho trạng thái; đánh dấu auth công khai/chỉ admin rõ.
- **Tiêu chí đánh giá:** đủ endpoint trong bảng mục tiêu; tài liệu validate được bằng công cụ OpenAPI đang dùng; ví dụ create→confirm chạy thật; không khai báo trường client không được ghi.

#### Buổi 60 · Ngày 6 — Nghiệm thu báo cáo và API contract
- **Kiến thức:** kiểm thử từ dữ liệu chuẩn, đối chiếu API/CSV/tài liệu; regression hợp đồng.
- **Bài tập:** dựng dataset biết trước; so API reports với CSV, thử hai role và khoảng ngày; sửa khác biệt về tên trường/status.
- **Đáp án định hướng:** dùng chung report query/bộ lọc cho JSON và export; chuẩn hóa timezone ở một chỗ; trong bài kiểm tra tắt/chờ cache theo chính sách để tránh so khác thời điểm.
- **Tiêu chí đánh giá:** tổng API và CSV khớp cùng dữ liệu; quyền giống nhau; suite xanh; OpenAPI và collection không còn request lỗi do tài liệu cũ.

### Tuần 11 — Triển khai và vận hành

#### Buổi 61 · Ngày 1 — Rà soát bảo mật thực tế
- **Kiến thức:** bí mật, debug, HTTPS, CORS, token API và CSRF trong bối cảnh đúng.
- **Bài tập:** rà 10 điểm: secrets, debug, log, quyền, mass assignment, rate limit, private file, validation, error output, cấu hình web root.
- **Đáp án định hướng:** giữ secret ngoài Git, APP_DEBUG=false khi triển khai, chỉ phục vụ thư mục public, CORS allowlist khi có browser client. API bearer không được dùng làm hướng dẫn thay thế Sanctum cookie/CSRF cho SPA tương lai.
- **Tiêu chí đánh giá:** lưu checklist có bằng chứng/config đã làm sạch; không token/secret thật trong repo; test guest và hai sales vẫn chặn quyền; API lỗi không lộ stack trace.

#### Buổi 62 · Ngày 2 — CI chạy kiểm tra tự động
- **Kiến thức:** pipeline, dependency install từ lock, môi trường test, quality gate.
- **Bài tập:** cấu hình CI trên nền tảng repository đang dùng, hoặc script CI chạy local nếu chưa có tài khoản: PHP tương thích, composer install, key test, MySQL test, migrate/test và Pint.
- **Đáp án định hướng:** chỉ dùng secret test/sinh tạm; không đưa DB production vào CI. Tách thí nghiệm cạnh tranh nhiều tiến trình nếu runner chưa hỗ trợ và ghi cách chạy thủ công.
- **Tiêu chí đánh giá:** một lần chạy xanh có log; cố ý thêm assertion sai làm pipeline đỏ rồi khôi phục; mọi check kết thúc bằng exit code đáng tin cậy.

#### Buổi 63 · Ngày 3 — Chuẩn bị môi trường staging
- **Kiến thức:** cấu hình runtime, DB, filesystem permissions, release artifact, worker và scheduler.
- **Bài tập:** chuẩn bị staging trên máy/VM/môi trường test sẵn có; nếu chưa có hosting, dùng môi trường local tách biệt, không bắt buộc mua dịch vụ.
- **Đáp án định hướng:** viết runbook: PHP/extensions, composer install production từ lock, env/key cố định, DB riêng, public web root, quyền storage/bootstrap/cache. Seed demo chỉ ở staging, không seed mật khẩu demo vào production.
- **Tiêu chí đánh giá:** fresh setup chạy theo runbook; env không public; health trả 200; không trỏ nhầm DB dev/test; ghi rõ local staging chưa chứng minh vận hành internet/HTTPS.

#### Buổi 64 · Ngày 4 — Phát hành thử và smoke test
- **Kiến thức:** migrate --force trong quy trình triển khai, config/route cache, restart worker; điều kiện trước/sau release.
- **Bài tập:** triển khai một release lên staging; chạy login, CRUD tối thiểu, draft→confirm→cancel, báo cáo và export.
- **Đáp án định hướng:** backup trước migration; cài dependency, migration có kiểm soát, cache cấu hình/routes phù hợp, restart worker; kiểm tra worker và scheduler riêng với web process.
- **Tiêu chí đánh giá:** 7 nhóm smoke test đạt; queue xử lý được sau release; debug tắt; token/DB credential không nằm trong log triển khai.

#### Buổi 65 · Ngày 5 — Log, health và khôi phục sự cố
- **Kiến thức:** request ID, log nghiệp vụ, failed jobs, health check nhẹ; backup chỉ hữu ích khi restore được.
- **Bài tập:** theo dấu một lỗi bằng request ID; backup DB staging, restore vào DB thử khác; kiểm đếm orders/items/movements và chạy đối soát kho.
- **Đáp án định hướng:** log actor/order/action không ghi token; health công khai không lộ DB config. Backup gồm DB và private export nếu muốn giữ file; giữ APP_KEY an toàn khi dữ liệu cần nó.
- **Tiêu chí đánh giá:** tìm ra lỗi từ log; restore thành công và invariant kho đúng; thời gian restore được ghi thực tế; không phục hồi đè database đang dùng.

#### Buổi 66 · Ngày 6 — Diễn tập rollback và vận hành
- **Kiến thức:** rollback code khác rollback dữ liệu, migration tương thích ngược, quy trình sự cố.
- **Bài tập:** phát hành thay đổi nhỏ có thể hoàn tác, quay về release trước trên staging; viết runbook worker ngừng chạy, export lỗi, migration lỗi.
- **Đáp án định hướng:** tránh tự động migrate:rollback với dữ liệu đang có; ưu tiên migration tương thích và sửa tiến khi cần; backup/restore chỉ thử trong môi trường riêng. Khởi động lại worker theo release code.
- **Tiêu chí đánh giá:** smoke test đạt sau quay lại code; không mất dữ liệu đơn mới; runbook có dấu hiệu phát hiện, bước xử lý và cách xác minh đã phục hồi.

### Tuần 12 — Hoàn thiện và nghiệm thu

#### Buổi 67 · Ngày 1 — Đóng băng phạm vi và kiểm tra chấp nhận
- **Kiến thức:** acceptance test, severity, phạm vi release; phân biệt lỗi bắt buộc và ý tưởng nâng cấp.
- **Bài tập:** chạy lại 8 kịch bản buổi 06 và ma trận buổi 43; lập danh sách lỗi theo nghiêm trọng/cao/thấp; chọn phần sửa trong hai buổi tới.
- **Đáp án định hướng:** ưu tiên vượt quyền, sai tiền, kho, transaction, replay trước giao diện tài liệu; không thêm thanh toán, nhiều kho hoặc frontend tuần này.
- **Tiêu chí đánh giá:** mọi lỗi có bước tái hiện và kết quả kỳ vọng; không còn lỗ hổng nghiêm trọng chưa có kế hoạch sửa; liên kết được yêu cầu với bằng chứng kiểm tra.

#### Buổi 68 · Ngày 2 — Sửa lỗi tính đúng và bảo mật
- **Kiến thức:** regression test tái hiện trước khi sửa; tìm nguyên nhân gốc.
- **Bài tập:** sửa nhóm lỗi quyền/tiền/kho/trùng ưu tiên cao nhất; nếu không có lỗi, bổ sung test cạnh tranh edit-vs-confirm và cancel-vs-cancel.
- **Đáp án định hướng:** test đỏ mô tả lỗi → sửa nhỏ nhất đúng thiết kế → test xanh; mọi thao tác đổi trạng thái phải cùng quy tắc khóa order trước product.
- **Tiêu chí đánh giá:** lỗi không tái hiện; DB không đổi khi request bị chặn; cancel đồng thời chỉ hoàn một lần; toàn bộ suite cũ vẫn xanh.

#### Buổi 69 · Ngày 3 — Hoàn thiện độ ổn định và tài liệu
- **Kiến thức:** triage phần còn lại, tính nhất quán contract, hiệu năng đủ cho phạm vi học.
- **Bài tập:** sửa lỗi còn lại của export/report/response; chạy lại đo query trang orders và thí nghiệm cạnh tranh; cập nhật OpenAPI.
- **Đáp án định hướng:** chỉ tối ưu nơi có số đo; đảm bảo file hoàn thành mới tải được, filter cùng nghĩa ở CSV/JSON; ghi giới hạn chưa xử lý thay vì che bằng response 200.
- **Tiêu chí đánh giá:** không còn lỗi nghiêm trọng/cao; query không N+1; hai thí nghiệm confirm/idempotency đạt; mọi hạn chế còn lại có mô tả rõ và mức ảnh hưởng.

#### Buổi 70 · Ngày 4 — README để người khác chạy dự án
- **Kiến thức:** tài liệu tái lập, demo data, hướng dẫn test và vận hành; onboarding độc lập.
- **Bài tập:** hoàn thiện README: yêu cầu, cài đặt, env mẫu, migrate/seed, start API/worker/scheduler, test, tài khoản demo local, API docs và quy tắc nghiệp vụ.
- **Đáp án định hướng:** thử từ checkout sạch vào DB bỏ đi; copy đúng lệnh, không dựa vào cấu hình ngầm của máy hiện tại. Thêm ERD và sơ đồ trạng thái đơn.
- **Tiêu chí đánh giá:** fresh setup thành công theo tài liệu; không cần hỏi mật khẩu cá nhân; có hướng dẫn DB test riêng và concurrency test; collection dùng biến môi trường không chứa token thật.

#### Buổi 71 · Ngày 5 — Demo thử và tự bảo vệ thiết kế
- **Kiến thức:** trình bày theo hành vi người dùng, giải thích quyết định và giới hạn.
- **Bài tập:** demo 15–20 phút: login → nhập kho → tạo khách/đơn → confirm → báo cáo → export → cancel; tiếp theo demo thiếu kho, truy cập chéo và replay.
- **Đáp án định hướng:** chuẩn bị dữ liệu nhỏ, số tiền/tồn kho dự đoán trước; giải thích snapshot, transaction, khóa dòng, idempotency, queue sau commit và scope cache bằng ví dụ dự án.
- **Tiêu chí đánh giá:** demo không sửa DB thủ công; mọi kết quả khớp số dự đoán; trả lời được ít nhất 6 câu hỏi ở phần 6; ghi lại phần cần chỉnh trước nộp.

#### Buổi 72 · Ngày 6 — Nghiệm thu cuối khóa
- **Kiến thức:** release checklist, tự đánh giá có bằng chứng, xác định hướng học tiếp.
- **Bài tập:** chạy suite/format, smoke staging và các ca cạnh tranh; chấm rubric cuối khóa; gắn tag v1.0.0 sau khi đạt và lưu báo cáo nghiệm thu.
- **Đáp án định hướng:** ghi version PHP/Laravel/DB, commit/tag, số test thực tế và kết quả; đính kèm OpenAPI, collection, ERD, runbook, số đo query, log concurrency đã bỏ dữ liệu nhạy cảm.
- **Tiêu chí đánh giá:** tổng từ 80/100, không lỗi nghiêm trọng, đủ tài liệu và bằng chứng; nếu chưa đạt ghi chính xác tiêu chí còn thiếu và kế hoạch khắc phục, không tự đánh dấu hoàn thành.

## 5. Rubric cuối khóa /100

| Nhóm | Điểm | Bằng chứng |
|---|---:|---|
| CRUD và hợp đồng API | 15 | Đủ endpoints, validation, resource, phân trang, OpenAPI khớp |
| Auth và phân quyền | 20 | Token lifecycle; admin/sales; chống truy cập chéo ở list/detail/export |
| Đơn hàng, tiền, kho | 25 | Snapshot đúng; state machine; transaction; ledger; không overselling/hoàn hai lần |
| Kiểm thử và khả năng tái lập | 15 | Test hành vi/rollback, DB riêng, thí nghiệm đồng thời, setup sạch |
| Báo cáo, queue, export | 10 | Tổng/timezone/scope đúng; private CSV; retry; cache không lộ dữ liệu |
| Cấu trúc và hiệu năng | 5 | Action rõ; không N+1; index có bằng chứng; input có giới hạn |
| Triển khai và tài liệu | 10 | CI/script, staging, backup/restore, runbook, demo |

**Mức đạt:** 80–89 đạt mục tiêu; 90–100 vững trong phạm vi dự án học; dưới 80 cần sửa nhóm yếu. Điểm cao không thay thế điều kiện bắt buộc: không vượt quyền, không sai tổng, không âm kho, không ghi dở dang, không tạo đơn/hoàn kho trùng. Một lỗi nghiêm trọng còn tồn tại nghĩa là chưa nghiệm thu dù tổng điểm đủ.

## 6. Bộ dữ liệu mẫu và đáp án kiểm tra tích hợp

### Dữ liệu chuẩn

- Admin, sales A và sales B; khách CA thuộc A, CB thuộc B.
- P1: SKU LAP-01, giá 1.000.000 VND; nhập kho +5 bằng stock adjustment.
- P2: SKU MOU-01, giá 200.000 VND; nhập kho +10 bằng stock adjustment.
- A tạo O1 cho CA: P1×2 và P2×3 → tổng **2.600.000 VND**, trạng thái draft; kho vẫn 5/10.
- Đổi giá P1 thành 1.200.000; chỉ đọc O1 vẫn **2.600.000**. Nếu không sửa dòng draft mà confirm O1: kho còn **3/7**, tổng vẫn **2.600.000**.
- Confirm O1 lần hai: **409**, kho vẫn 3/7. Cancel O1: kho **5/10**, O1 cancelled. Cancel lần hai: **409**, kho không đổi. Báo cáo hiện tại loại O1 sau khi hủy.
- A dùng cùng Idempotency-Key và payload tạo draft: chỉ **1 order**. Replay trả 200 và ID cũ; tạo mới lần đầu 201. Payload khác với key cũ trả **409**. Tài liệu phải nói rõ replay trả representation hiện tại của đơn cũ, không đảm bảo status/body giống hệt lần đầu.
- B xem O1 hoặc CA: **404**. Sales tạo product hay điều chỉnh kho: **403**. Guest gọi nghiệp vụ: **401**.
- Tạo O2, O3 mỗi đơn cần P1×4 khi tồn 5 rồi confirm đồng thời: một đơn thành công, một **409**, tồn cuối **1**. Nếu ban đầu còn ảnh hưởng của đơn khác, reset fixture riêng trước thí nghiệm.

### Câu hỏi tự kiểm tra

1. **Vì sao không nhận total/price từ client?** Client có thể sửa payload; server lấy giá từ nguồn dữ liệu được quản lý và tự tính, snapshot giữ lịch sử.
2. **Validation đủ để tránh bán quá kho chưa?** Chưa. Hai request có thể cùng đọc tồn cũ; cần transaction và khóa/check lại trong vùng khóa.
3. **Transaction có thay thế phân quyền?** Không. Transaction bảo đảm tính nguyên tử; policy/scope bảo đảm ai được thao tác dữ liệu nào.
4. **Vì sao khóa order trước product?** Mọi thao tác sửa/xác nhận/hủy cùng tranh chấp trên một order; thứ tự khóa thống nhất giảm deadlock và bảo vệ trạng thái.
5. **Vì sao unique idempotency key vẫn cần transaction?** Unique chặn hai bản ghi key; transaction buộc key và đơn cùng thành công/thất bại, tránh đã tạo đơn nhưng chưa lưu key.
6. **Queue có tự bảo đảm gửi thông báo đúng một lần không?** Không. Job có thể retry; cần xử lý lặp theo tính chất tác vụ, và không hứa exactly-once với dịch vụ ngoài chỉ bằng queue.
7. **Test 20 lần confirm tuần tự chứng minh chống overselling chưa?** Chưa. Cần kết nối/tiến trình độc lập với thời gian thực thi chồng nhau và kiểm trạng thái DB cuối.
8. **Vì sao cache key có user/scope?** Cùng filter nhưng quyền khác tạo kết quả khác; key chung có thể lộ báo cáo người khác.
9. **Vì sao không tính lịch sử đơn từ giá product hiện tại?** Giá và tên thay đổi; snapshot cho biết giá trị được chốt lúc soạn đơn.
10. **Rollback code có tự khôi phục database không?** Không. Schema/data có vòng đời riêng; cần migration tương thích và quy trình phục hồi đã thử.

## 7. Tài liệu chính thức theo tuần

Các quy tắc nghiệp vụ, thời lượng và tiêu chí trong lộ trình là thiết kế cho dự án này. Các liên kết dưới đây là nguồn tra cứu Laravel; luôn giữ nhánh **12.x**, không chuyển sang tài liệu mặc định của phiên bản mới hơn.

| Tuần | Phần tài liệu cần đọc (chỉ đọc mục phục vụ bài trong 25 phút) |
|---|---|
| 1 | [Installation](https://laravel.com/docs/12.x/installation), [Configuration](https://laravel.com/docs/12.x/configuration), [Structure](https://laravel.com/docs/12.x/structure) |
| 2 | [Routing](https://laravel.com/docs/12.x/routing), [Middleware](https://laravel.com/docs/12.x/middleware), [Validation](https://laravel.com/docs/12.x/validation), [HTTP Tests](https://laravel.com/docs/12.x/http-tests) |
| 3 | [Migrations](https://laravel.com/docs/12.x/migrations), [Eloquent](https://laravel.com/docs/12.x/eloquent), [Relationships](https://laravel.com/docs/12.x/eloquent-relationships), [Database Testing](https://laravel.com/docs/12.x/database-testing) |
| 4 | [API Resources](https://laravel.com/docs/12.x/eloquent-resources), [Pagination](https://laravel.com/docs/12.x/pagination), [Queries](https://laravel.com/docs/12.x/queries) |
| 5 | [Sanctum](https://laravel.com/docs/12.x/sanctum), [Authorization](https://laravel.com/docs/12.x/authorization), [Hashing](https://laravel.com/docs/12.x/hashing) |
| 6–7 | [Database transactions](https://laravel.com/docs/12.x/database#database-transactions), [Pessimistic locking](https://laravel.com/docs/12.x/queries#pessimistic-locking), [Validation](https://laravel.com/docs/12.x/validation) |
| 8 | [Testing](https://laravel.com/docs/12.x/testing), [Container](https://laravel.com/docs/12.x/container), [Eager loading](https://laravel.com/docs/12.x/eloquent-relationships#eager-loading), [Pint](https://laravel.com/docs/12.x/pint) |
| 9 | [Queues](https://laravel.com/docs/12.x/queues), [Events](https://laravel.com/docs/12.x/events), [Filesystem](https://laravel.com/docs/12.x/filesystem), [Scheduling](https://laravel.com/docs/12.x/scheduling) |
| 10 | [Queries](https://laravel.com/docs/12.x/queries), [Cache](https://laravel.com/docs/12.x/cache), [Rate Limiting](https://laravel.com/docs/12.x/rate-limiting) |
| 11–12 | [Deployment](https://laravel.com/docs/12.x/deployment), [Logging](https://laravel.com/docs/12.x/logging), [Errors](https://laravel.com/docs/12.x/errors), [Testing](https://laravel.com/docs/12.x/testing) |

Đã đối chiếu các mục cài đặt, Sanctum, kiểm thử, database, authorization, queue và deployment với tài liệu nhánh Laravel 12.x khi soạn lộ trình ngày 30/10/2026. Không yêu cầu đọc hết tài liệu; mỗi buổi chỉ tra phần cần dùng và quay lại thực hành.

## 8. Mẫu nhật ký mỗi buổi

Sao chép mẫu này 72 lần hoặc dùng một file nhật ký duy nhất:

```text
Buổi: __ /72 | Ngày thực học: __ | Thời gian: 120 phút
Kiến thức tôi giải thích được:
Commit/tài liệu:
Request hoặc test chứng minh:
Tiêu chí đã đạt / chưa đạt:
Điểm tự chấm: __ /10
Vướng mắc và cách thử tiếp:
```

Sau khóa học, chọn một hướng mở rộng theo nhu cầu: frontend dùng Sanctum cookie đúng cách; thanh toán sandbox với webhook/idempotency; nhiều kho; hoặc quan sát hệ thống và load test. Các phần này nằm ngoài 72 buổi bắt buộc.
