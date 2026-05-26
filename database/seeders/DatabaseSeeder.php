<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. TẠO TÀI KHOẢN ADMIN/TEST CHÍNH ĐỂ BẠN ĐĂNG NHẬP
        $admin = User::factory()->create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'), // Mật khẩu là: password
            'role' => 'admin',
        ]);

        // 2. TẠO 10 TÀI KHOẢN KHÁCH HÀNG
        $customerNames = [
            'Nguyễn Văn An', 'Trần Thị Bảo Ngọc', 'Lê Hoàng Hải', 'Phạm Đức Mạnh', 
            'Vũ Thu Quỳnh', 'Hoàng Thái Sơn', 'Đặng Minh Trí', 'Bùi Xuân Hùng', 
            'Đỗ Cẩm Tiên', 'Ngô Thanh Tùng'
        ];
        
        $customers = collect(); // Biến chứa danh sách khách hàng
        
        foreach ($customerNames as $index => $name) {
            // Lấy chữ cái đầu và tên để tạo email (VD: Nguyễn Văn An -> an.nguyen@gmail.com)
            $emailPrefix = 'khachhang' . ($index + 1); 
            
            $customers->push(User::factory()->create([
                'name' => $name,
                'email' => $emailPrefix . '@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]));
        }

        // 3. TẠO THỢ CẮT TÓC (BARBERS)
        $barberNames = ['Anh Tuấn', 'Hải Phong', 'Duy Thái'];
        $barbers = [];

        foreach ($barberNames as $index => $name) {
            // Mỗi barber cũng cần 1 tài khoản User liên kết
            $user = User::factory()->create([
                'name' => $name,
                'email' => 'tho' . ($index + 1) . '@gmail.com',
                'password' => Hash::make('password'),
            ]);

            // Tạo thông tin Barber
            $barbers[] = Barber::create([
                'user_id' => $user->id,
                'name' => $name,
                'phone' => '09' . rand(10000000, 99999999),
                'bio' => 'Thợ cắt tóc với hơn 5 năm kinh nghiệm. Thế mạnh: Undercut, Mohican, Side Part và uốn phồng.',
                'avatar' => null,
                'is_active' => true,
            ]);
        }

        // 4. TẠO MENU DỊCH VỤ (SERVICES)
        $servicesData = [
            ['name' => 'Cắt tóc nam tiêu chuẩn', 'price' => 60000, 'duration_minutes' => 30, 'description' => 'Tư vấn kiểu tóc, cắt gọn gàng và tạo kiểu bằng sáp/pomade.'],
            ['name' => 'Combo Cắt Gội Massage VIP', 'price' => 120000, 'duration_minutes' => 45, 'description' => 'Cắt tạo kiểu, gội đầu massage, cạo mặt êm ái, đắp mặt nạ.'],
            ['name' => 'Uốn phồng Hàn Quốc', 'price' => 250000, 'duration_minutes' => 90, 'description' => 'Uốn tóc tự nhiên, dễ vào nếp, bảo hành 1 tháng.'],
            ['name' => 'Nhuộm màu thời trang', 'price' => 300000, 'duration_minutes' => 120, 'description' => 'Nhuộm các màu nâu, vàng, đỏ... sử dụng thuốc nhuộm organic.'],
            ['name' => 'Tẩy tóc + Nhuộm khói/Bạch kim', 'price' => 500000, 'duration_minutes' => 180, 'description' => 'Tẩy tóc an toàn (2 lần) + Nhuộm màu sáng (xám khói, bạch kim).'],
            ['name' => 'Cạo mặt + Lấy ráy tai', 'price' => 40000, 'duration_minutes' => 20, 'description' => 'Thư giãn hoàn toàn với bộ dụng cụ được tiệt trùng 100%.'],
        ];

        $services = [];
        foreach ($servicesData as $item) {
            $services[] = Service::create($item);
        }

        // 5. TẠO NGẪU NHIÊN 20 LỊCH HẸN (APPOINTMENTS)
        $customerIds = $customers->pluck('id')->toArray();
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $times = ['08:30', '09:00', '10:00', '13:30', '15:00', '16:30', '18:00', '19:30'];

        for ($i = 0; $i < 20; $i++) {
            // Sinh ngày ngẫu nhiên trong khoảng 5 ngày trước đến 7 ngày tới
            $randomDate = Carbon::now()->addDays(rand(-5, 7))->format('d-m-Y');
            
            // Lấy ngẫu nhiên Thợ và Dịch vụ
            $randomBarber = $barbers[array_rand($barbers)];
            $randomService = $services[array_rand($services)];

            Appointment::create([
                'user_id' => $customerIds[array_rand($customerIds)], // Chọn khách ngẫu nhiên
                'barber_id' => $randomBarber->id,
                'service_id' => $randomService->id,
                'appointment_date' => $randomDate,
                'appointment_time' => $times[array_rand($times)], // Chọn giờ ngẫu nhiên
                'status' => $statuses[array_rand($statuses)],
                'notes' => rand(0, 1) ? 'Khách quen, yêu cầu cắt thật kỹ phần gáy.' : null,
            ]);
        }
    }
}
