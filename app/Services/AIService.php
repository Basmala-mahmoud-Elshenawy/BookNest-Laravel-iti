<?php

namespace App\Services;

/**
 * Deterministic, database-grounded BookNest assistant.
 * No external AI/API is required.
 */
class AIService
{
    public function generate(string $message, string $intent, array $context, bool $isAdmin = false): string
    {
        $ar = $this->isArabic($message);

        return match ($intent) {
            'greeting' => $ar ? 'أهلاً! أنا مساعد BookNest. أقدر أساعدك في البحث عن الكتب، معرفة التوافر، المؤلفين والتصنيفات، الترشيحات، والمقارنة بين الكتب.' : 'Hi! I’m the BookNest Library Assistant. I can help you find books, check availability, explore authors and categories, get recommendations, or compare books.',
            'help' => $this->help($isAdmin, $ar),
            'how_to_borrow' => $ar ? 'لاستعارة كتاب، افتحي صفحة تفاصيله واضغطي Borrow Book. لازم تكوني مسجلة دخول ويكون فيه نسخة متاحة، والنظام يسجل الاستعارة وموعد الإرجاع تلقائياً.' : 'To borrow a book, open its details and choose Borrow Book. You must be logged in and at least one copy must be available.',
            'how_to_return' => $ar ? 'لإرجاع كتاب، افتحي Borrowings واختاري Return Book بجانب الاستعارة الحالية. بعد الإرجاع ترجع النسخة إلى عدد النسخ المتاحة.' : 'To return a book, open Borrowings and choose Return Book for the active borrowing. The returned copy is added back to available copies.',
            'how_to_favorite' => $ar ? 'لحفظ كتاب، اضغطي علامة القلب في كارت الكتاب أو صفحة التفاصيل. الكتب المحفوظة ستظهر في Favorites.' : 'Use the heart button on a book card or its details page. Saved books appear in Favorites.',
            'profile' => $ar ? 'افتحي Profile وعدّلي اهتماماتك والموضوعات والمهارات والأهداف والتصنيفات المفضلة. هذه البيانات هي أساس الـ Recommendation.' : 'Open Profile and update your interests, topics, skills, goals, and preferred categories. These fields power your personalized recommendations.',
            'availability' => $this->availability($context, $ar),
            'book_search' => $this->bookSearch($context, $ar),
            'author_search' => $this->authorSearch($context, $ar),
            'category_search' => $this->categorySearch($context, $ar),
            'recommendation' => $this->recommendation($context, $ar),
            'comparison' => $this->comparison($context, $ar),
            'admin_stats' => $this->adminStats($context, $ar),
            default => $this->bookSearch($context, $ar),
        };
    }

    private function help(bool $admin, bool $ar): string
    {
        if ($ar) {
            $r = "أقدر أساعدك في:\n• البحث عن الكتب بالعنوان أو الموضوع أو ISBN أو الكاتب أو التصنيف\n• معرفة عدد النسخ المتاحة وحالة الكتاب\n• ترشيح الكتب بناءً على بروفايلك\n• توضيح نسبة الـ Match وسبب الترشيح\n• مقارنة الكتب وشرح الاستعارة والإرجاع والمفضلة والبروفايل";
            if ($admin) $r .= "\n• إحصائيات المكتبة والكتب قليلة التوافر";
            return $r;
        }

        $r = "I can help with:\n• Finding books by title, topic, ISBN, author, or category\n• Checking copies and availability\n• Personalized recommendations based on your profile\n• Explaining the match score and why a book was recommended\n• Comparing books and explaining borrowing, returning, favorites, and profile settings";
        if ($admin) $r .= "\n• Library statistics and low-availability books";
        return $r;
    }

    private function availability(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (!$books) return $ar ? 'مش لاقية كتاب مطابق. جربي اسم الكتاب أو الكاتب أو التصنيف أو اكتبي جزء أقصر من الاسم.' : 'I couldn’t find a matching book. Try the title, author, category, or a shorter phrase.';

        return collect($books)->map(function (array $book) use ($ar) {
            $status = $book['available_copies'] > 0
                ? ($ar ? "متاح الآن — {$book['available_copies']} نسخة من {$book['total_copies']}" : "available — {$book['available_copies']} of {$book['total_copies']} copies")
                : ($ar ? "غير متاح حالياً — 0 من {$book['total_copies']} نسخة متاحة" : "currently unavailable — 0 of {$book['total_copies']} copies available");
            return "• {$book['title']} — {$status}";
        })->implode("\n");
    }

    private function bookSearch(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (!$books) return $ar ? 'مش لاقية الكتاب في كتالوج المكتبة. جربي العنوان أو الموضوع أو الكاتب أو ISBN أو التصنيف.' : 'I couldn’t find a matching book in the library catalog. Try another title, topic, author, ISBN, or category.';

        $header = $ar ? "لقيت الكتب دي:\n" : "Here are the matching books:\n";
        return $header.collect($books)->map(function (array $b) use ($ar) {
            $author = $b['authors'] !== '' ? ($ar ? " — الكاتب: {$b['authors']}" : " by {$b['authors']}") : '';
            $cat = $b['category'] !== '' ? ($ar ? " — {$b['category']}" : " [{$b['category']}]") : '';
            $copies = $ar ? " — {$b['available_copies']}/{$b['total_copies']} نسخة متاحة" : " — {$b['available_copies']}/{$b['total_copies']} available";
            return "• {$b['title']}{$author}{$cat}{$copies}";
        })->implode("\n");
    }

