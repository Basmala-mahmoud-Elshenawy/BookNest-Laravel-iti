<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BookNest — Find Your Next Great Book')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.navbar')

    <main>
        @if ($errors->any())
            <div class="container" style="margin-top:20px;"><div class="card card-pad form-error"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        @endif

        @if (session('status'))
            <div class="container" style="margin-top:20px;">
                <div class="card card-pad" style="border-left:4px solid var(--color-button);">{{ session('status') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    @auth
        <button type="button" class="floating-chat" id="floating-chat-toggle" aria-expanded="false" aria-controls="floating-chat-panel">
            <span class="floating-chat-icon" aria-hidden="true">✦</span>
            <span>Ask BookNest AI</span>
        </button>
        <section class="floating-chat-panel card" id="floating-chat-panel" hidden aria-label="BookNest AI Assistant">
            <div class="floating-chat-head">
                <div><strong>BookNest AI</strong><small>Ask about books, categories, availability or recommendations.</small></div>
                <button type="button" class="chat-close" id="floating-chat-close" aria-label="Close chat">×</button>
            </div>
            <div class="floating-chat-messages" id="floating-chat-messages">
                <div class="chat-bubble bot">Hi {{ auth()->user()->name }}! What would you like to know?</div>
            </div>
            <form id="floating-chat-form" class="chat-input-row">
                @csrf
                <input type="text" id="floating-chat-input" class="form-control" placeholder="Ask a question..." autocomplete="off" maxlength="1000" required>
                <button type="submit" class="btn btn-primary btn-sm">Send</button>
            </form>
        </section>
    @endauth

    @stack('scripts')

    @auth
    <script>
    (() => {
        const toggle = document.getElementById('floating-chat-toggle');
        const panel = document.getElementById('floating-chat-panel');
        const close = document.getElementById('floating-chat-close');
        const form = document.getElementById('floating-chat-form');
        const input = document.getElementById('floating-chat-input');
        const messages = document.getElementById('floating-chat-messages');
        if (!toggle || !panel || !form) return;

        const addMessage = (text, type) => {
            const bubble = document.createElement('div');
            bubble.className = `chat-bubble ${type}`;
            bubble.textContent = text;
            messages.appendChild(bubble);
            messages.scrollTop = messages.scrollHeight;
            return bubble;
        };

        const setOpen = (open) => {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
            if (open) input.focus();
        };
        toggle.addEventListener('click', () => setOpen(panel.hidden));
        close.addEventListener('click', () => setOpen(false));

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim();
            if (!message) return;
            addMessage(message, 'user');
            input.value = '';
            const thinking = addMessage('Thinking...', 'bot');
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            try {
                const response = await fetch('{{ route('chatbot.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message })
                });
                thinking.remove();
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    addMessage(data.message || 'I could not process that question. Please try again.', 'error');
                    return;
                }
                addMessage(data.response || 'I could not find an answer in the available library data.', 'bot');
            } catch (error) {
                thinking.remove();
                addMessage('The chat service is temporarily unavailable. The rest of BookNest is still working.', 'error');
            } finally {
                button.disabled = false;
                input.focus();
            }
        });
    })();
    </script>
    @endauth
</body>
</html>
