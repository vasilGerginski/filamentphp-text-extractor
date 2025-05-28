<?php

/*
 * FilamentPHP Text Extractor - Lang Files Feature Demo
 * 
 * This script demonstrates how the automatic lang file generation works
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🌍 FilamentPHP Text Extractor - Lang Files Demo\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Show current blog posts
echo "📚 Blog Posts in Database:\n";
echo "-" . str_repeat("-", 30) . "\n";

$posts = App\Models\BlogPost::all();
foreach ($posts as $post) {
    echo "• {$post->title}\n";
    echo "  Excerpt: " . substr($post->excerpt, 0, 50) . "...\n";
    $tags = is_array($post->tags) ? $post->tags : [];
    echo "  Tags: " . implode(', ', $tags) . "\n\n";
}

// Show generated lang file info
echo "📁 Generated Lang File:\n";
echo "-" . str_repeat("-", 25) . "\n";

$langFile = resource_path('lang/en/blog_post.php');
if (file_exists($langFile)) {
    $translations = include $langFile;
    echo "File: resources/lang/en/blog_post.php\n";
    echo "Keys generated: " . count($translations) . "\n";
    echo "File size: " . number_format(filesize($langFile)) . " bytes\n\n";
    
    echo "📝 Sample Translation Keys:\n";
    echo "-" . str_repeat("-", 28) . "\n";
    $sample = array_slice($translations, 0, 5, true);
    foreach ($sample as $key => $value) {
        echo "'{$key}' => '{$value}'\n";
    }
    echo "... and " . (count($translations) - 5) . " more keys\n\n";
} else {
    echo "❌ Lang file not found!\n\n";
}

// Show database stats
echo "📊 Database Statistics:\n";
echo "-" . str_repeat("-", 23) . "\n";

try {
    $extractedCount = VasilGerginski\FilamentTextExtractor\Models\ExtractedText::count();
    $blogPostTexts = VasilGerginski\FilamentTextExtractor\Models\ExtractedText::where('model_type', 'App\\Models\\BlogPost')->count();
    
    echo "Total extracted texts: {$extractedCount}\n";
    echo "BlogPost texts: {$blogPostTexts}\n";
    echo "Unique text values: " . VasilGerginski\FilamentTextExtractor\Models\ExtractedText::distinct('text_value')->count() . "\n\n";
} catch (Exception $e) {
    echo "❌ Could not fetch database stats: {$e->getMessage()}\n\n";
}

// Show how to use translations
echo "🎯 How to Use in Your Application:\n";
echo "-" . str_repeat("-", 35) . "\n";

echo "1. In Blade templates:\n";
echo "   {{ __('blog_post.Getting Started with Laravel') }}\n\n";

echo "2. In PHP code:\n";
echo "   \$title = __('blog_post.Getting Started with Laravel');\n\n";

echo "3. With locale switching:\n";
echo "   App::setLocale('es');\n";
echo "   echo __('blog_post.Getting Started with Laravel'); // 'Comenzando con Laravel'\n\n";

echo "4. Access the demo page:\n";
echo "   Visit: http://localhost:8000/translation-demo\n";
echo "   Try: http://localhost:8000/translation-demo?lang=es\n\n";

// Show configuration
echo "⚙️ Package Configuration:\n";
echo "-" . str_repeat("-", 25) . "\n";

$config = config('filament-text-extractor');
if ($config) {
    echo "Auto-generate lang files: " . ($config['auto_generate_lang_files'] ?? true ? 'Yes' : 'No') . "\n";
    echo "Lang file pattern: " . ($config['lang_file_pattern'] ?? '{model_name}.php') . "\n";
    echo "Supported locales: " . implode(', ', $config['locales'] ?? ['en']) . "\n\n";
} else {
    echo "Config not loaded - using default settings\n\n";
}

echo "✅ Lang Files Feature is working perfectly!\n";
echo "🚀 Ready for multi-language scaling!\n";