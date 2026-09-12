<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds real Book records from every cover asset supplied in
 * BookNest-Assets/books, using database/seeders_data/books_manifest.json
 * (built by the asset-preparation step -- see README "Asset pipeline").
 *
 * Titles come directly from the supplied filenames (per project decision:
 * import all 233 covers, filename-derived titles, minimal metadata).
 * Author, ISBN, and publication date are NOT fabricated -- the assets did
 * not include that information, so those fields are left null/empty rather
 * than invented, per the "no fabricated facts" requirement.
 */
class BookSeeder extends Seeder
{
    /** Keyword => category name, checked in order against the Programming-folder titles. */
    private const PROGRAMMING_KEYWORDS = [
        'ai' => 'Artificial Intelligence',
        'artificial intelligence' => 'Artificial Intelligence',
        'machine learning' => 'Artificial Intelligence',
        'deep learning' => 'Artificial Intelligence',
        'neural' => 'Artificial Intelligence',
        'database' => 'Database',
        'sql' => 'Database',
        'network' => 'Networking',
        'kali' => 'Cyber Security',
        'hacker' => 'Cyber Security',
        'linux' => 'Cyber Security',
        'html' => 'Web Development',
        'css' => 'Web Development',
        'javascript' => 'Web Development',
        'react' => 'Web Development',
        'sass' => 'Web Development',
        'responsive web' => 'Web Development',
        'git' => 'Web Development',
    ];

    public function run(): void
    {
        $manifestPath = database_path('seeders_data/books_manifest.json');

        if (! file_exists($manifestPath)) {
            $this->command?->warn('books_manifest.json not found; skipping book seeding. Run tools/prepare_assets.php first.');

            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        $categoryCache = Category::all()->keyBy('name');

        foreach ($manifest as $entry) {
            $categoryName = match ($entry['category_slug']) {
                'arabic-literature' => 'Arabic Literature',
                'literature' => 'Literature',
                'programming' => $this->resolveProgrammingCategory($entry['title']),
                default => 'Programming',
            };

            $category = $categoryCache->get($categoryName);

            $copies = random_int(1, 6);

            $title = $this->cleanTitle($entry['title']);
            $slug = Str::slug($title).'-'.Str::lower(substr(md5($entry['original_filename']), 0, 5));

            Book::updateOrCreate(
                ['cover_source' => $entry['original_filename'], 'category_id' => $category?->id],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'category_id' => $category?->id,
                    'description' => null,
                    'isbn' => null,
                    'published_at' => null,
                    'language' => $entry['category_slug'] === 'arabic-literature' ? 'ar' : 'en',
                    'total_copies' => $copies,
                    'available_copies' => random_int(0, $copies),
                    'cover_path' => $entry['cover_path'],
                    'cover_source' => $entry['original_filename'],
                ]
            );
        }

        $this->command?->info('Seeded '.count($manifest).' books from asset manifest.');
    }

    private function resolveProgrammingCategory(string $title): string
    {
        $normalized = Str::lower($title);

        foreach (self::PROGRAMMING_KEYWORDS as $keyword => $categoryName) {
            if (str_contains($normalized, $keyword)) {
                return $categoryName;
            }
        }

        return 'Programming';
    }
    /**
     * Clean titles imported from filenames without changing the supplied
     * cover assets or inventing missing bibliographic metadata.
     */
    private function cleanTitle(string $title): string
    {
        // Some supplied Arabic filenames contain literal #UXXXX Unicode
        // escapes. Decode those before presenting the title to users.
        $title = preg_replace_callback('/#U([0-9A-Fa-f]{4,6})/', function ($m) {
            $codepoint = hexdec($m[1]);
            return $codepoint <= 0x10FFFF ? mb_chr($codepoint, 'UTF-8') : $m[0];
        }, $title) ?? $title;

        $fixes = [
            'As Good As Dead”' => 'As Good as Dead',
            "Don'tWorry" => "Don't Worry",
            'How To HEAL' => 'How to Heal',
            'I Never know how old Iwas' => 'I Never Know How Old I Was',
            'IT END WITH US' => 'It Ends with Us',
            'IT ENDS WITH US' => 'It Ends with Us',
            'I_will_design_professiona' => 'I Will Design Professionally',
            'Kind words for unkind dayes' => 'Kind Words for Unkind Days',
            'Murder At The Black cat cafe' => 'Murder at the Black Cat Cafe',
            'The Bookestore of Illusion' => 'The Bookstore of Illusion',
            'The Cat Who Saved Bookes' => 'The Cat Who Saved Books',
            'The Housemaid is Watching' => 'The Housemaid Is Watching',
            "The Housemaid's Secret" => "The Housemaid's Secret",
            'The Kite runner' => 'The Kite Runner',
            'The mind- of money' => 'The Mind of Money',
            'Tress of the emerald sea' => 'Tress of the Emerald Sea',
            'Welocome to The Hyunam-Dong' => 'Welcome to the Hyunam-Dong Bookshop',
            'Python prgramming' => 'Python Programming',
            'c++ prime' => 'C++ Primer',
            'essential maths for AI' => 'Essential Maths for AI',
            'head first java' => 'Head First Java',
            'clean code' => 'Clean Code',
        ];

        $title = $fixes[$title] ?? $title;
        $title = preg_replace('/\s+/', ' ', trim($title)) ?? trim($title);

        return $title;
    }

}
