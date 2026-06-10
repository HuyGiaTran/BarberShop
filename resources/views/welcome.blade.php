<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(233, 69, 96, 0.18), transparent 28%),
                linear-gradient(135deg, #101826 0%, #16213e 50%, #0f1724 100%);
            color: #f8fafc;
        }
        .hero-card {
            background: rgba(15, 23, 36, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }
        .badge-soft {
            background: rgba(233, 69, 96, 0.16);
            color: #ffb6c1;
            border: 1px solid rgba(233, 69, 96, 0.25);
        }
        .btn-primary {
            background: #e94560;
            border-color: #e94560;
        }
        .btn-primary:hover {
            background: #cf3852;
            border-color: #cf3852;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container py-5">
        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="hero-card rounded-4 shadow-lg p-4 p-md-5">
                    <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">Barber Shop Admin Workspace</span>
                    <h1 class="display-5 fw-bold mb-3">He thong khach hang dang duoc hoan thien.</h1>
                    <p class="lead text-light-emphasis mb-4">
                        Khu vuc quan tri da duoc bao ve bang phan quyen admin. Frontend danh cho khach hang se duoc trien khai o phase tiep theo.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Vao dashboard</a>
                            @else
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light btn-lg">Dang xuat</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Dang nhap</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Dang ky</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
