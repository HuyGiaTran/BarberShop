@extends('layouts.public')

@section('title', 'Gentlemen\'s Barber Shop - Đặt lịch cắt tóc')
@section('content')

    {{-- Hero Section --}}
    <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-12">
                    <h1 class="text-white mb-lg-3 mb-4"><strong>Barber <em>Shop</em></strong></h1>
                    <p class="text-black">Get the most professional haircut for you</p>
                    <br>
                    <a class="btn custom-btn custom-border-btn custom-btn-bg-white smoothscroll me-2 mb-2" href="#section_2">About Us</a>
                    <a class="btn custom-btn smoothscroll mb-2" href="#section_3">What we do</a>
                </div>
            </div>
        </div>

        <div class="custom-block d-lg-flex flex-column justify-content-center align-items-center">
            <img src="{{ asset('images/vintage-chair-barbershop.jpg') }}" class="custom-block-image img-fluid" alt="">

            <h4><strong class="text-white">Hurry Up! Get good haircut.</strong></h4>

            <a href="#booking-section" class="smoothscroll btn custom-btn custom-btn-italic mt-3">Book a seat</a>
        </div>
    </section>

    {{-- About Section (Our Story & Barbers) --}}
    <section class="about-section section-padding" id="section_2">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-12 mx-auto">
                    <h2 class="mb-4">Best hairdressers</h2>
                    <div class="border-bottom pb-3 mb-5">
                        <p>Gentlemen's Barber Shop - Nơi mang đến cho bạn phong cách cắt tóc chuyên nghiệp nhất. Đội ngũ barber giàu kinh nghiệm của chúng tôi luôn sẵn sàng phục vụ bạn.</p>
                    </div>
                </div>

                <h6 class="mb-5">Meet Our Barbers</h6>

                @forelse($barbers as $barber)
                <div class="col-lg-5 col-12 custom-block-bg-overlay-wrap {{ $loop->odd ? 'me-lg-5' : '' }} mb-5 mb-lg-0 {{ $loop->index >= 2 ? 'mt-4 mt-lg-0' : '' }}">
                    <img src="{{ $barber->avatar ? asset('storage/' . $barber->avatar) : asset('images/barber/portrait-male-hairdresser-with-scissors.jpg') }}" 
                         class="custom-block-bg-overlay-image img-fluid" 
                         alt="{{ $barber->name }}">

                    <div class="team-info d-flex align-items-center flex-wrap">
                        <p class="mb-0">{{ $barber->name }}</p>

                        <ul class="social-icon ms-auto">
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-facebook"></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-instagram"></a>
                            </li>
                        </ul>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-center">Chưa có barber nào. Vui lòng quay lại sau.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Featured / Discount Section --}}
    <section class="featured-section section-padding">
        <div class="section-overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto">
                    <h2 class="mb-3">Get 32% Discount</h2>
                    <p>on every second week of the month</p>
                    <strong>Promo Code: BarBerMo</strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Services Section --}}
    <section class="services-section section-padding" id="section_3">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-12">
                    <h2 class="mb-5">Services</h2>
                </div>

                @forelse($services as $service)
                <div class="col-lg-6 col-12 mb-4">
                    <div class="services-thumb">
                        <img src="{{ asset('images/services/hairdresser-grooming-their-client.jpg') }}" class="services-image img-fluid" alt="{{ $service->name }}">

                        <div class="services-info d-flex align-items-end">
                            <h4 class="mb-0">{{ $service->name }}</h4>
                            <strong class="services-thumb-price">${{ number_format($service->price, 2) }}</strong>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p class="text-center">Chưa có dịch vụ nào.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Booking Section --}}
    <section class="booking-section section-padding" id="booking-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-12 mx-auto">
                    @guest
                    {{-- Chưa đăng nhập: hiển thị form nhưng disabled và có thông báo --}}
                    <div class="custom-form booking-form" id="bb-booking-form">
                        <div class="text-center mb-5">
                            <h2 class="mb-1">Book a seat</h2>
                            <p>Please fill out the form and we get back to you</p>
                        </div>

                        <div class="booking-form-body">
                            <div class="row">
                                <div class="col-lg-6 col-12">
                                    <input type="text" class="form-control" placeholder="Full name" disabled>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <input type="tel" class="form-control" placeholder="Mobile 010-020-0340" disabled>
                                </div>
                            
                                <div class="col-lg-6 col-12">
                                    <input class="form-control" type="time" value="18:30" disabled>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <select class="form-select form-control" disabled>
                                        <option selected>Select Barber</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <select class="form-select form-control" disabled>
                                        <option selected>Select Service</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <input type="date" class="form-control" disabled>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <input type="number" class="form-control" placeholder="Number of People" disabled>
                                </div>
                            </div>

                            <textarea rows="3" class="form-control" placeholder="Comment (Optional)" disabled></textarea>

                            <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                <a href="{{ route('login') }}" class="form-control text-center text-decoration-none" 
                                   style="background: var(--custom-btn-bg-color); border: none; border-radius: var(--border-radius-large); color: var(--white-color); font-size: var(--p-font-size); font-weight: var(--font-weight-medium); padding: 10px 20px; display: block;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để đặt lịch
                                </a>
                            </div>
                        </div>
                    </div>
                    @endguest
                    @auth
                    {{-- Đã đăng nhập: form hoạt động bình thường --}}
                    <form method="post" class="custom-form booking-form" id="booking-form" role="form">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                        <input type="hidden" name="status" value="pending">

                        <div class="text-center mb-5">
                            <h2 class="mb-1">Book a seat</h2>
                            <p>Please fill out the form and we get back to you</p>
                        </div>

                        <div id="booking-feedback" class="alert d-none" role="alert"></div>

                        <div class="booking-form-body">
                            <div class="row">
                                <div class="col-lg-6 col-12">
                                    <input type="text" name="customer_name" id="bb-name" class="form-control" placeholder="Full name" value="{{ Auth::user()->name }}" readonly>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <input type="email" class="form-control" name="customer_email" placeholder="Email" value="{{ Auth::user()->email }}" readonly>
                                </div>
                            
                                <div class="col-lg-6 col-12">
                                    <select class="form-select form-control" name="appointment_time" id="bb-time" required disabled>
                                        <option selected value="">Chọn barber và ngày trước</option>
                                    </select>
                                    <div class="small text-muted mt-2" id="bb-time-help">
                                        Khung giờ trống sẽ tự động cập nhật theo barber và ngày bạn chọn.
                                    </div>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <select class="form-select form-control" name="barber_id" id="bb-barber" aria-label="Select Barber" required>
                                        <option selected value="">Select Barber</option>
                                        @foreach($barbers as $barber)
                                        <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <select class="form-select form-control" name="service_id" id="bb-service" aria-label="Select Service" required>
                                        <option selected value="">Select Service</option>
                                        @foreach($services as $service)
                                        <option
                                            value="{{ $service->id }}"
                                            data-barber-id="{{ $service->barber_id ?? '' }}"
                                            data-duration="{{ $service->duration_minutes }}"
                                        >
                                            {{ $service->name }} - ${{ number_format($service->price, 2) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="small text-muted mt-2" id="bb-service-help">
                                        Chọn barber để xem các dịch vụ phù hợp.
                                    </div>
                                </div>

                                <div class="col-lg-6 col-12">
                                    <input type="date" name="appointment_date" id="bb-date" class="form-control" placeholder="Date" min="{{ now()->toDateString() }}" required>
                                </div>
                            </div>

                            <textarea name="notes" rows="3" class="form-control" id="bb-message" placeholder="Comment (Optional)"></textarea>

                            <div class="col-lg-4 col-md-10 col-8 mx-auto">
                                <button type="submit" class="form-control">Submit Booking</button>
                            </div>
                        </div>
                    </form>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- Price List Section --}}
    <section class="price-list-section section-padding" id="section_4">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-12">
                    <div class="price-list-thumb-wrap">
                        <div class="mb-4">
                            <h2 class="mb-2">Price List</h2>
                            <strong>Starting at $25</strong>
                        </div>

                        @forelse($services as $service)
                        <div class="price-list-thumb">
                            <h6 class="d-flex">
                                {{ $service->name }}
                                <span class="price-list-thumb-divider"></span>
                                <strong>${{ number_format($service->price, 2) }}</strong>
                            </h6>
                        </div>
                        @empty
                        <p>Chưa có dịch vụ nào.</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-4 col-12 custom-block-bg-overlay-wrap mt-5 mb-5 mb-lg-0 mt-lg-0 pt-3 pt-lg-0">
                    <img src="{{ asset('images/vintage-chair-barbershop.jpg') }}" class="custom-block-bg-overlay-image img-fluid" alt="">
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Section --}}
    <section class="contact-section" id="section_5">
        <div class="section-padding section-bg">
            <div class="container">
                <div class="row">   
                    <div class="col-lg-8 col-12 mx-auto">
                        <h2 class="text-center">Say hello</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-padding">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-12">
                        <h5 class="mb-3"><strong>Contact</strong> Information</h5>

                        <p class="text-white d-flex mb-1">
                            <a href="tel: 120-240-3600" class="site-footer-link">
                                (+49) 120-240-3600
                            </a>
                        </p>

                        <p class="text-white d-flex">
                            <a href="mailto:info@yourgmail.com" class="site-footer-link">
                                hello@barber.beauty
                            </a>
                        </p>

                        <ul class="social-icon">
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-facebook"></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-twitter"></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-instagram"></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-youtube"></a>
                            </li>
                            <li class="social-icon-item">
                                <a href="#" class="social-icon-link bi-whatsapp"></a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-5 col-12 contact-block-wrap mt-5 mt-lg-0 pt-4 pt-lg-0 mx-auto">
                        <div class="contact-block">
                            <h6 class="mb-0">
                                <i class="custom-icon bi-shop me-3"></i>
                                <strong>Open Daily</strong>
                                <span class="ms-auto">10:00 AM - 8:00 PM</span>
                            </h6>
                        </div>
                    </div>

                    <div class="col-lg-12 col-12 mx-auto mt-5 pt-5">
                        <iframe class="google-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7702.122299518348!2d13.396786616231472!3d52.531268574169616!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47a85180d9075183%3A0xbba8c62c3dc41a7d!2sBarbabella%20Barbershop!5e1!3m2!1sen!2sth!4v1673886261201!5m2!1sen!2sth" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-12">
                    <h4 class="site-footer-title mb-4">Our Branches</h4>
                </div>

                <div class="col-lg-4 col-md-6 col-11">
                    <div class="site-footer-thumb">
                        <strong class="mb-1">Grünberger</strong>
                        <p>Grünberger Str. 31, 10245 Berlin, Germany</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-11">
                    <div class="site-footer-thumb">
                        <strong class="mb-1">Behrenstraße</strong>
                        <p>Behrenstraße 27, 10117 Berlin, Germany</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-11">
                    <strong class="mb-1">Weinbergsweg</strong>
                    <p>Weinbergsweg 23, 10119 Berlin, Germany</p>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-12 mt-4">
                        <p class="copyright-text mb-0">Copyright © 2036 Barber Shop 
                        - Design: <a href="https://templatemo.com" rel="nofollow" target="_blank">TemplateMo</a></p>
                    </div>

                    <div class="col-lg-2 col-md-3 col-3 mt-lg-4 ms-auto">
                        <a href="#section_1" class="back-top-icon smoothscroll" title="Back Top">
                            <i class="bi-arrow-up-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('booking-form');
    const feedback = document.getElementById('booking-feedback');

    if (!form || !feedback) {
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    const defaultButtonLabel = submitButton ? submitButton.textContent : 'Submit Booking';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const barberSelect = document.getElementById('bb-barber');
    const serviceSelect = document.getElementById('bb-service');
    const dateInput = document.getElementById('bb-date');
    const timeSelect = document.getElementById('bb-time');
    const serviceHelp = document.getElementById('bb-service-help');
    const timeHelp = document.getElementById('bb-time-help');
    const serviceOptions = Array.from(serviceSelect?.querySelectorAll('option') ?? []);

    const showFeedback = (type, message) => {
        feedback.className = `alert alert-${type}`;
        feedback.textContent = message;
    };

    const resetTimeOptions = (placeholder, disabled = true) => {
        if (!timeSelect) {
            return;
        }

        timeSelect.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        option.selected = true;
        timeSelect.appendChild(option);
        timeSelect.disabled = disabled;
    };

    const filterServicesByBarber = () => {
        if (!barberSelect || !serviceSelect) {
            return;
        }

        const selectedBarberId = barberSelect.value;
        let visibleCount = 0;

        serviceOptions.forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const optionBarberId = option.dataset.barberId ?? '';
            const matches = !selectedBarberId || !optionBarberId || optionBarberId === selectedBarberId;

            option.hidden = !matches;

            if (matches) {
                visibleCount++;
            }
        });

        if (!serviceOptions.find((option) => option.value === serviceSelect.value && !option.hidden)) {
            serviceSelect.value = '';
        }

        if (serviceHelp) {
            serviceHelp.textContent = selectedBarberId
                ? (visibleCount > 0
                    ? 'Danh sách dịch vụ đã được lọc theo barber bạn chọn.'
                    : 'Barber này hiện chưa có dịch vụ riêng. Bạn có thể chọn dịch vụ mặc định nếu có.')
                : 'Chọn barber để xem các dịch vụ phù hợp.';
        }
    };

    const fetchAvailableSlots = async () => {
        if (!barberSelect || !dateInput || !timeSelect) {
            return;
        }

        const barberId = barberSelect.value;
        const appointmentDate = dateInput.value;

        if (!barberId || !appointmentDate) {
            resetTimeOptions('Chọn barber và ngày trước', true);

            if (timeHelp) {
                timeHelp.textContent = 'Khung giờ trống sẽ tự động cập nhật theo barber và ngày bạn chọn.';
            }

            return;
        }

        resetTimeOptions('Đang tải khung giờ...', true);

        try {
            const response = await fetch(`/api/barbers/${barberId}/slots?date=${encodeURIComponent(appointmentDate)}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể lấy khung giờ trống.');
            }

            const slots = Array.isArray(result.data) ? result.data : [];
            const isOnLeave = Boolean(result.meta?.is_on_leave);

            resetTimeOptions(
                slots.length > 0 ? 'Chọn khung giờ trống' : 'Không còn khung giờ trống',
                slots.length === 0
            );

            slots.forEach((slot) => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = slot;
                timeSelect.appendChild(option);
            });

            if (timeHelp) {
                timeHelp.textContent = slots.length > 0
                    ? 'Đã tải khung giờ trống cho barber và ngày bạn chọn.'
                    : (isOnLeave
                        ? 'Barber đang nghỉ phép trong ngày này. Vui lòng chọn ngày hoặc barber khác.'
                        : 'Ngày này đã kín lịch. Vui lòng chọn ngày hoặc barber khác.');
            }
        } catch (error) {
            resetTimeOptions('Không tải được khung giờ', true);

            if (timeHelp) {
                timeHelp.textContent = 'Không thể tải khung giờ trống lúc này. Vui lòng thử lại sau.';
            }
        }
    };

    barberSelect?.addEventListener('change', () => {
        filterServicesByBarber();
        fetchAvailableSlots();
    });

    dateInput?.addEventListener('change', fetchAvailableSlots);

    filterServicesByBarber();
    resetTimeOptions('Chọn barber và ngày trước', true);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Đang gửi...';
        }

        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const response = await fetch('/api/appointments', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json().catch(() => ({}));

            if (response.ok && result.success) {
                showFeedback('success', result.message || 'Đặt lịch thành công!');
                form.reset();
                filterServicesByBarber();
                resetTimeOptions('Chọn barber và ngày trước', true);

                if (serviceHelp) {
                    serviceHelp.textContent = 'Chọn barber để xem các dịch vụ phù hợp.';
                }

                if (timeHelp) {
                    timeHelp.textContent = 'Khung giờ trống sẽ tự động cập nhật theo barber và ngày bạn chọn.';
                }

                return;
            }

            const validationMessage = result.errors
                ? Object.values(result.errors).flat().join(' ')
                : null;

            showFeedback('danger', validationMessage || result.message || 'Có lỗi xảy ra khi đặt lịch.');
        } catch (error) {
            showFeedback('danger', 'Không thể kết nối tới server. Vui lòng thử lại sau.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = defaultButtonLabel;
            }
        }
    });
});
</script>
@endpush
