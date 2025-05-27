<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Feature;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\ExtractedText;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;
use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class TextExtractionServiceTest extends TestCase
{
    private TextExtractionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new TextExtractionService();
    }

    public function test_extracts_simple_text_fields()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post Title',
            'slug' => 'test-blog-post',
            'excerpt' => 'This is a test excerpt for the blog post',
            'meta_description' => 'Meta description for SEO purposes',
        ]);

        $this->service->extractFromModel($blogPost);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'original_text' => 'Test Blog Post Title',
            'field_type' => 'text',
        ]);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'excerpt',
            'original_text' => 'This is a test excerpt for the blog post',
            'field_type' => 'text',
        ]);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'meta_description',
            'original_text' => 'Meta description for SEO purposes',
            'field_type' => 'text',
        ]);
    }

    public function test_extracts_json_array_fields()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'tags' => ['Laravel', 'PHP', 'Testing', 'Filament'],
        ]);

        $this->service->extractFromModel($blogPost);

        $extractedTags = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'tags')
            ->get();

        $this->assertCount(4, $extractedTags);
        
        $tagValues = $extractedTags->pluck('original_text')->toArray();
        $this->assertContains('Laravel', $tagValues);
        $this->assertContains('PHP', $tagValues);
        $this->assertContains('Testing', $tagValues);
        $this->assertContains('Filament', $tagValues);
    }

    public function test_extracts_builder_content_fields()
    {
        $builderContent = [
            [
                'type' => 'heading',
                'data' => [
                    'level' => 'h2',
                    'content' => 'Introduction to Testing',
                ],
            ],
            [
                'type' => 'paragraph',
                'data' => [
                    'content' => 'This is a paragraph about testing in Laravel applications.',
                ],
            ],
            [
                'type' => 'quote',
                'data' => [
                    'content' => 'Testing is the key to reliable software.',
                    'author' => 'Unknown Developer',
                ],
            ],
            [
                'type' => 'callout',
                'data' => [
                    'type' => 'info',
                    'title' => 'Important Note',
                    'content' => 'Always write tests for your critical features.',
                ],
            ],
        ];

        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'content' => $builderContent,
        ]);

        $this->service->extractFromModel($blogPost);

        // Check heading extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'content',
            'original_text' => 'Introduction to Testing',
            'field_type' => 'builder',
            'context' => json_encode(['block_type' => 'heading', 'block_index' => 0]),
        ]);

        // Check paragraph extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'content',
            'original_text' => 'This is a paragraph about testing in Laravel applications.',
            'field_type' => 'builder',
            'context' => json_encode(['block_type' => 'paragraph', 'block_index' => 1]),
        ]);

        // Check quote extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'content',
            'original_text' => 'Testing is the key to reliable software.',
            'field_type' => 'builder',
            'context' => json_encode(['block_type' => 'quote', 'block_index' => 2]),
        ]);

        // Check callout extraction
        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'content',
            'original_text' => 'Important Note',
            'field_type' => 'builder',
            'context' => json_encode(['block_type' => 'callout', 'block_index' => 3, 'field' => 'title']),
        ]);
    }

    public function test_extracts_rich_text_fields()
    {
        $richContent = '<h2>Rich Text Heading</h2><p>This is a <strong>rich text</strong> paragraph with <em>formatting</em>.</p>';
        
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'rich_content' => $richContent,
        ]);

        $this->service->extractFromModel($blogPost);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'rich_content',
            'original_text' => $richContent,
            'field_type' => 'richtext',
        ]);
    }

    public function test_extracts_special_fields()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post-slug',
            'author_email' => 'author@example.com',
        ]);

        $this->service->extractFromModel($blogPost);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'slug',
            'original_text' => 'test-blog-post-slug',
            'field_type' => 'slug',
        ]);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'author_email',
            'original_text' => 'author@example.com',
            'field_type' => 'email',
        ]);
    }

    public function test_updates_existing_extracted_text()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
        ]);

        // First extraction
        $this->service->extractFromModel($blogPost);

        $this->assertDatabaseHas('extracted_texts', [
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'original_text' => 'Original Title',
        ]);

        // Update the blog post
        $blogPost->update(['title' => 'Updated Title']);

        // Second extraction
        $this->service->extractFromModel($blogPost);

        // Should update the existing record, not create a new one
        $titleRecords = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->where('field_name', 'title')
            ->get();

        $this->assertCount(1, $titleRecords);
        $this->assertEquals('Updated Title', $titleRecords->first()->original_text);
    }

    public function test_handles_empty_fields_gracefully()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'excerpt' => null,
            'meta_description' => '',
            'tags' => [],
            'content' => null,
        ]);

        $this->service->extractFromModel($blogPost);

        // Should only extract non-empty fields
        $extractedTexts = ExtractedText::where('model_type', TestBlogPost::class)
            ->where('model_id', $blogPost->id)
            ->get();

        $this->assertEquals(2, $extractedTexts->count()); // Only title and slug
        $this->assertTrue($extractedTexts->contains('field_name', 'title'));
        $this->assertTrue($extractedTexts->contains('field_name', 'slug'));
    }

    public function test_batch_extraction_for_multiple_models()
    {
        $blogPosts = [];
        for ($i = 1; $i <= 5; $i++) {
            $blogPosts[] = TestBlogPost::create([
                'title' => "Blog Post {$i}",
                'slug' => "blog-post-{$i}",
                'excerpt' => "Excerpt for blog post {$i}",
            ]);
        }

        foreach ($blogPosts as $blogPost) {
            $this->service->extractFromModel($blogPost);
        }

        $totalExtracted = ExtractedText::where('model_type', TestBlogPost::class)->count();
        $this->assertEquals(15, $totalExtracted); // 3 fields × 5 posts
    }
}