    private function authorSearch(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (!$books) return $ar ? 'مش لاقية كتاب مطابق لاسم الكاتب ده في الكتالوج.' : 'I couldn’t find a book matching that author in the catalog.';
        $term = $context['search_term'] ?? '';
        return ($ar ? "الكتب المطابقة لـ {$term}:\n" : "Books matching {$term}:\n") .
            collect($books)->map(fn(array $b) => '• '.$b['title'].($b['authors'] ? " — {$b['authors']}" : ''))->implode("\n");
    }

    private function categorySearch(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (!$books && empty($context['search_term']) && !empty($context['all_categories'])) {
            return ($ar ? "التصنيفات المتاحة:\n" : "Available categories:\n") .
                collect($context['all_categories'])->map(fn(string $x) => "• {$x}")->implode("\n");
        }
        if (!$books) return $ar ? 'مش لاقية كتب في التصنيف ده.' : 'I couldn’t find books in that category.';
        return ($ar ? "الكتب المطابقة للتصنيف:\n" : "Books matching this category:\n") .
            collect($books)->map(fn(array $b) => "• {$b['title']}")->implode("\n");
    }

    private function recommendation(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (!$books) return $ar
            ? ($context['profile_message'] ?? 'كمّلي بيانات البروفايل الأول علشان أقدر أحسب الترشيحات.')
            : ($context['profile_message'] ?? 'Complete your profile so I can calculate personalized recommendations.');

        $header = $ar ? "بناءً على بروفايلك، دي أفضل الترشيحات:\n" : "Based on your profile, these are the strongest matches:\n";
        return $header.collect($books)->map(function (array $b) use ($ar) {
            $pct = (int)($b['match_percentage'] ?? 0);
            $label = $pct >= 70 ? ($ar ? 'ترشيح قوي' : 'Strong recommendation') :
                ($pct >= 45 ? ($ar ? 'مُوصى به' : 'Recommended') : ($ar ? 'ممكن يعجبك' : 'Possible match'));
            $reason = $b['match_reason'] ?? '';
            $match = $ar ? "{$pct}% تطابق — {$label}" : "{$pct}% match — {$label}";
            return "• {$b['title']} — {$match}".($reason ? "\n  ".($ar ? "السبب: {$reason}" : $reason) : '');
        })->implode("\n");
    }

    private function comparison(array $context, bool $ar): string
    {
        $books = $context['books'] ?? [];
        if (count($books) < 2) return $ar ? 'محتاج كتابين على الأقل علشان أعمل مقارنة. اكتبي اسمي الكتابين.' : 'I need at least two matching books to compare them. Give me two book titles.';
        $lines = [$ar ? 'دي مقارنة مبنية على بيانات المكتبة:' : 'Here’s a database-based comparison:'];
        foreach (array_slice($books, 0, 2) as $b) {
            $status = $b['available_copies'] > 0 ? ($ar ? 'متاح' : 'available') : ($ar ? 'غير متاح' : 'unavailable');
            $lines[] = "• {$b['title']} — ".($ar ? "الكاتب: {$b['authors']}; التصنيف: {$b['category']}; الحالة: {$status}" : "Author: {$b['authors']}; Category: {$b['category']}; Availability: {$status}");
        }
        return implode("\n", $lines);
    }

    private function adminStats(array $context, bool $ar): string
    {
        if (!isset($context['total_book_copies'])) {
            return $ar ? 'المعلومة دي متاحة للأدمن فقط.' : 'That information is available to administrators only.';
        }

        $question = $context['question'] ?? '';
        $asksCategory = preg_match('/(which category|what category|largest category|biggest category|most books|category has|اكتر تصنيف|أكتر تصنيف|أكثر تصنيف|أكبر تصنيف|اكبر تصنيف|تصنيف فيه|قسم فيه)/u', $question) === 1;
        $asksUsers = preg_match('/(how many users|registered users|total users|عدد المستخدمين|المستخدمين المسجلين|كام مستخدم|عدد اليوزر)/u', $question) === 1;
        $asksAvailable = preg_match('/(available|availability|in stock|how many books are available|متاح|متاحة|متوفر|متوفرة|كام كتاب متاح|عدد الكتب المتاحة)/u', $question) === 1;

        if ($asksCategory && !empty($context['top_category'])) {
            return $ar
                ? 'أكبر تصنيف في المكتبة هو: '.$context['top_category'].'.'
                : 'The category with the most books is: '.$context['top_category'].'.';
        }

        if ($asksUsers) {
            return $ar
                ? 'عدد المستخدمين المسجلين حالياً: '.$context['total_users'].'.'
                : 'There are '.$context['total_users'].' registered users.';
        }

        if ($asksAvailable) {
            return $ar
                ? 'حالياً يوجد '.$context['available_copies'].' نسخة متاحة للاستعارة من إجمالي '.$context['total_book_copies'].' نسخة.'
                : 'There are currently '.$context['available_copies'].' available copies out of '.$context['total_book_copies'].' total book copies.';
        }

        return $ar
            ? "إحصائيات المكتبة:\n• إجمالي نسخ الكتب: {$context['total_book_copies']}\n• النسخ المتاحة حالياً: {$context['available_copies']}\n• المستخدمون المسجلون: {$context['total_users']}"
            : "Library statistics:\n• Total book copies: {$context['total_book_copies']}\n• Currently available copies: {$context['available_copies']}\n• Registered users: {$context['total_users']}";
    }

    private function isArabic(string $message): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $message) === 1;
    }
}
