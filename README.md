# README

## Giới thiệu

RoseShop là hệ thống quản lý cửa hàng hoa được xây dựng bằng Laravel, hỗ trợ quản lý sản phẩm, nhập hàng, khách hàng, đơn hàng, hóa đơn và báo cáo thống kê.

## Công nghệ sử dụng

* Laravel
* PHP
* MySQL
* HTML/CSS
* JavaScript
* Bootstrap

# I. Các công việc đã thực hiện (Frontend)

## 1. Xây dựng Dashboard quản trị

### Chức năng Dashboard

* Thiết kế giao diện Dashboard tổng quan cho hệ thống RoseShop.
* Bổ sung menu **Tổng quan** trên Sidebar.
* Hiển thị các chỉ số tổng quan:

  * Tổng sản phẩm.
  * Tổng khách hàng.
  * Tổng đơn hàng.
  * Doanh thu.
* Hiển thị danh sách đơn hàng chờ xử lý.
* Hiển thị Top sản phẩm bán chạy.
* Hiển thị danh sách sản phẩm sắp hết hàng.
* Xây dựng biểu đồ doanh thu theo thời gian.

### Quy tắc thống kê Dashboard

#### Tổng đơn hàng

Chỉ số **Tổng đơn hàng** trên Dashboard được tính từ toàn bộ bản ghi trong bảng `hoa_don`, bao gồm tất cả các trạng thái:

* DELIVERED (Đã giao)
* PENDING (Chờ xử lý)
* CONFIRMED (Đã xác nhận)
* SHIPPING (Đang giao)
* CANCELLED (Đã hủy)

Ví dụ:

| Trạng thái    | Số lượng |
| ------------- | -------- |
| DELIVERED     | 14       |
| PENDING       | 9        |
| CONFIRMED     | 3        |
| SHIPPING      | 1        |
| CANCELLED     | 1        |
| **Tổng cộng** | **28**   |

Do đó:

* Tổng đơn hàng = 28
* Đơn hàng đã giao = 14

Hai chỉ số này mang ý nghĩa khác nhau và được sử dụng cho các mục đích thống kê khác nhau.

#### Doanh thu

Doanh thu chỉ được tính từ các đơn hàng đã hoàn thành giao hàng thành công (`DELIVERED`).

Các đơn hàng ở trạng thái:

* PENDING
* CONFIRMED
* SHIPPING
* CANCELLED

không được cộng vào doanh thu thực tế.

## 2. Kết nối Dashboard với cơ sở dữ liệu

* Thay thế toàn bộ dữ liệu mẫu (fake data) bằng dữ liệu thực từ MySQL.
* Thống kê số lượng sản phẩm từ bảng `san_pham`.
* Thống kê số lượng khách hàng từ bảng `khach_hang`.
* Thống kê số lượng đơn hàng từ bảng `hoa_don`.
* Thống kê doanh thu từ các đơn hàng đã giao thành công.
* Lấy danh sách sản phẩm sắp hết hàng từ kho.
* Lấy danh sách đơn hàng chờ xử lý.
* Thống kê Top 5 sản phẩm bán chạy.
* Thống kê doanh thu theo ngày phục vụ biểu đồ.

## 3. Chuẩn hóa Layout quản trị

* Xây dựng layout chung `layouts/admin.blade.php`.
* Thiết kế Sidebar cố định cho toàn bộ hệ thống quản trị.
* Tách riêng phần Content bằng `@yield('content')`.
* Loại bỏ cơ chế iframe.
* Đồng bộ giao diện giữa các module quản trị.

## 4. Refactor giao diện các chức năng

### Quản lý người dùng

* Chuyển sang sử dụng layout chung.
* Tách riêng CSS theo module.
* Scope toàn bộ CSS để tránh ảnh hưởng Sidebar và Dashboard.

### Quản lý khách hàng

* Chuyển sang sử dụng layout chung.
* Thiết kế lại giao diện hiện đại hơn.
* Giữ nguyên toàn bộ chức năng tìm kiếm, sắp xếp và xóa khách hàng.

### Quản lý sản phẩm

* Chuyển sang sử dụng layout chung.
* Đồng bộ giao diện với Dashboard và các module khác.
* Giữ nguyên toàn bộ nghiệp vụ quản lý sản phẩm.

### Các module khác

* Quản lý nhập hàng.
* Quản lý đơn hàng.
* Quản lý hóa đơn.
* Quản lý nhân viên.
* Báo cáo doanh thu.
* Báo cáo sản phẩm.

Tất cả được chuyển sang sử dụng layout chung để đảm bảo Sidebar cố định và giao diện thống nhất.

## 5. Kiểm tra và tối ưu

* Kiểm tra ảnh hưởng CSS giữa các module.
* Kiểm tra tính toàn vẹn của Controller, Model, Route và Database.
* Xác nhận chỉ thay đổi phần giao diện (View + CSS), không làm thay đổi nghiệp vụ hệ thống.
* Kiểm tra thống kê Dashboard với dữ liệu thực trong cơ sở dữ liệu.
* Đối chiếu số lượng đơn hàng theo trạng thái:

  * DELIVERED
  * PENDING
  * CONFIRMED
  * SHIPPING
  * CANCELLED
* Đảm bảo Dashboard hiển thị chính xác số liệu toàn hệ thống.
