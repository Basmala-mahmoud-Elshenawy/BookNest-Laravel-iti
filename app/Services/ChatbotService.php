<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Rule-based Library Assistant orchestrator.
 *
 * Authentication and authorization are handled by the HTTP middleware and
 * the authenticated User model. This service only queries data that the
 * resolved role is allowed to see, then passes plain facts to AIService for
 * deterministic response formatting. There is no external AI/API call.
 */
class ChatbotService
{
    private const ADMIN_ONLY_SIGNALS = [
        'all users', 'registered users', 'how many users', 'user list',
        'admin statistics', 'admin-only', 'admin only', 'every user',
        'list users', 'user accounts', 'total users', 'إحصائيات الادمن',
        'عدد المستخدمين', 'المستخدمين المسجلين', 'قائمة المستخدمين',
    ];

    private const STOP_WORDS = [
        'recommend', 'recommendation', 'recommendations', 'find', 'show', 'search',
        'tell', 'give', 'me', 'books', 'book', 'about', 'please', 'the', 'a', 'an',
        'good', 'best', 'available', 'availability', 'is', 'are', 'there', 'any',
        'which', 'what', 'who', 'can', 'you', 'compare', 'comparison', 'between', 'and',
        'for', 'in', 'on', 'of', 'with', 'from', 'by', 'do', 'i', 'have', 'how',
        'many', 'category', 'categories', 'author', 'authors', 'wrote', 'written',
        'writer', 'it', 'us', 'so', 'my', 'your', 'know', 'think', 'opinion',
        'كتاب', 'كتب', 'عن', 'في', 'من', 'هل', 'يوجد', 'متاح', 'متاحة', 'ابحث',
        'رشح', 'رشحلي', 'اقتراح', 'اقتراحات', 'مقارنة', 'بين', 'و', 'لي', 'ليّ',
        'عايز', 'عايزة', 'عاوز', 'عاوزة', 'مين', 'كاتب', 'كاتبة', 'مؤلف', 'مؤلفة',
        'الكاتب', 'الكاتبة', 'المؤلف', 'المؤلفة', 'المتاح', 'المتاحة',
        'رشح', 'رشحلي', 'ماتش', 'match', 'matches', 'matching', 'category', 'categories',
        'تصنيف', 'تصنيفات', 'التصنيف', 'التصنيفات', 'فئة', 'فئات', 'الفئة', 'الفئات',
        'قسم', 'أقسام', 'القسم', 'الأقسام',
        'هو', 'هي', 'ده', 'دي', 'دا', 'اللي', 'الي', 'ايه', 'إيه', 'رايك', 'رأيك',
        'اعرف', 'أعرف', 'عارف', 'عارفة', 'تقدر', 'ينفع', 'ممكن', 'عندكم', 'عندك',
    ];

    public function __construct(
        private readonly AIService $ai,
        private readonly RecommendationService $recommendations,
    ) {
    }

    /**
     * @return array{response:string,rejected:bool,reason:?string}
     */
    public function handle(User $user, string $message): array
    {
        $normalized = $this->normalize($message);
        $isAdmin = $user->isAdmin();

        if (! $isAdmin && $this->looksLikeAdminRequest($normalized)) {
            return [
                'response' => "I can't share administrative or other users' account information. I can help with books, recommendations, availability, authors, categories, and how to use the library.",
                'rejected' => true,
                'reason' => 'user_requested_admin_scope',
            ];
        }

        $intent = $this->detectIntent($normalized, $isAdmin);
        $context = $this->buildContext($user, $normalized, $intent, $isAdmin);
        $response = $this->ai->generate($message, $intent, $context, $isAdmin);

        return ['response' => $response, 'rejected' => false, 'reason' => null];
    }

