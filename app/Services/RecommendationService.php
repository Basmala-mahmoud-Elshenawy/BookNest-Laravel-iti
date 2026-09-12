<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Deterministic profile -> book matching engine.
 *
 * The score is always calculated from the current user profile and the
 * current book metadata. It is never random and it does not require an
 * external AI provider. Updating/saving the profile immediately changes the
 * score the next time a book card, recommendation page, dashboard, or book
 * details page is loaded.
 */
class RecommendationService
{
    /** Preferred category is the strongest explicit preference. */
    private const CATEGORY_WEIGHT = 0.50;

    /** Text/topic similarity supplies the rest of the score. */
    private const TOPIC_WEIGHT = 0.50;

    public function matchPercentage(User $user, Book $book): int
    {
        $profile = $user->profile;

        if (! $profile) {
            return 0;
        }

        $userTokens = collect($profile->tokenBag());
        $bookTokens = collect($book->tokenBag());
        $preferredCategoryIds = collect($profile->preferred_category_ids ?? [])
            ->map(fn ($id) => (int) $id);

        $categoryMatch = $book->category_id
            && $preferredCategoryIds->contains((int) $book->category_id);

        // A selected category is a real preference even when the user has not
        // filled the free-text fields. Do not return 0% for those books.
        $topicSimilarity = 0.0;
        if ($userTokens->isNotEmpty() && $bookTokens->isNotEmpty()) {
            $overlap = $userTokens->intersect($bookTokens)->count();
            $smallerSet = min($userTokens->count(), $bookTokens->count());
            $topicSimilarity = $smallerSet > 0 ? $overlap / $smallerSet : 0.0;
        }

        $score = ($categoryMatch ? self::CATEGORY_WEIGHT : 0)
            + ($topicSimilarity * self::TOPIC_WEIGHT);

        // Make a preferred category clearly visible as a recommendation, but
        // keep room for books that match the user's topics even outside it.
        if ($categoryMatch && $topicSimilarity === 0.0) {
            $score = 0.60;
        }

        return (int) round(max(0, min(100, $score * 100)));
    }

    public function matchLabel(int $percentage): string
    {
        return match (true) {
            $percentage >= 70 => 'Strong match',
            $percentage >= 45 => 'Good match',
            $percentage >= 25 => 'Possible match',
            default => 'Low match',
        };
    }

    public function isRecommended(int $percentage): bool
    {
        return $percentage >= 45;
    }

    public function rank(User $user, Collection $books): Collection
    {
        return $books
            ->map(function (Book $book) use ($user) {
                $book->setAttribute('match_percentage', $this->matchPercentage($user, $book));
                $book->setAttribute('match_label', $this->matchLabel((int) $book->match_percentage));

                return $book;
            })
            ->sortByDesc('match_percentage')
            ->values();
    }

    public function explainMatch(User $user, Book $book): string
    {
        $profile = $user->profile;
        if (! $profile) {
            return 'Complete your profile to get a personalized match.';
        }

        $reasons = [];
        $preferredCategoryIds = collect($profile->preferred_category_ids ?? [])
            ->map(fn ($id) => (int) $id);

        if ($book->category_id && $preferredCategoryIds->contains((int) $book->category_id)) {
            $reasons[] = 'it is in one of your preferred categories';
        }

        $shared = collect($profile->tokenBag())
            ->intersect(collect($book->tokenBag()))
            ->take(5);

        if ($shared->isNotEmpty()) {
            $reasons[] = 'it matches your interests/topics: '.$shared->implode(', ');
        }

        if ($reasons === []) {
            return 'This book has a low direct overlap with your current profile. You can improve the score by adding more interests, topics, skills, goals, or preferred categories.';
        }

        return 'Recommended because '.$this->joinReasons($reasons).'.';
    }

    private function joinReasons(array $reasons): string
    {
        if (count($reasons) === 1) {
            return $reasons[0];
        }

        return implode(' and ', [$reasons[0], $reasons[1]]);
    }
}
