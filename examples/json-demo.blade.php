<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Translation Demo - FilamentPHP Text Extractor</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .lang-switcher { margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; }
        .lang-switcher a { margin-right: 10px; padding: 8px 15px; text-decoration: none; background: #007cba; color: white; border-radius: 4px; }
        .lang-switcher a.active { background: #28a745; }
        .hero-component { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 40px; border-radius: 12px; text-align: center; margin: 30px 0; }
        .hero-title { font-size: 3em; font-weight: bold; margin-bottom: 20px; }
        .hero-subtitle { font-size: 1.3em; margin-bottom: 30px; opacity: 0.9; }
        .hero-button { background: #ff6b6b; color: white; padding: 15px 30px; border: none; border-radius: 25px; font-size: 1.1em; cursor: pointer; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin: 40px 0; }
        .feature-card { background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .feature-title { font-size: 1.4em; font-weight: bold; color: #333; margin-bottom: 15px; }
        .feature-description { color: #666; }
        .code-example { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: monospace; margin: 20px 0; }
        .demo-section { border: 2px solid #007cba; margin: 30px 0; padding: 25px; border-radius: 12px; background: #f8f9fa; }
        .json-structure { background: #fffacd; border: 1px solid #ddd; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>🌍 JSON Field Translation Demo</h1>
    
    <div class="lang-switcher">
        <strong>Language:</strong> {{ app()->getLocale() }} | 
        <a href="?lang=en" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">🇺🇸 English</a>
        <a href="?lang=es" class="{{ app()->getLocale() == 'es' ? 'active' : '' }}">🇪🇸 Español</a>
    </div>

    @php
        $post = App\Models\BlogPost::where('title', 'Hero Component Demo')->first();
        $content = $post->content;
    @endphp

    <div class="demo-section">
        <h2>✨ Live Hero Component (Translated JSON)</h2>
        
        @if(isset($content[0]) && $content[0]['type'] === 'hero')
            <div class="hero-component">
                <div class="hero-title">{{ $content[0]['title'] }}</div>
                <div class="hero-subtitle">{{ $content[0]['subtitle'] }}</div>
                <button class="hero-button">{{ $content[0]['button_text'] }}</button>
            </div>
        @endif
    </div>

    <div class="demo-section">
        <h2>🔧 Features Section (Translated JSON)</h2>
        
        @if(isset($content[1]) && $content[1]['type'] === 'feature_grid')
            <h3 style="text-align: center; margin-bottom: 30px;">{{ $content[1]['heading'] }}</h3>
            
            <div class="features-grid">
                @foreach($content[1]['items'] as $item)
                    <div class="feature-card">
                        <div class="feature-title">{{ $item['title'] }}</div>
                        <div class="feature-description">{{ $item['description'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="demo-section">
        <h2>📊 How It Works</h2>
        
        <h3>1. Original JSON Structure (Stored in Database)</h3>
        <div class="json-structure">{{ json_encode($post->getRawOriginal('content'), JSON_PRETTY_PRINT) }}</div>

        <h3>2. Template Code (No Translation Calls!)</h3>
        <div class="code-example">@<!-- Hero Component -->
@if($content[0]['type'] === 'hero')
    &lt;div class="hero-component"&gt;
        &lt;div class="hero-title"&gt;@{{ $content[0]['title'] }}&lt;/div&gt;
        &lt;div class="hero-subtitle"&gt;@{{ $content[0]['subtitle'] }}&lt;/div&gt;
        &lt;button class="hero-button"&gt;@{{ $content[0]['button_text'] }}&lt;/button&gt;
    &lt;/div&gt;
@endif</div>

        <h3>3. JsonTranslatable Cast Magic</h3>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px;">
            ✅ Automatically detects translatable fields: <code>title</code>, <code>subtitle</code>, <code>button_text</code><br>
            ✅ Ignores technical fields: <code>type</code>, <code>background_image</code>, <code>icon</code><br>
            ✅ Works recursively through nested JSON structures<br>
            ✅ Falls back to original text if no translation found<br>
            ✅ Uses standard Laravel translation files
        </div>
    </div>

    <div class="demo-section">
        <h2>⚙️ Setup</h2>
        
        <h3>1. Add JsonTranslatable Cast to Model</h3>
        <div class="code-example">protected $casts = [
    'content' => JsonTranslatable::class,
    'metadata' => JsonTranslatable::class,
    // ... other casts
];</div>

        <h3>2. Use JSON Fields Normally in Templates</h3>
        <div class="code-example">@{{ $post->content[0]['title'] }}           // Automatically translated!
@{{ $post->content[0]['subtitle'] }}        // Automatically translated!
@{{ $post->metadata['page_title'] }}        // Automatically translated!</div>

        <h3>3. Translatable Field Detection</h3>
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;">
            <strong>Automatically translates fields with these names:</strong><br>
            <code>title</code>, <code>subtitle</code>, <code>heading</code>, <code>text</code>, <code>content</code>, <code>description</code>, <code>label</code>, <code>button_text</code>, <code>message</code>, <code>caption</code>
        </div>
    </div>

    <div class="demo-section">
        <h2>🎯 Perfect for:</h2>
        <ul style="font-size: 1.1em;">
            <li><strong>Page Builders:</strong> Hero sections, feature grids, testimonials</li>
            <li><strong>CMS Content:</strong> Flexible content blocks with multiple fields</li>
            <li><strong>Form Builders:</strong> Field labels, descriptions, validation messages</li>
            <li><strong>E-commerce:</strong> Product features, specifications, descriptions</li>
            <li><strong>Marketing:</strong> Landing page components, call-to-action blocks</li>
        </ul>
    </div>

    <div style="margin-top: 40px; padding: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; text-align: center; color: white;">
        <h3>🚀 FilamentPHP Text Extractor v1.2.0</h3>
        <p style="font-size: 1.2em; margin: 15px 0;">
            <strong>JSON Field Translation Made Simple</strong>
        </p>
        <p>Build multilingual UI components without the complexity!</p>
    </div>
</body>
</html>