    private function detectIntent(string $message, bool $isAdmin): string
    {
        if ($this->containsAny($message, ['hello', 'hi', 'hey', 'مرحبا', 'اهلا', 'أهلا', 'السلام عليكم'])) {
            return 'greeting';
        }

        if ($isAdmin && $this->containsAny($message, [
            'statistics', 'stats', 'how many users', 'how many books', 'registered users',
            'most books', 'largest category', 'biggest category', 'which category has',
            'إحصائيات', 'عدد المستخدمين', 'عدد الكتب', 'اكتر تصنيف', 'أكبر تصنيف', 'اكبر تصنيف',
        ])) {
            return 'admin_stats';
        }

        if ($this->containsAny($message, ['how do i borrow', 'how to borrow', 'borrow a book', 'borrow book', 'ازاي استعير', 'كيف استعار', 'استعير'])) {
            return 'how_to_borrow';
        }

        if ($this->containsAny($message, ['how do i return', 'how to return', 'return a book', 'return book', 'ازاي ارجع', 'إرجاع الكتاب', 'ارجع الكتاب'])) {
            return 'how_to_return';
        }

        if ($this->containsAny($message, ['favorite', 'favourites', 'favorites', 'heart', 'المفضلة', 'المفضلات'])) {
            return 'how_to_favorite';
        }

        if ($this->containsAny($message, ['profile', 'my profile', 'البروفايل', 'الملف الشخصي'])) {
            return 'profile';
        }

        if ($this->containsAny($message, ['help', 'what can you do', 'how does this work', 'مساعدة', 'تقدر تعمل ايه', 'ازاي استخدم'])) {
            return 'help';
        }

        if ($this->containsAny($message, ['compare', 'comparison', 'compare books', 'قارن', 'مقارنة', 'الفرق بين'])) {
            return 'comparison';
        }

        if ($this->containsAny($message, [
            'recommend', 'recommended', 'recommendation', 'recommendations',
            'suggest', 'suggestion', 'suggestions', 'match', 'matches', 'matching',
            'رشح', 'رشحلي', 'اقتراح', 'اقتراحات', 'مناسب لاهتماماتي', 'مناسبة لاهتماماتي',
        ])) {
            return 'recommendation';
        }

        if ($this->containsAny($message, [
            'author', 'authors', 'written by', 'books by', 'writer of', 'who wrote',
            'مؤلف', 'مؤلفة', 'المؤلف', 'المؤلفة', 'كاتب', 'كاتبة', 'الكاتب', 'الكاتبة',
            'كتب الكاتب', 'مين كتب', 'من كتب', 'مين اللي كتب',
        ])) {
            return 'author_search';
        }

        if ($this->containsAny($message, [
            'category', 'categories', 'genre', 'genres', 'تصنيف', 'تصنيفات',
            'التصنيف', 'التصنيفات', 'فئة', 'فئات', 'الفئة', 'الفئات',
            'قسم', 'أقسام', 'القسم', 'الأقسام',
        ])) {
            return 'category_search';
        }

        // Checked after author/category so a generic "available" inside a
        // more specific question (e.g. "what categories are available")
        // doesn't steal the intent before the more specific trigger gets a
        // chance to match.
        if ($this->containsAny($message, ['available', 'availability', 'in stock', 'متاح', 'متاحة', 'المتاح', 'المتاحة', 'نسخة', 'نسخ'])) {
            return 'availability';
        }

        return 'book_search';
    }

    private function buildContext(User $user, string $message, string $intent, bool $isAdmin): array
    {
        if ($intent === 'admin_stats' && $isAdmin) {
            return $this->buildAdminContext($message, $user);
        }

        if ($intent === 'recommendation') {
            return $this->buildRecommendationContext($user);
        }

        $searchTerm = $this->extractSearchTerm($message);

        if ($intent === 'category_search' && $searchTerm === '') {
            // "What categories do you have" has no specific category name to
            // search for -- it's asking for the list itself, not a filtered
            // search, so it needs its own branch rather than falling through
            // to an empty, always-failing book search.
            return [
                'search_term' => '',
                'books' => [],
                'all_categories' => Category::orderBy('name')->pluck('name')->all(),
            ];
        }

        // Availability questions must still surface a matching book even if
        // it currently has 0 copies -- that's exactly the "unavailable"
        // answer the AIService formatter is built to give. Pre-filtering it
        // out here made a real book look like it doesn't exist at all.
        $books = $this->searchBooks($searchTerm);

        return [
            'search_term' => $searchTerm,
            'books' => $this->serializeBooks($books, $user),
        ];
    }

    private function buildRecommendationContext(User $user): array
    {
        if (! $user->profile) {
            return [
                'profile_message' => 'Complete your profile first. Your interests, topics, skills, goals, and preferred categories are used by the built-in recommendation engine.',
                'books' => [],
            ];
        }

        $books = Book::query()->with(['category', 'authors'])->get();
        $ranked = $this->recommendations->rank($user, $books)
            ->filter(fn (Book $book) => $this->recommendations->isRecommended((int) $book->match_percentage))
            ->take(8);

        if ($ranked->isEmpty()) {
            return [
                'profile_message' => "I couldn't find a strong match yet. Try adding a few more interests, topics, skills, or preferred categories to your profile.",
                'books' => [],
            ];
        }

        return [
            'books' => $this->serializeBooks($ranked, $user),
        ];
    }

