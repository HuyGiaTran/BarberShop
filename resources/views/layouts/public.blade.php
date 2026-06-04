<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="BarberShop - Đặt lịch cắt tóc chuyên nghiệp">
        <meta name="author" content="">

        <title>@yield('title', 'Gentlemen\'s Barber Shop')</title>

        <!-- CSS FILES -->        
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;500&display=swap" rel="stylesheet">

        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
        <link href="{{ asset('css/templatemo-barber-shop.css') }}" rel="stylesheet">
        @stack('styles')
    </head>
    
    <body>

        <div class="container-fluid">
            <div class="row">

                <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <nav id="sidebarMenu" class="col-md-4 col-lg-3 d-md-block sidebar collapse p-0">

                    <div class="position-sticky sidebar-sticky d-flex flex-column justify-content-center align-items-center">
                        <a class="navbar-brand" href="{{ route('home') }}">
                            <img src="{{ asset('images/templatemo-barber-logo.png') }}" class="logo-image img-fluid" alt="Barber Shop Logo">
                        </a>

                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link click-scroll" href="#section_1">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link click-scroll" href="#section_2">Our Story</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link click-scroll" href="#section_3">Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link click-scroll" href="#section_4">Price List</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link click-scroll" href="#section_5">Contact</a>
                            </li>
                            @auth
                            @if(Auth::user()->role === 'admin')
                            <li class="nav-item mt-4 pt-3" style="border-top: 1px solid rgba(0,0,0,0.1);">
                                <a class="nav-link" href="{{ route('dashboard') }}" style="color: var(--secondary-color);">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a>
                            </li>
                            @endif
                            <li class="nav-item" style="border-top: 1px solid rgba(0,0,0,0.1);">
                                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                                    @csrf
                                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                                        style="font-size: 16px; color: var(--dark-color);"
                                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                                        <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                            @endauth
                            @guest
                            <li class="nav-item mt-4 pt-3" style="border-top: 1px solid rgba(0,0,0,0.1);">
                                <a class="nav-link" href="{{ route('login') }}" style="font-size: 16px;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}" style="font-size: 16px;">
                                    <i class="bi bi-person-plus me-2"></i>Đăng ký
                                </a>
                            </li>
                            @endguest
                        </ul>
                    </div>
                </nav>
                
                <div class="col-md-8 ms-sm-auto col-lg-9 p-0">
                    @yield('content')
                </div>

            </div>
        </div>

        <!-- JAVASCRIPT FILES -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/click-scroll.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        @stack('scripts')
    </body>
</html>