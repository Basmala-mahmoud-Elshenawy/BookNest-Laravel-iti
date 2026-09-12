<?php
// One-off asset preparation script: copies every provided book cover into
// the Laravel public storage structure with a filesystem-safe name, and
// writes a JSON manifest (title, category folder, cover path, original
// filename) that BookSeeder.php consumes. Not part of the request cycle.

$sourceRoot = '/home/claude/assets/BookNest-Assets/books';
$destRoot = '/home/claude/booknest/storage/app/public/books';
$manifestPath = '/home/claude/booknest/database/seeders_data/books_manifest.json';

$folderToSlug = [
    'Arabic books' => 'arabic-literature',
    'English books' => 'literature',
    'Programing books' => 'programming',
];

function slugify(string $text): string
{
    $text = trim($text);
    // Transliterate where possible, otherwise keep unicode but strip unsafe chars.
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $base = $ascii !== false ? $ascii : $text;
    $base = preg_replace('/[^A-Za-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    $base = strtolower($base);
    return $base !== '' ? $base : 'cover';
}

$manifest = [];
$counter = [];

foreach ($folderToSlug as $folder => $categorySlug) {
    $dir = "$sourceRoot/$folder";
    if (!is_dir($dir)) continue;
    $destDir = "$destRoot/$categorySlug";
    if (!is_dir($destDir)) mkdir($destDir, 0777, true);

    $files = array_values(array_filter(scandir($dir), fn($f) => !in_array($f, ['.', '..'])));
    sort($files);

    foreach ($files as $file) {
        $srcPath = "$dir/$file";
        if (!is_file($srcPath)) continue;

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

        $rawTitle = pathinfo($file, PATHINFO_FILENAME);
        // Clean common artifacts (stray curly-quote entities, extra spaces, trailing "2" volume markers kept as-is).
        $title = str_replace(['_'], [' '], $rawTitle);
        $title = preg_replace('/\s+/', ' ', trim($title));
        $title = trim($title, " \t\n\r\0\x0B\"'");

        $slugBase = slugify($rawTitle);
        $counter[$categorySlug] = ($counter[$categorySlug] ?? 0) + 1;
        $destFilename = $slugBase . '-' . $counter[$categorySlug] . '.' . $ext;
        $destPath = "$destDir/$destFilename";
        copy($srcPath, $destPath);

        $manifest[] = [
            'title' => $title !== '' ? $title : $rawTitle,
            'category_folder' => $folder,
            'category_slug' => $categorySlug,
            'cover_path' => "books/$categorySlug/$destFilename",
            'original_filename' => $file,
        ];
    }
}

file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fwrite(STDOUT, "Prepared " . count($manifest) . " book cover assets.\n");
foreach ($counter as $slug => $count) {
    fwrite(STDOUT, "  $slug: $count\n");
}
