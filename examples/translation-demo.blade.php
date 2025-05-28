<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Demo - FilamentPHP Text Extractor</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .post { border: 1px solid #ddd; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .meta { color: #666; font-size: 0.9em; }
        .tags { margin-top: 10px; }
        .tag { background: #e3f2fd; padding: 4px 8px; border-radius: 4px; margin-right: 5px; display: inline-block; font-size: 0.8em; }
        .lang-switcher { margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px; }
        .lang-switcher a { margin-right: 10px; padding: 5px 10px; text-decoration: none; background: #007cba; color: white; border-radius: 4px; }
        .lang-switcher a.active { background: #28a745; }
        .translation-note { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>🌍 Translation Demo - FilamentPHP Text Extractor</h1>
    
    <div class="translation-note">
        <strong>Note:</strong> This demo shows how the auto-generated translation files work. 
        Currently showing: <strong>{{ app()->getLocale() }}</strong> locale.
        All texts below are being pulled from <code>resources/lang/{{ app()->getLocale() }}/blog_post.php</code>
    </div>

    <div class="lang-switcher">
        <strong>Switch Language:</strong>
        <a href="?lang=en" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">🇺🇸 English</a>
        <a href="?lang=es" class="{{ app()->getLocale() == 'es' ? 'active' : '' }}">🇪🇸 Español</a>
        <a href="?lang=fr" class="{{ app()->getLocale() == 'fr' ? 'active' : '' }}">🇫🇷 Français</a>
    </div>

    @foreach($posts as $post)
        <article class="post">
            <h2>{{ __('blog_post.' . $post->title) }}</h2>
            
            <div class="meta">
                <strong>{{ __('blog_post.Meta description') }}:</strong> 
                {{ __('blog_post.' . $post->meta_description) }}
            </div>
            
            <p><strong>{{ __('blog_post.Excerpt') }}:</strong> {{ __('blog_post.' . $post->excerpt) }}</p>
            
            @if($post->content && is_array($post->content))
                <div class="content">
                    @foreach($post->content as $block)
                        @if(is_array($block) && isset($block['type']) && isset($block['text']))
                            @if($block['type'] === 'heading')
                                <h3>{{ __('blog_post.' . $block['text']) }}</h3>
                            @elseif($block['type'] === 'paragraph')
                                <p>{{ __('blog_post.' . $block['text']) }}</p>
                            @endif
                        @endif
                    @endforeach
                </div>
            @endif
            
            @if($post->tags && is_array($post->tags))
                <div class="tags">
                    <strong>{{ __('blog_post.Tags') }}:</strong>
                    @foreach($post->tags as $tag)
                        <span class="tag">{{ __('blog_post.' . $tag) }}</span>
                    @endforeach
                </div>
            @endif
            
            <div class="meta">
                <small>{{ __('blog_post.Author') }}: {{ $post->author_email }}</small>
            </div>
        </article>
    @endforeach

    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>🎯 How This Works</h3>
        <ol>
            <li><strong>Automatic Extraction:</strong> When BlogPost records were created, the FilamentPHP Text Extractor automatically extracted all translatable text</li>
            <li><strong>Lang File Generation:</strong> A translation file was generated at <code>resources/lang/en/blog_post.php</code></li>
            <li><strong>Translation Usage:</strong> Each text is accessed using Laravel's <code>__()</code> helper with the key pattern <code>blog_post.{original_text}</code></li>
            <li><strong>Multi-language Ready:</strong> Copy the file to other locales (es, fr, etc.) and translate the values</li>
        </ol>
        
        <p><strong>Generated {{ count(array_keys(__('blog_post'))) }} translation keys</strong> from your BlogPost content!</p>
    </div>
</body>
</html>