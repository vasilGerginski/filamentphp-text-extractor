<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Feature;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\ExtractedText;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;
use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class BlogPostExtractionTest extends TestCase
{
    private TextExtractionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new TextExtractionService();
    }

    public function test_complete_blog_post_extraction_workflow()
    {
        // Create a comprehensive blog post with all field types
        $blogPost = TestBlogPost::create([
            'title' => 'Building a Modern Laravel Application',
            'slug' => 'building-modern-laravel-application',
            'excerpt' => 'Learn how to build a modern Laravel application with best practices and advanced features.',
            'meta_description' => 'A comprehensive guide to building Laravel applications with modern tools and techniques.',
            'author_email' => 'john.doe@example.com',
            'tags' => ['Laravel', 'PHP', 'Web Development', 'Tutorial'],
            'content' => [
                [
                    'type' => 'heading',
                    'data' => [
                        'level' => 'h2',
                        'content' => 'Introduction to Modern Laravel Development',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'content' => 'Laravel has evolved significantly over the years, becoming one of the most popular PHP frameworks.',
                    ],
                ],
                [
                    'type' => 'callout',
                    'data' => [
                        'type' => 'info',
                        'title' => 'Prerequisites',
                        'content' => 'You should have basic knowledge of PHP and web development concepts.',
                    ],
                ],
                [
                    'type' => 'code',
                    'data' => [
                        'language' => 'php',
                        'code' => '<?php\n\nnamespace App\\Http\\Controllers;\n\nclass HomeController extends Controller\n{\n    public function index()\n    {\n        return view(\'home\');\n    }\n}',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'content' => 'Laravel is the PHP framework for web artisans.',
                        'author' => 'Taylor Otwell',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'url' => '/images/laravel-architecture.png',
                        'alt' => 'Laravel Application Architecture',
                        'caption' => 'The typical structure of a Laravel application',
                    ],
                ],
            ],
            'rich_content' => '<h2>Getting Started</h2><p>To get started with Laravel, you need to install <strong>Composer</strong> and <em>PHP 8.1+</em>.</p><ul><li>Install Composer</li><li>Run composer create-project</li><li>Configure your environment</li></ul>',
        ]);

        // Extract all translatable text
        $this->service->extractFromModel($blogPost);

        // Verify simple text field extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'original_text' => 'Building a Modern Laravel Application',
            'field_type' => 'text',
        ]);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'excerpt',
            'original_text' => 'Learn how to build a modern Laravel application with best practices and advanced features.',
            'field_type' => 'text',
        ]);

        // Verify special field extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'slug',
            'original_text' => 'building-modern-laravel-application',
            'field_type' => 'slug',
        ]);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'author_email',
            'original_text' => 'john.doe@example.com',
            'field_type' => 'email',
        ]);

        // Verify tags extraction
        $tags = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'tags')
            ->pluck('original_text')
            ->toArray();

        $this->assertCount(4, $tags);
        $this->assertEquals(['Laravel', 'PHP', 'Web Development', 'Tutorial'], $tags);

        // Verify builder content extraction
        $builderTexts = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'content')
            ->where('field_type', 'builder')
            ->get();

        // Check heading
        $heading = $builderTexts->firstWhere('original_text', 'Introduction to Modern Laravel Development');
        $this->assertNotNull($heading);
        $this->assertEquals(json_encode(['block_type' => 'heading', 'block_index' => 0]), $heading->context);

        // Check paragraph
        $paragraph = $builderTexts->firstWhere('original_text', 'Laravel has evolved significantly over the years, becoming one of the most popular PHP frameworks.');
        $this->assertNotNull($paragraph);

        // Check callout title and content
        $calloutTitle = $builderTexts->firstWhere('original_text', 'Prerequisites');
        $this->assertNotNull($calloutTitle);
        $calloutContent = $builderTexts->firstWhere('original_text', 'You should have basic knowledge of PHP and web development concepts.');
        $this->assertNotNull($calloutContent);

        // Check quote
        $quote = $builderTexts->firstWhere('original_text', 'Laravel is the PHP framework for web artisans.');
        $this->assertNotNull($quote);

        // Check image alt and caption
        $imageAlt = $builderTexts->firstWhere('original_text', 'Laravel Application Architecture');
        $this->assertNotNull($imageAlt);
        $imageCaption = $builderTexts->firstWhere('original_text', 'The typical structure of a Laravel application');
        $this->assertNotNull($imageCaption);

        // Verify rich text extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'rich_content',
            'field_type' => 'richtext',
        ]);

        // Verify total count of extracted texts
        $totalExtracted = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->count();

        // Should have: title, excerpt, meta_description, slug, author_email, 
        // 4 tags, multiple builder blocks, and rich_content
        $this->assertGreaterThan(15, $totalExtracted);
    }

    public function test_handles_blog_post_with_minimal_data()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Minimal Blog Post',
            'slug' => 'minimal-blog-post',
        ]);

        $this->service->extractFromModel($blogPost);

        $extractedTexts = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->get();

        // Should only extract non-empty fields
        $this->assertEquals(2, $extractedTexts->count());
        $this->assertEquals(['title', 'slug'], $extractedTexts->pluck('field_name')->toArray());
    }

    public function test_updates_blog_post_extracted_texts()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'tags' => ['Original', 'Tags'],
        ]);

        // First extraction
        $this->service->extractFromModel($blogPost);

        // Update blog post
        $blogPost->update([
            'title' => 'Updated Title',
            'tags' => ['Updated', 'Tags', 'New'],
        ]);

        // Second extraction
        $this->service->extractFromModel($blogPost);

        // Check title was updated
        $titleRecord = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'title')
            ->first();

        $this->assertEquals('Updated Title', $titleRecord->original_text);

        // Check tags were updated
        $tagRecords = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'tags')
            ->pluck('original_text')
            ->toArray();

        $this->assertEquals(['Updated', 'Tags', 'New'], $tagRecords);
    }

    public function test_extracts_nested_builder_content()
    {
        $complexContent = [
            [
                'type' => 'section',
                'data' => [
                    'title' => 'Getting Started Section',
                    'blocks' => [
                        [
                            'type' => 'heading',
                            'data' => ['content' => 'Installation Guide'],
                        ],
                        [
                            'type' => 'paragraph',
                            'data' => ['content' => 'Follow these steps to install Laravel.'],
                        ],
                    ],
                ],
            ],
        ];

        $blogPost = TestBlogPost::create([
            'title' => 'Complex Blog Post',
            'slug' => 'complex-blog-post',
            'content' => $complexContent,
        ]);

        $this->service->extractFromModel($blogPost);

        // Verify that nested content is properly extracted
        $extractedContent = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'content')
            ->pluck('original_text')
            ->toArray();

        $this->assertContains('Getting Started Section', $extractedContent);
        $this->assertContains('Installation Guide', $extractedContent);
        $this->assertContains('Follow these steps to install Laravel.', $extractedContent);
    }
}