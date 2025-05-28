# FilamentPHP Text Extractor - Examples & Demos

This directory contains comprehensive examples and live demos showcasing all the features of the FilamentPHP Text Extractor package.

## 🎯 Quick Start

1. **Install the package** in your Laravel project
2. **Copy the demo files** to your Laravel application
3. **Add the routes** to your `routes/web.php`
4. **Run the demos** to see the features in action

## 📁 Demo Files

### 🌍 Translation System Demos

#### `translation-demo.blade.php`
**Classic manual translation approach**
- Shows the traditional `__('blog_post.text')` method
- Language switching functionality
- Demonstrates how auto-generated lang files work
- **Route:** `/translation-demo`

#### `cast-demo.blade.php` 
**Automatic translation with Translatable cast**
- Shows the new `{{ $post->title }}` approach (no manual calls!)
- Side-by-side comparison of old vs new methods
- Live examples with automatic translation
- **Route:** `/cast-demo`

#### `json-demo.blade.php`
**JSON field translation with JsonTranslatable cast**
- Hero component with automatic JSON translation
- Feature grids with nested JSON structures  
- Perfect for page builders and CMS content blocks
- **Route:** `/json-demo`

### 🛠️ Utility Scripts

#### `test-example.php`
**CLI demonstration script**
- Shows extraction statistics
- Tests translation functions
- Displays configuration details
- **Usage:** `php test-example.php`

### 📚 Documentation

#### `LANG_FILES_EXAMPLE.md`
**Comprehensive feature documentation**
- Complete workflow explanation
- Multi-language setup guide
- Use cases and benefits
- Code examples and best practices

#### `EXAMPLE_SUMMARY.md`
**Results summary and metrics**
- Demonstration outcomes
- Performance statistics
- Feature completion status

## 🚀 Routes Setup

Add these routes to your Laravel `routes/web.php`:

```php
// Translation Demos
Route::get('/translation-demo', function () {
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    $posts = App\Models\BlogPost::all();
    return view('translation-demo', compact('posts'));
});

Route::get('/cast-demo', function () {
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    $posts = App\Models\BlogPost::take(3)->get();
    return view('cast-demo', compact('posts'));
});

Route::get('/json-demo', function () {
    if (request('lang') && in_array(request('lang'), ['en', 'es', 'fr', 'de'])) {
        app()->setLocale(request('lang'));
    }
    return view('json-demo');
});
```

## 🎭 Live Demo Features

### 🔄 Language Switching
- **English ↔ Spanish** translation switching
- Real-time content translation
- URL parameter support (`?lang=es`)

### 📊 Translation Casts

#### **Translatable Cast** (Simple Fields)
```php
protected $casts = [
    'title' => Translatable::class,
    'excerpt' => Translatable::class,
    'meta_description' => Translatable::class,
];

// Usage in templates:
{{ $post->title }}        // Automatically translated!
{{ $post->excerpt }}      // Automatically translated!
```

#### **JsonTranslatable Cast** (JSON Fields)
```php
protected $casts = [
    'content' => JsonTranslatable::class,
    'metadata' => JsonTranslatable::class,
];

// Usage with JSON data:
{{ $post->content[0]['title'] }}           // Hero title - auto translated!
{{ $post->content[0]['subtitle'] }}        // Hero subtitle - auto translated!
{{ $post->content[0]['button_text'] }}     // Button text - auto translated!
```

### 🎨 UI Components Supported

The JsonTranslatable cast automatically detects and translates:

- ✅ **Hero Sections:** `title`, `subtitle`, `button_text`
- ✅ **Feature Grids:** `heading`, `description`, `label`
- ✅ **Content Blocks:** `text`, `content`, `caption`
- ✅ **Form Elements:** `placeholder`, `label`, `message`
- ❌ **Technical Fields:** `type`, `icon`, `url`, `id` (ignored)

## 📈 Demo Results

### Automatic Text Extraction
- ✅ **39 unique texts** extracted from 4 blog posts
- ✅ **100% coverage** of translatable fields  
- ✅ **Zero manual work** required

### Translation Files Generated
- ✅ `resources/lang/en/blog_post.php` (39 keys)
- ✅ `resources/lang/es/blog_post.php` (39 translations)
- ✅ **Laravel standard format**

### Cast Performance
- ✅ **English:** Direct field access (`$post->title`)
- ✅ **Spanish:** Automatic translation (`"Comenzando con Laravel"`)
- ✅ **JSON Fields:** Nested structure translation
- ✅ **Fallback Support:** Returns original if no translation

## 🎯 Perfect For

### 🏗️ **Page Builders**
- Hero sections with title/subtitle/CTA
- Feature grids with multiple text fields
- Testimonial blocks with quotes/names
- Call-to-action components

### 📝 **Content Management**
- Flexible content blocks
- Multi-field components  
- Rich media sections
- Editorial layouts

### 🛒 **E-commerce**
- Product feature lists
- Category descriptions
- Marketing banners
- Promotional blocks

### 📋 **Form Builders**
- Field labels and descriptions
- Validation messages
- Help text and placeholders
- Multi-step form content

## 🌟 Key Benefits

- 🚫 **No More `__()`:** Use simple `{{ $property }}` syntax
- 🔄 **Automatic Detection:** Smart field recognition
- 🌍 **Multi-Language Ready:** Works with any Laravel locale
- 📦 **Laravel Standard:** Uses existing translation system
- ⚡ **Zero Config:** Works out of the box
- 🎨 **UI-Focused:** Perfect for component-based content

## 📞 Support

Visit the main [FilamentPHP Text Extractor repository](https://github.com/vasilGerginski/filamentphp-text-extractor) for:
- Installation instructions
- API documentation  
- Issue reporting
- Feature requests

Happy translating! 🌍✨