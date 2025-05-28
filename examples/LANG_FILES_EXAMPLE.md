# FilamentPHP Text Extractor - Lang Files Feature Example

This example demonstrates the automatic language file generation feature of the FilamentPHP Text Extractor package.

## 🚀 What Just Happened

When we created new BlogPost records, the package automatically:
1. **Extracted all translatable text** from the model fields
2. **Generated a lang file** at `resources/lang/en/blog_post.php`
3. **Stored extraction data** in the `extracted_texts` database table

## 📁 Generated Lang File

**Location:** `resources/lang/en/blog_post.php`

```php
<?php

return [
    'About us' => 'About us',
    'Advanced PHP Techniques' => 'Advanced PHP Techniques',
    'Complete Laravel tutorial for beginners - learn MVC, routing, and more' => 'Complete Laravel tutorial for beginners - learn MVC, routing, and more',
    'Design Patterns' => 'Design Patterns',
    'Discover advanced PHP programming patterns and best practices for professional development' => 'Discover advanced PHP programming patterns and best practices for professional development',
    'Getting Started with Laravel' => 'Getting Started with Laravel',
    'Introduction' => 'Introduction',
    'Laravel is a powerful PHP framework that makes web development enjoyable and efficient.' => 'Laravel is a powerful PHP framework that makes web development enjoyable and efficient.',
    'Learn how to optimize your PHP applications for better performance.' => 'Learn how to optimize your PHP applications for better performance.',
    'Learn the basics of Laravel framework and build your first application' => 'Learn the basics of Laravel framework and build your first application',
    'Master advanced PHP techniques including design patterns, optimization, and best practices' => 'Master advanced PHP techniques including design patterns, optimization, and best practices',
    'Performance Optimization' => 'Performance Optimization',
    'Understanding design patterns is crucial for writing maintainable code.' => 'Understanding design patterns is crucial for writing maintainable code.',
    'best practices' => 'best practices',
    'web development' => 'web development',
    // ... and more
];
```

## 🔧 How to Use Translations

### 1. Manual Translation in Views

```php
// In your Blade templates
{{ __('blog_post.Getting Started with Laravel') }}
{{ __('blog_post.Learn the basics of Laravel framework and build your first application') }}
```

### 2. Using the Translatable Cast (Coming Soon)

```php
// In your BlogPost model
protected $casts = [
    'title' => Translatable::class,
    'excerpt' => Translatable::class,
    'meta_description' => Translatable::class,
];

// Usage in controllers/views
$post = BlogPost::first();
echo $post->title; // Automatically returns translated version if available
```

## 🌍 Multi-Language Support

### Step 1: Translate the Generated File

Copy the English file to other languages:

```bash
# Spanish
cp resources/lang/en/blog_post.php resources/lang/es/blog_post.php
# French  
cp resources/lang/en/blog_post.php resources/lang/fr/blog_post.php
# German
cp resources/lang/en/blog_post.php resources/lang/de/blog_post.php
```

### Step 2: Update Translations

Edit each language file:

**resources/lang/es/blog_post.php:**
```php
<?php

return [
    'Getting Started with Laravel' => 'Comenzando con Laravel',
    'Learn the basics of Laravel framework and build your first application' => 'Aprende los conceptos básicos del framework Laravel y construye tu primera aplicación',
    'Advanced PHP Techniques' => 'Técnicas Avanzadas de PHP',
    'Design Patterns' => 'Patrones de Diseño',
    'Performance Optimization' => 'Optimización de Rendimiento',
    // ... translate other keys
];
```

### Step 3: Use in Your Application

```php
// Set locale
App::setLocale('es');

// Use translations
{{ __('blog_post.Getting Started with Laravel') }}
// Output: "Comenzando con Laravel"
```

## 📊 Database Structure

The package stores extraction metadata in the `extracted_texts` table:

```sql
SELECT model_type, model_id, text_key, text_value, locale, is_translated 
FROM extracted_texts 
WHERE model_type = 'App\\Models\\BlogPost' 
LIMIT 5;
```

| model_type | model_id | text_key | text_value | locale | is_translated |
|------------|----------|----------|------------|--------|---------------|
| App\Models\BlogPost | 3 | title | Getting Started with Laravel | en | 0 |
| App\Models\BlogPost | 3 | excerpt | Learn the basics of Laravel... | en | 0 |
| App\Models\BlogPost | 4 | title | Advanced PHP Techniques | en | 0 |

## ⚙️ Configuration

The feature is controlled by these config options in `config/filament-text-extractor.php`:

```php
'auto_generate_lang_files' => env('AUTO_GENERATE_LANG_FILES', true),
'lang_file_pattern' => '{model_name}.php',
'locales' => ['en', 'es', 'fr', 'de'],
```

## 🎯 Use Cases

1. **Content Management Systems** - Automatically extract all user-generated content
2. **E-commerce Sites** - Extract product names, descriptions, categories
3. **Blogs & News Sites** - Extract article titles, excerpts, content
4. **Multi-tenant Applications** - Different translations per tenant
5. **API Documentation** - Extract and translate API endpoint descriptions

## 🔄 Workflow Example

```php
// 1. Create content (automatic extraction happens)
$post = BlogPost::create([
    'title' => 'New Feature Announcement',
    'excerpt' => 'We are excited to introduce our latest feature...'
]);

// 2. Lang file automatically updated with new texts
// 3. Translate the values in lang files manually or via translation service
// 4. Use translated content throughout your app

// In Blade templates:
<h1>{{ __('blog_post.New Feature Announcement') }}</h1>
<p>{{ __('blog_post.We are excited to introduce our latest feature...') }}</p>
```

## 🚀 Benefits

- ✅ **Zero Manual Work** - Texts are extracted automatically
- ✅ **Laravel Standard** - Uses standard Laravel translation files
- ✅ **Developer Friendly** - Familiar `__()` helper function
- ✅ **Scalable** - Works with any number of models and languages
- ✅ **Flexible** - Support for complex JSON fields and rich text
- ✅ **Maintainable** - Clear separation between content and translations

## 🎉 Result

You now have a fully automated system that:
1. Extracts all translatable content from your models
2. Generates Laravel-compatible translation files
3. Enables easy multi-language support
4. Maintains clean separation between content and translations

Ready to scale your application globally! 🌍