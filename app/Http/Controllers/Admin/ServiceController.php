<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Hiển thị danh sách dịch vụ (có lọc theo giá)
     */
    public function index(Request $request)
    {
        $query = Service::query();

        // Lọc theo giá tối thiểu
        if ($request->filled('min_price')) {
            $minPrice = (float) $request->min_price;
            $query->where('price', '>=', $minPrice);
        }

        // Lọc theo giá tối đa
        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->max_price;
            $query->where('price', '<=', $maxPrice);
        }

        // Lọc theo tên dịch vụ
        if ($request->filled('search')) {
        $query->where('name', 'like', $request->search . '%');
        }

        $services = $query->with('barber')->paginate(10);

        return view('services.index', compact('services'));
    }
    /**
     * Hiển thị form thêm dịch vụ
     */
    public function create()
    {
        $barbers = Barber::all();
        return view('services.create', compact('barbers'));
    }

    /**
     * Lưu dịch vụ mới vào database
     */
    public function store(Request $request)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'barber_id' => 'nullable|exists:barbers,id',
        ], [
            'name.required' => 'Vui lòng nhập tên dịch vụ',
            'name.max' => 'Tên dịch vụ không được vượt quá 255 ký tự',
            'price.required' => 'Vui lòng nhập giá dịch vụ',
            'price.numeric' => 'Giá phải là số',
            'price.min' => 'Giá không được âm',
            'duration_minutes.required' => 'Vui lòng nhập thời gian dịch vụ',
            'duration_minutes.integer' => 'Thời gian phải là số nguyên',
            'duration_minutes.min' => 'Thời gian phải ít nhất 1 phút',
            'barber_id.exists' => 'Barber không hợp lệ',
        ]);

        // Tạo dịch vụ mới
        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', 'Thêm dịch vụ thành công!');
    }

    /**
     * Hiển thị chi tiết dịch vụ
     */
    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    /**
     * Hiển thị form sửa dịch vụ
     */
    public function edit(Service $service)
    {
        $barbers = Barber::all();
        return view('services.edit', compact('service', 'barbers'));
    }

    /**
     * Cập nhật dịch vụ
     */
    public function update(Request $request, Service $service)
    {
        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'barber_id' => 'nullable|exists:barbers,id',
        ], [
            'name.required' => 'Vui lòng nhập tên dịch vụ',
            'name.max' => 'Tên dịch vụ không được vượt quá 255 ký tự',
            'price.required' => 'Vui lòng nhập giá dịch vụ',
            'price.numeric' => 'Giá phải là số',
            'price.min' => 'Giá không được âm',
            'duration_minutes.required' => 'Vui lòng nhập thời gian dịch vụ',
            'duration_minutes.integer' => 'Thời gian phải là số nguyên',
            'duration_minutes.min' => 'Thời gian phải ít nhất 1 phút',
            'barber_id.exists' => 'Barber không hợp lệ',
        ]);

        // Cập nhật dịch vụ
        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'Cập nhật dịch vụ thành công!');
    }

    /**
     * Xóa dịch vụ
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Xóa dịch vụ thành công!');
    }
}