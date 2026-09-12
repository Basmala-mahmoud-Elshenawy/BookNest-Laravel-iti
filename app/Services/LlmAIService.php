<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmAIService
{
    private function toolDefinitions(): array
    {
        return [
            [
                'function_declarations' => [
                    [
                        'name' => 'search_books',
                        'description' => 'Search the book catalog by title, author, category, or keyword.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'query' => ['type' => 'STRING', 'description' => 'Search keywords (title, author name, category, etc).'],
                            ],
                            'required' => ['query'],
                        ],
                    ],
                    [
                        'name' => 'get_book_details',
                        'description' => 'Get full details (author, description, availability, category) for a specific book by its ID.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'book_id' => ['type' => 'INTEGER'],
                            ],
                            'required' => ['book_id'],
                        ],
                    ],
                    [
                        'name' => 'list_categories',
                        'description' => 'List all book categories with how many books are in each.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => new \stdClass(),
                        ],
                    ],
                    [
                        'name' => 'check_availability',
                        'description' => 'Check how many copies of a book are currently available to borrow.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'title_or_id' => ['type' => 'STRING', 'description' => 'Book title or numeric ID.'],
                            ],
                            'required' => ['title_or_id'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function executeTool(string $name, array $args): array
    {
        return match ($name) {
            'search_books' => $this->searchBooks($args['query'] ?? ''),
            'get_book_details' => $this->getBookDetails((int) ($args['book_id'] ?? 0)),
            'list_categories' => $this->listCategories(),
            'check_availability' => $this->checkAvailability($args['title_or_id'] ?? ''),
            default => ['error' => 'Unknown tool'],
        };
    }

        private function searchBooks(string $query): array
    {
        $books = Book::with('category')->search($query)->limit(8)->get();
        return [
            'books' => $books->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'category' => $b->category?->name,
                'available_copies' => $b->available_copies,
            ])->toArray(),
        ];
    }

    private function getBookDetails(int $bookId): array
    {
        $book = Book::with('category')->find($bookId);
        if (!$book) return ['error' => 'Book not found'];

        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'description' => $book->description,
            'category' => $book->category?->name,
            'available_copies' => $book->available_copies,
        ];
    }


    private function listCategories(): array
    {
        return [
            'categories' => Category::withCount('books')->orderBy('name')->get()
                ->map(fn ($c) => ['name' => $c->name, 'books_count' => $c->books_count])->toArray(),
        ];
    }
    private function checkAvailability(string $titleOrId): array
    {
        $book = is_numeric($titleOrId)
            ? Book::find((int) $titleOrId)
            : Book::where('title', 'like', "%{$titleOrId}%")->first();

        if (!$book) return ['error' => 'Book not found'];

        return ['title' => $book->title, 'available_copies' => $book->available_copies];
    }

    public function respond(User $user, string $message, ?int $bookId = null): string
    {

        $systemPrompt = "You are the BookNest Library Assistant. Answer ONLY using the provided tools "
            . "to look up real catalog data — never invent book details. Be concise and friendly. "
            . "Do not use Markdown formatting (no asterisks, no bullet stars) — write plain text only."
            . ($bookId ? " The user is currently viewing book_id={$bookId}." : '');
            
        $contents = [
            ['role' => 'user', 'parts' => [['text' => $message]]],
        ];

        $model = config('services.gemini.model');
        $apiKey = config('services.gemini.key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        for ($i = 0; $i < 4; $i++) {
            $response = Http::post($url, [
                'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                'contents' => $contents,
                'tools' => $this->toolDefinitions(),
            ]);

            if ($response->failed()) {
                Log::error('Gemini API error', ['body' => $response->body()]);
                return "Sorry, the assistant is temporarily unavailable.";
            }




            $candidate = $response->json('candidates.0.content');
            if (!$candidate) {
                return "Sorry, I couldn't process that.";
            }
            $parts = $candidate['parts'] ?? [];
            $functionCalls = array_filter($parts, fn ($p) => isset($p['functionCall']));

            if (empty($functionCalls)) {
                $text = collect($parts)->pluck('text')->filter()->implode("\n");
                return $text ?: "Sorry, I couldn't process that.";
            }

            $contents[] = ['role' => 'model', 'parts' => $parts];

            $responseParts = [];
            foreach ($functionCalls as $part) {
                $fnName = $part['functionCall']['name'];
                $fnArgs = $part['functionCall']['args'] ?? [];
                $result = $this->executeTool($fnName, $fnArgs);

                $responseParts[] = [
                    'functionResponse' => [
                        'name' => $fnName,
                        'response' => $result,
                    ],
                ];
            }

            $contents[] = ['role' => 'user', 'parts' => $responseParts];
        }

        return "Sorry, I couldn't process that request.";
    }
}