<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translatable Cast Demo - FilamentPHP Text Extractor</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .demo-section { border: 2px solid #007cba; margin: 20px 0; padding: 20px; border-radius: 8px; background: #f8f9fa; }
        .code-example { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        .result { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .lang-switcher { margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; }
        .lang-switcher a { margin-right: 10px; padding: 8px 15px; text-decoration: none; background: #007cba; color: white; border-radius: 4px; }
        .lang-switcher a.active { background: #28a745; }
        .comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .comparison > div { padding: 15px; border-radius: 5px; }
        .old-way { background: #fff3cd; border: 1px solid #ffeaa7; }
        .new-way { background: #d4edda; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <h1>🎯 Translatable Cast Demo</h1>
    
    <div class="lang-switcher">
        <strong>Current Language:</strong> <strong>{{ app()->getLocale() }}</strong> | 
        <a href="?lang=en" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">🇺🇸 English</a>
        <a href="?lang=es" class="{{ app()->getLocale() == 'es' ? 'active' : '' }}">🇪🇸 Español</a>
    </div>

    <div class="demo-section">
        <h2>✨ Magic in Action</h2>
        <p>With the <code>Translatable</code> cast, you can now simply use <code>@{{ $post->title }}</code> and get automatic translations!</p>
        
        <div class="comparison">
            <div class="old-way">
                <h3>❌ Old Way (Manual)</h3>
                <div class="code-example">@{{ __('blog_post.' . $post->title) }}</div>
            </div>
            
            <div class="new-way">
                <h3>✅ New Way (Automatic)</h3>
                <div class="code-example">@{{ $post->title }}</div>
                <p><strong>Just works!</strong> 🎉</p>
            </div>
        </div>
    </div>

    <div class="demo-section">
        <h2>🧪 Live Examples</h2>
        
        @foreach($posts as $post)
            <div style="border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 5px; background: white;">
                <h3 style="margin-top: 0;">{{ $post->title }}</h3>
                <p><strong>Excerpt:</strong> {{ $post->excerpt }}</p>
                <p><strong>SEO Description:</strong> {{ $post->meta_description }}</p>
                
                <div style="font-size: 0.9em; color: #666; margin-top: 10px;">
                    <strong>Behind the scenes:</strong>
                    <ul>
                        <li>Original stored: "{{ $post->getRawOriginal('title') }}"</li>
                        <li>Auto-translated to: "{{ $post->title }}"</li>
                        <li>Language: {{ app()->getLocale() }}</li>
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <div class="demo-section">
        <h2>🔧 How It Works</h2>
        
        <h3>1. Add the Cast to Your Model</h3>
        <div class="code-example">protected $casts = [
    'title' => Translatable::class,
    'excerpt' => Translatable::class,
    'meta_description' => Translatable::class,
];</div>

        <h3>2. Use Properties Normally</h3>
        <div class="code-example">// In your Blade templates or PHP code
echo $post->title;        // Automatically translated!
echo $post->excerpt;      // Automatically translated!
echo $post->meta_description; // Automatically translated!</div>

        <h3>3. The Cast Automatically:</h3>
        <div class="result">
            ✅ Checks <code>resources/lang/{{ app()->getLocale() }}/blog_post.php</code> for translations<br>
            ✅ Falls back to database <code>translated_value</code> if needed<br>
            ✅ Returns original value if no translation found<br>
            ✅ Works with any locale you set via <code>App::setLocale()</code>
        </div>
    </div>

    <div class="demo-section">
        <h2>🚀 Benefits</h2>
        <ul>
            <li><strong>Zero Code Changes:</strong> Use <code>$post->title</code> everywhere, translations work automatically</li>
            <li><strong>Clean Templates:</strong> No more <code>__()</code> calls cluttering your views</li>
            <li><strong>Smart Fallbacks:</strong> Always returns something meaningful</li>
            <li><strong>Laravel Standard:</strong> Uses standard Laravel translation files</li>
            <li><strong>Performance:</strong> Cached translation lookups</li>
        </ul>
    </div>

    <div style="margin-top: 40px; padding: 20px; background: #e3f2fd; border-radius: 8px; text-align: center;">
        <h3>🎉 FilamentPHP Text Extractor v1.2.0</h3>
        <p>Automatic text extraction + language file generation + transparent translation casts = 🔥</p>
        <p><strong>Multi-language apps made effortless!</strong></p>
    </div>
</body>
</html>