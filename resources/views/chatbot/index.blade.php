@extends('layouts.app')
@section('title', 'BookNest AI Assistant')
@section('content')
<section class="section">
    <div class="container chat-wrap">
        <h1>BookNest AI Assistant</h1>
        <p class="book-author">
            @if(auth()->user()->isAdmin())
                Ask the BookNest AI directly about library statistics, category breakdowns, or book search.
            @else
                Ask the BookNest AI directly for recommendations, book explanations, comparisons, or catalog search.
            @endif
        </p>

        <div class="card" style="margin-top:20px;">
            <div class="chat-window" id="chat-window">
                <div class="chat-bubble bot">Hi {{ auth()->user()->name }}! How can I help you find your next book today?</div>
            </div>
            <form id="chat-form" class="chat-input-row">
                <input type="text" id="chat-input" class="form-control" placeholder="e.g. Recommend books about PHP" autocomplete="off" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>

        <div class="card card-pad" style="margin-top:20px;">
            <strong>Try asking:</strong>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                @if(auth()->user()->isAdmin())
                    <span class="badge badge-reserved">How many books are available?</span>
                    <span class="badge badge-reserved">Which category has the most books?</span>
                    <span class="badge badge-reserved">How many users are registered?</span>
                @else
                    <span class="badge badge-reserved">Recommend books about PHP</span>
                    <span class="badge badge-reserved">Which books match my interests?</span>
                    <span class="badge badge-reserved">Find books about AI</span>
                @endif
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const windowEl = document.getElementById('chat-window');
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function addBubble(text, cls) {
    const div = document.createElement('div');
    div.className = 'chat-bubble ' + cls;
    div.textContent = text;
    windowEl.appendChild(div);
    windowEl.scrollTop = windowEl.scrollHeight;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = input.value.trim();
    if (!message) return;
    addBubble(message, 'user');
    input.value = '';
    addBubble('Thinking...', 'bot');

    try {
        const res = await fetch('{{ route('chatbot.send') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ message }),
        });
        windowEl.lastChild.remove();
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            addBubble(err.message || 'Something went wrong. Please try again.', 'error');
            return;
        }
        const data = await res.json();
        addBubble(data.response, 'bot');
    } catch (err) {
        windowEl.lastChild.remove();
        addBubble('Network error — please try again.', 'error');
    }
});
</script>
@endpush
@endsection
