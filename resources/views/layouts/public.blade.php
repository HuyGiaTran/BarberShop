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
        <style>
            /* Chatbot Styles */
            #chatbot-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: Arial, sans-serif;
            }
            #chatbot-button {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background-color: var(--custom-btn-bg-color, #bc9c22);
                color: #fff;
                border: none;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                transition: transform 0.3s;
            }
            #chatbot-button:hover {
                transform: scale(1.1);
            }
            #chatbot-window {
                display: none;
                width: 320px;
                height: 450px;
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                flex-direction: column;
                overflow: hidden;
                position: absolute;
                bottom: 80px;
                right: 0;
            }
            #chatbot-header {
                background-color: var(--custom-btn-bg-color, #bc9c22);
                color: #fff;
                padding: 15px;
                font-weight: bold;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            #chatbot-close {
                cursor: pointer;
                font-size: 20px;
            }
            #chatbot-messages {
                flex: 1;
                padding: 15px;
                overflow-y: auto;
                background-color: #f9f9f9;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .chat-msg {
                max-width: 80%;
                padding: 10px 15px;
                border-radius: 15px;
                font-size: 14px;
                line-height: 1.4;
            }
            .chat-msg.user {
                align-self: flex-end;
                background-color: #e0e0e0;
                color: #333;
                border-bottom-right-radius: 2px;
            }
            .chat-msg.bot {
                align-self: flex-start;
                background-color: var(--custom-btn-bg-color, #bc9c22);
                color: #fff;
                border-bottom-left-radius: 2px;
            }
            #chatbot-input-area {
                display: flex;
                border-top: 1px solid #ddd;
                padding: 10px;
                background: #fff;
            }
            #chatbot-input {
                flex: 1;
                border: 1px solid #ddd;
                padding: 8px 12px;
                border-radius: 20px;
                outline: none;
            }
            #chatbot-send {
                background: transparent;
                border: none;
                color: var(--custom-btn-bg-color, #bc9c22);
                font-size: 20px;
                cursor: pointer;
                margin-left: 10px;
            }
        </style>
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

        <!-- Chatbot UI -->
        <div id="chatbot-container">
            <button id="chatbot-button" title="Chat với chúng tôi">
                <i class="bi bi-chat-dots-fill"></i>
            </button>
            <div id="chatbot-window">
                <div id="chatbot-header">
                    <span>Trợ lý Barber AI</span>
                    <i class="bi bi-x" id="chatbot-close"></i>
                </div>
                <div id="chatbot-messages">
                    <div class="chat-msg bot">Xin chào! Tôi là trợ lý AI của Barber Shop. Tôi có thể giúp gì cho bạn?</div>
                </div>
                <div id="chatbot-input-area">
                    <input type="text" id="chatbot-input" placeholder="Nhập câu hỏi..." autocomplete="off">
                    <button id="chatbot-send"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT FILES -->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/click-scroll.js') }}"></script>
        <script src="{{ asset('js/custom.js') }}"></script>
        
        <!-- Chatbot Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatBtn = document.getElementById('chatbot-button');
                const chatWin = document.getElementById('chatbot-window');
                const closeBtn = document.getElementById('chatbot-close');
                const chatInput = document.getElementById('chatbot-input');
                const sendBtn = document.getElementById('chatbot-send');
                const msgsArea = document.getElementById('chatbot-messages');

                chatBtn.addEventListener('click', () => {
                    chatWin.style.display = chatWin.style.display === 'flex' ? 'none' : 'flex';
                });

                closeBtn.addEventListener('click', () => {
                    chatWin.style.display = 'none';
                });

                const appendMessage = (text, sender) => {
                    const msgDiv = document.createElement('div');
                    msgDiv.className = `chat-msg ${sender}`;
                    msgDiv.textContent = text;
                    msgsArea.appendChild(msgDiv);
                    msgsArea.scrollTop = msgsArea.scrollHeight;
                };

                const sendMessage = async () => {
                    const text = chatInput.value.trim();
                    if (!text) return;

                    appendMessage(text, 'user');
                    chatInput.value = '';
                    chatInput.disabled = true;
                    sendBtn.disabled = true;

                    // Loading indicator
                    const loadingId = 'loading-' + Date.now();
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'chat-msg bot';
                    loadingDiv.id = loadingId;
                    loadingDiv.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang trả lời...';
                    msgsArea.appendChild(loadingDiv);
                    msgsArea.scrollTop = msgsArea.scrollHeight;

                    try {
                        const response = await fetch('/api/chatbot/ask', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ message: text })
                        });

                        const data = await response.json();
                        document.getElementById(loadingId).remove();

                        if (response.ok && data.success) {
                            appendMessage(data.reply, 'bot');
                        } else {
                            appendMessage(data.message || 'Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau.', 'bot');
                        }
                    } catch (err) {
                        document.getElementById(loadingId).remove();
                        appendMessage('Lỗi mạng. Không thể gửi tin nhắn.', 'bot');
                    }

                    chatInput.disabled = false;
                    sendBtn.disabled = false;
                    chatInput.focus();
                };

                sendBtn.addEventListener('click', sendMessage);
                chatInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') sendMessage();
                });
            });
        </script>
        @stack('scripts')
    </body>
</html>