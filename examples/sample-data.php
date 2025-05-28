<?php

/*
|--------------------------------------------------------------------------
| Sample Data for FilamentPHP Text Extractor Demos
|--------------------------------------------------------------------------
|
| Run this in your Laravel tinker to create sample blog posts for testing
| php artisan tinker
| include 'examples/sample-data.php';
|
*/

// Sample Blog Post 1: Getting Started Guide
App\Models\BlogPost::create([
    'title' => 'Getting Started with Laravel',
    'slug' => 'getting-started-laravel',
    'excerpt' => 'Learn the basics of Laravel framework and build your first application',
    'content' => [
        ['type' => 'heading', 'text' => 'Introduction'],
        ['type' => 'paragraph', 'text' => 'Laravel is a powerful PHP framework that makes web development enjoyable and efficient.'],
        ['type' => 'paragraph', 'text' => 'In this guide, we will explore the key features and concepts.']
    ],
    'meta_description' => 'Complete Laravel tutorial for beginners - learn MVC, routing, and more',
    'rich_content' => '<h2>Welcome to Laravel</h2><p>This is <strong>rich text</strong> content with HTML formatting.</p>',
    'author_email' => 'john@example.com',
    'tags' => ['php', 'laravel', 'tutorial', 'web development']
]);

// Sample Blog Post 2: Advanced Techniques
App\Models\BlogPost::create([
    'title' => 'Advanced PHP Techniques',
    'slug' => 'advanced-php-techniques',
    'excerpt' => 'Discover advanced PHP programming patterns and best practices for professional development',
    'content' => [
        ['type' => 'heading', 'text' => 'Design Patterns'],
        ['type' => 'paragraph', 'text' => 'Understanding design patterns is crucial for writing maintainable code.'],
        ['type' => 'heading', 'text' => 'Performance Optimization'],
        ['type' => 'paragraph', 'text' => 'Learn how to optimize your PHP applications for better performance.']
    ],
    'meta_description' => 'Master advanced PHP techniques including design patterns, optimization, and best practices',
    'rich_content' => '<h2>Code Quality</h2><p>Writing <em>clean</em> and <strong>efficient</strong> code is essential.</p>',
    'author_email' => 'jane@example.com',
    'tags' => ['php', 'advanced', 'patterns', 'optimization', 'best practices']
]);

// Sample Blog Post 3: Hero Component Demo (JSON Translation Demo)
App\Models\BlogPost::create([
    'title' => 'Hero Component Demo',
    'slug' => 'hero-component-demo',
    'excerpt' => 'Demonstrates JSON field translation with hero components',
    'content' => [
        [
            'type' => 'hero',
            'title' => 'Welcome to Our Platform',
            'subtitle' => 'Build amazing applications with our tools',
            'button_text' => 'Get Started Now',
            'background_image' => '/images/hero-bg.jpg'
        ],
        [
            'type' => 'feature_grid', 
            'heading' => 'Key Features',
            'items' => [
                [
                    'title' => 'Easy Integration',
                    'description' => 'Seamlessly integrate with your existing workflow',
                    'icon' => 'check-circle'
                ],
                [
                    'title' => 'Powerful Analytics',
                    'description' => 'Get insights into your application performance',
                    'icon' => 'chart-bar'
                ]
            ]
        ]
    ],
    'meta_description' => 'Demo of JSON field translation capabilities',
    'author_email' => 'demo@example.com',
    'tags' => ['demo', 'json', 'translation', 'hero', 'components']
]);

echo "✅ Sample blog posts created successfully!\n";
echo "📊 Total posts: " . App\Models\BlogPost::count() . "\n";
echo "🌍 Run text extraction: php artisan text:extract BlogPost\n";
echo "🎯 View demos at:\n";
echo "   - /translation-demo\n";
echo "   - /cast-demo\n";
echo "   - /json-demo\n";