    private function buildAdminContext(string $message, User $user): array
    {
        $perCategory = Category::withCount('books')->orderByDesc('books_count')->get();
        $topCategory = $perCategory->first();
        $lowAvailability = Book::where('available_copies', '<=', 1)
            ->orderBy('available_copies')
            ->limit(10)
            ->get(['title', 'available_copies', 'total_copies']);

        $searchTerm = $this->extractSearchTerm($message);
        $searchBooks = $searchTerm !== '' ? $this->searchBooks($searchTerm) : collect();

        return [
            'total_book_copies' => Book::sum('total_copies'),
            'available_copies' => Book::sum('available_copies'),
            'total_users' => User::count(),
            'top_category' => $topCategory ? "{$topCategory->name} ({$topCategory->books_count} books)" : '',
            'question' => $message,
            'low_availability' => $lowAvailability->map(fn (Book $book) => [
                'title' => $book->title,
                'available_copies' => $book->available_copies,
                'total_copies' => $book->total_copies,
            ])->all(),
            'books' => $this->serializeBooks($searchBooks, $user),
        ];
    }

    private function searchBooks(string $term, bool $availableOnly = false)
    {
        if ($term === '') {
            return collect();
        }

        $query = Book::query()->with(['category', 'authors']);

        if ($availableOnly) {
            $query->where('available_copies', '>', 0);
        }

        $query->search($term);
        $books = $query->limit(12)->get();

        if ($books->isNotEmpty()) {
            return $books;
        }

        // A natural-language question often leaves several useful keywords.
        // Try the meaningful words individually without changing Book::search.
        $tokens = $this->meaningfulTokens($term);
        if ($tokens === []) {
            return collect();
        }

        $query = Book::query()->with(['category', 'authors']);
        if ($availableOnly) {
            $query->where('available_copies', '>', 0);
        }
        $query->where(function ($q) use ($tokens) {
            foreach ($tokens as $token) {
                $like = '%'.$token.'%';
                $q->orWhere('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('isbn', 'like', $like)
                    ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', $like))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
            }
        });

        return $query->limit(12)->get();
    }

    private function serializeBooks($books, User $user): array
    {
        return $books->map(function (Book $book) use ($user): array {
            $authors = $book->authors->pluck('name')->implode(', ');
            $reason = '';
            if (isset($book->match_percentage)) {
                $reason = $this->recommendations->explainMatch($user, $book);
            }

            return [
                'title' => $book->title,
                'authors' => $authors,
                'category' => $book->category?->name ?? '',
                'available_copies' => (int) $book->available_copies,
                'total_copies' => (int) $book->total_copies,
                'match_percentage' => $book->match_percentage ?? null,
                'match_label' => isset($book->match_percentage)
                    ? $this->recommendations->matchLabel((int) $book->match_percentage)
                    : null,
                'match_reason' => $reason,
            ];
        })->all();
    }

    private function extractSearchTerm(string $message): string
    {
        $term = preg_replace('/[^\p{L}\p{N}\s+#.-]/u', ' ', $message) ?? $message;
        $tokens = preg_split('/\s+/u', Str::lower(trim($term)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_filter($tokens, function (string $token): bool {
            return ! in_array($token, self::STOP_WORDS, true) && preg_match('/[^\x00-\x7F]/', $token) ? preg_match_all('/./us', $token) > 1 : strlen($token) > 1;
        }));

        return implode(' ', $tokens);
    }

    private function meaningfulTokens(string $term): array
    {
        return array_values(array_filter(
            preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [],
            fn (string $token): bool => preg_match('/[^\x00-\x7F]/', $token) ? preg_match_all('/./us', $token) > 1 : strlen($token) > 1
        ));
    }

    private function looksLikeAdminRequest(string $message): bool
    {
        return $this->containsAny($message, self::ADMIN_ONLY_SIGNALS);
    }

    /**
     * Whole-word/phrase matching (Unicode-aware) rather than raw substring
     * matching. Plain str_contains() was previously used here, which caused
     * short trigger words to false-positive inside unrelated words -- e.g.
     * the greeting trigger "hi" matched inside "which", so a message like
     * "which books match my interests" was misclassified as a greeting
     * before it ever reached the recommendation intent check. This silently
     * broke many legitimate questions that happened to contain a trigger
     * word as a substring.
     */
    private function containsAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = Str::lower(trim($needle));
            if ($needle === '') {
                continue;
            }

            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($needle, '/').'(?![\p{L}\p{N}])/u';
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $message): string
    {
        return Str::lower(trim(preg_replace('/\s+/u', ' ', $message) ?? $message));
    }
}
