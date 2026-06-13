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
            /* ===== SIDEBAR MENU STYLES ===== */
            .sidebar .nav-link {
                font-size: 16px;
                padding: 12px 20px;
                transition: all 0.3s ease;
                border-radius: 6px;
                margin: 2px 10px;
                color: var(--dark-color);
            }
            .sidebar .nav-link:hover {
                transform: scale(1.05);
                background-color: #d4edda !important;
                color: #155724;
                font-weight: 600;
            }
            .sidebar .nav-link:active,
            .sidebar .nav-link:focus {
                transform: scale(1.05);
                background-color: #c3e6cb !important;
            }
            .sidebar .nav-item.logout-item .nav-link,
            .sidebar .nav-item.logout-item button {
                transition: all 0.3s ease;
                border-radius: 6px;
                margin: 2px 10px;
            }
            .sidebar .nav-item.logout-item .nav-link:hover,
            .sidebar .nav-item.logout-item button:hover {
                transform: scale(1.05);
                background-color: #f8d7da !important;
                color: #721c24 !important;
                font-weight: 600;
            }
            .sidebar .navbar-brand .logo-image {
                transition: transform 0.3s ease;
            }
            .sidebar .navbar-brand:hover .logo-image {
                transform: scale(1.08);
            }

            /* Modern Premium Chatbot Styles */
            #chatbot-container {
                position: fixed;
                bottom: 30px;
                right: 30px;
                z-index: 9999;
                font-family: 'Unbounded', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            #chatbot-button {
                width: 65px;
                height: 65px;
                border-radius: 50%;
                background: linear-gradient(135deg, #bc9c22 0%, #d4b84a 100%);
                color: #fff;
                border: none;
                box-shadow: 0 10px 25px rgba(188, 156, 34, 0.4);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
            }
            #chatbot-button::before {
                content: '';
                position: absolute;
                inset: -5px;
                border-radius: 50%;
                background: linear-gradient(135deg, #bc9c22 0%, #d4b84a 100%);
                opacity: 0.3;
                filter: blur(8px);
                z-index: -1;
                transition: opacity 0.3s;
            }
            #chatbot-button:hover {
                transform: scale(1.08) translateY(-5px);
            }
            #chatbot-button:hover::before {
                opacity: 0.6;
            }
            #chatbot-window {
                display: none;
                width: 380px;
                height: 600px;
                max-height: 80vh;
                background-color: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 24px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(255,255,255,0.5) inset;
                flex-direction: column;
                overflow: hidden;
                position: absolute;
                bottom: 85px;
                right: 0;
                transform-origin: bottom right;
                animation: chatPop 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                border: 1px solid rgba(188, 156, 34, 0.2);
            }
            @keyframes chatPop {
                0% { opacity: 0; transform: scale(0.9) translateY(20px); }
                100% { opacity: 1; transform: scale(1) translateY(0); }
            }
            #chatbot-header {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                color: #fff;
                padding: 20px;
                font-weight: 500;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #bc9c22;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                z-index: 2;
            }
            #chatbot-header span {
                font-size: 1.1rem;
                letter-spacing: 0.5px;
            }
            #chatbot-close {
                cursor: pointer;
                font-size: 24px;
                transition: transform 0.3s, color 0.3s;
                opacity: 0.8;
            }
            #chatbot-close:hover {
                transform: rotate(90deg);
                color: #bc9c22;
                opacity: 1;
            }
            #chatbot-messages {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
                display: flex;
                flex-direction: column;
                gap: 15px;
                scroll-behavior: smooth;
            }
            #chatbot-messages::-webkit-scrollbar {
                width: 6px;
            }
            #chatbot-messages::-webkit-scrollbar-track {
                background: transparent;
            }
            #chatbot-messages::-webkit-scrollbar-thumb {
                background-color: rgba(188, 156, 34, 0.3);
                border-radius: 10px;
            }
            #chatbot-messages::-webkit-scrollbar-thumb:hover {
                background-color: rgba(188, 156, 34, 0.6);
            }
            .chat-msg {
                max-width: 85%;
                padding: 14px 18px;
                border-radius: 20px;
                font-size: 14px;
                line-height: 1.5;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                box-shadow: 0 2px 10px rgba(0,0,0,0.03);
                position: relative;
                animation: msgFadeIn 0.3s ease-out forwards;
                word-wrap: break-word;
            }
            @keyframes msgFadeIn {
                0% { opacity: 0; transform: translateY(10px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .chat-msg.user {
                align-self: flex-end;
                background: linear-gradient(135deg, #f0f0f0 0%, #e4e4e4 100%);
                color: #2b2b2b;
                border-bottom-right-radius: 4px;
            }
            .chat-msg.bot {
                align-self: flex-start;
                background: linear-gradient(135deg, #bc9c22 0%, #a68a1d 100%);
                color: #ffffff;
                border-bottom-left-radius: 4px;
                box-shadow: 0 4px 15px rgba(188, 156, 34, 0.2);
            }
            .chat-msg.bot strong {
                font-weight: 600;
                color: #fff;
            }
            #chatbot-input-area {
                display: flex;
                border-top: 1px solid rgba(0,0,0,0.05);
                padding: 15px;
                background: #fff;
                align-items: center;
                gap: 10px;
                z-index: 2;
            }
            .chatbot-img-btn {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: #6c757d;
                transition: all 0.3s;
                flex-shrink: 0;
                margin: 0;
            }
            .chatbot-img-btn:hover {
                background: #e9ecef;
                color: #bc9c22;
                transform: scale(1.05);
            }
            .chatbot-input-wrapper {
                flex: 1;
                position: relative;
                display: flex;
                align-items: center;
                background: #f8f9fa;
                border-radius: 25px;
                border: 1px solid #e9ecef;
                padding: 4px 4px 4px 15px;
                transition: all 0.3s;
            }
            .chatbot-input-wrapper:focus-within {
                border-color: #bc9c22;
                box-shadow: 0 0 0 3px rgba(188, 156, 34, 0.1);
                background: #fff;
            }
            #chatbot-input {
                flex: 1;
                border: none;
                background: transparent;
                padding: 8px 0;
                outline: none;
                font-size: 14px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            #chatbot-send {
                background: #bc9c22;
                border: none;
                color: #fff;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
            }
            #chatbot-send:hover {
                background: #a68a1d;
                transform: scale(1.05) rotate(-10deg);
            }
            #chatbot-send i {
                font-size: 14px;
                margin-left: -2px;
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

                    <div class="position-sticky sidebar-sticky d-flex flex-column align-items-center" style="height: 100vh;">
                        <div class="d-flex flex-column align-items-center flex-shrink-0 pt-4">
                            <a class="navbar-brand" href="{{ route('home') }}">
                                <img src="{{ asset('images/templatemo-barber-logo.png') }}" class="logo-image img-fluid" alt="Barber Shop Logo">
                            </a>

                            <ul class="nav flex-column w-100 mt-4">
                                <li class="nav-item">
                                    <a class="nav-link click-scroll" href="#section_1" style="color: #000;">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link click-scroll" href="#section_2" style="color: #000;">Our Story</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link click-scroll" href="#section_3" style="color: #000;">Services</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link click-scroll" href="#section_4" style="color: #000;">Price List</a>
                                </li>
                                
                                @auth
                                    @if(Auth::user()->role === 'customer')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('customer.appointments.index') }}" style="color: #000;">My Schedules</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('customer.loyalty.index') }}" style="color: #000;">My Loyalty</a>
                                    </li>
                                    @endif
                                @else
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
                                @endauth
                            </ul>
                        </div>

                        @auth
                        <div class="mt-auto w-100" style="border-top: 1px solid rgba(0,0,0,0.1);">
                            <li class="nav-item logout-item" style="list-style: none;">
                                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                                    @csrf
                                    <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                                        style="font-size: 16px; color: #000; padding: 12px 20px;"
                                        onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
                                        Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </div>
                        @endauth
                    </div>
                </nav>
                
                <div class="col-md-8 ms-sm-auto col-lg-9 p-0">
                    @yield('content')
                </div>

            </div>
        </div>

        <!-- Chatbot UI -->
        @auth
        <div id="chatbot-container">
            <button id="chatbot-button" title="Chat với chúng tôi">
                <i class="bi bi-chat-dots-fill"></i>
            </button>
            <div id="chatbot-window">
                <div id="chatbot-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-robot fs-4" style="color: #bc9c22;"></i>
                        <span>Trợ lý Barber AI</span>
                    </div>
                    <i class="bi bi-x" id="chatbot-close"></i>
                </div>
                <div id="chatbot-messages">
                    <div class="chat-msg bot">Xin chào <strong>{{ Auth::user()->name }}</strong>! Tôi là trợ lý AI của Barber Shop.<br><br>Tôi có thể giúp bạn đặt lịch hoặc tư vấn kiểu tóc (bạn có thể gửi ảnh cho tôi).</div>
                </div>
                <form id="chatbot-input-area">
                    <label for="chatbot-image" class="chatbot-img-btn" title="Gửi ảnh">
                        <i class="bi bi-image"></i>
                    </label>
                    <input type="file" id="chatbot-image" accept="image/*" style="display: none;">
                    
                    <div class="chatbot-input-wrapper">
                        <input type="text" id="chatbot-input" placeholder="Nhập tin nhắn..." autocomplete="off">
                        <button type="submit" id="chatbot-send" title="Gửi">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
                <div id="chatbot-preview" style="display: none; padding: 5px 10px; background: #f1f1f1; font-size: 12px; border-top: 1px solid #ddd;">
                    <span id="chatbot-preview-name"></span>
                    <i class="bi bi-x-circle text-danger ms-2" id="chatbot-preview-remove" style="cursor: pointer;"></i>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatBtn = document.getElementById('chatbot-button');
                const chatWin = document.getElementById('chatbot-window');
                const closeBtn = document.getElementById('chatbot-close');
                const chatInput = document.getElementById('chatbot-input');
                const chatImage = document.getElementById('chatbot-image');
                const sendBtn = document.getElementById('chatbot-send');
                const msgsArea = document.getElementById('chatbot-messages');
                const form = document.getElementById('chatbot-input-area');
                const previewDiv = document.getElementById('chatbot-preview');
                const previewName = document.getElementById('chatbot-preview-name');
                const previewRemove = document.getElementById('chatbot-preview-remove');

                let chatHistory = [];

                // Auto pop-up after 2 seconds
                setTimeout(() => {
                    if (chatWin.style.display !== 'flex') {
                        chatWin.style.display = 'flex';
                    }
                }, 2000);

                chatBtn.addEventListener('click', () => {
                    chatWin.style.display = chatWin.style.display === 'flex' ? 'none' : 'flex';
                });

                closeBtn.addEventListener('click', () => {
                    chatWin.style.display = 'none';
                });

                chatImage.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        previewName.textContent = 'Đã đính kèm: ' + this.files[0].name;
                        previewDiv.style.display = 'block';
                    }
                });

                previewRemove.addEventListener('click', function() {
                    chatImage.value = '';
                    previewDiv.style.display = 'none';
                });

                const appendMessage = (text, sender, isImage = false) => {
                    const msgDiv = document.createElement('div');
                    msgDiv.className = `chat-msg ${sender}`;
                    
                    if (isImage) {
                        msgDiv.innerHTML = text;
                    } else {
                        const formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                        msgDiv.innerHTML = formattedText;
                    }
                    
                    msgsArea.appendChild(msgDiv);
                    msgsArea.scrollTop = msgsArea.scrollHeight;
                };

                const sendMessage = async (e) => {
                    e.preventDefault();
                    
                    const text = chatInput.value.trim();
                    const file = chatImage.files[0];
                    
                    if (!text && !file) return;

                    // Display user message
                    if (text) appendMessage(text, 'user');
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            appendMessage(`<img src="${e.target.result}" style="max-width: 100%; border-radius: 8px;">`, 'user', true);
                        }
                        reader.readAsDataURL(file);
                    }

                    // Prepare FormData
                    const formData = new FormData();
                    if (text) formData.append('message', text);
                    if (file) formData.append('image', file);
                    if (chatHistory.length > 0) formData.append('history', JSON.stringify(chatHistory));

                    // Reset inputs
                    chatInput.value = '';
                    chatImage.value = '';
                    previewDiv.style.display = 'none';
                    chatInput.disabled = true;
                    sendBtn.disabled = true;

                    // Loading indicator
                    const loadingId = 'loading-' + Date.now();
                    const loadingDiv = document.createElement('div');
                    loadingDiv.className = 'chat-msg bot';
                    loadingDiv.id = loadingId;
                    loadingDiv.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
                    msgsArea.appendChild(loadingDiv);
                    msgsArea.scrollTop = msgsArea.scrollHeight;

                    try {
                        const response = await fetch('/api/chatbot/ask', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();
                        document.getElementById(loadingId).remove();

                        if (response.ok && data.success) {
                            appendMessage(data.reply, 'bot');
                            
                            if (data.history) {
                                chatHistory = data.history;
                            } else {
                                chatHistory.push({ role: 'user', text: text });
                                chatHistory.push({ role: 'model', text: data.reply });
                            }
                        } else {
                            if (response.status === 401) {
                                appendMessage('Vui lòng đăng nhập để sử dụng tính năng Chat.', 'bot');
                            } else {
                                appendMessage(data.message || 'Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau.', 'bot');
                            }
                        }
                    } catch (err) {
                        document.getElementById(loadingId).remove();
                        appendMessage('Lỗi mạng. Không thể gửi tin nhắn.', 'bot');
                    }

                    chatInput.disabled = false;
                    sendBtn.disabled = false;
                    chatInput.focus();
                };

                form.addEventListener('submit', sendMessage);
            });
        </script>
        @endauth
        @stack('scripts')
    </body>
</html>