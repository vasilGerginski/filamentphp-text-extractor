<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Feature;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class BlogPostResourceTest extends TestCase
{
    public function test_blog_post_with_builder_form_content()
    {
        // Create a comprehensive blog post with all the builder form fields
        $builderContent = [
            [
                'type' => 'heading',
                'data' => [
                    'level' => 'h2',
                    'content' => 'Getting Started with Laravel',
                ],
            ],
            [
                'type' => 'paragraph',
                'data' => [
                    'content' => 'Laravel is a web application framework with expressive, elegant syntax.',
                ],
            ],
            [
                'type' => 'image',
                'data' => [
                    'url' => '/storage/images/laravel-logo.png',
                    'alt' => 'Laravel Logo',
                    'caption' => 'The Laravel framework logo',
                ],
            ],
            [
                'type' => 'quote',
                'data' => [
                    'content' => 'The PHP Framework for Web Artisans',
                    'author' => 'Laravel Team',
                ],
            ],
            [
                'type' => 'code',
                'data' => [
                    'language' => 'php',
                    'code' => '<?php\n\nRoute::get("/", function () {\n    return view("welcome");\n});',
                ],
            ],
            [
                'type' => 'callout',
                'data' => [
                    'type' => 'info',
                    'title' => 'Prerequisites',
                    'content' => 'Make sure you have PHP 8.1+ and Composer installed.',
                ],
            ],
        ];

        $blogPost = TestBlogPost::create([
            'title' => 'Complete Laravel Tutorial',
            'slug' => 'complete-laravel-tutorial',
            'excerpt' => 'Learn Laravel from scratch with this comprehensive tutorial.',
            'meta_description' => 'A complete guide to Laravel framework covering all essential concepts.',
            'author_email' => 'john@example.com',
            'tags' => ['Laravel', 'PHP', 'Web Development', 'Tutorial', 'Framework'],
            'content' => $builderContent,
            'rich_content' => '<h2>Introduction</h2><p>Welcome to our <strong>Laravel tutorial</strong>!</p>',
        ]);

        // Verify all fields are saved correctly
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Complete Laravel Tutorial',
            'slug' => 'complete-laravel-tutorial',
            'author_email' => 'john@example.com',
        ]);

        // Verify JSON fields
        $this->assertCount(5, $blogPost->tags);
        $this->assertContains('Laravel', $blogPost->tags);
        $this->assertContains('Framework', $blogPost->tags);

        // Verify builder content structure
        $this->assertCount(6, $blogPost->content);
        
        // Test heading block
        $headingBlock = $blogPost->content[0];
        $this->assertEquals('heading', $headingBlock['type']);
        $this->assertEquals('h2', $headingBlock['data']['level']);
        $this->assertEquals('Getting Started with Laravel', $headingBlock['data']['content']);

        // Test image block
        $imageBlock = $blogPost->content[2];
        $this->assertEquals('image', $imageBlock['type']);
        $this->assertEquals('Laravel Logo', $imageBlock['data']['alt']);
        $this->assertEquals('The Laravel framework logo', $imageBlock['data']['caption']);

        // Test callout block
        $calloutBlock = $blogPost->content[5];
        $this->assertEquals('callout', $calloutBlock['type']);
        $this->assertEquals('info', $calloutBlock['data']['type']);
        $this->assertEquals('Prerequisites', $calloutBlock['data']['title']);
    }

    public function test_blog_post_field_configuration_for_text_extraction()
    {
        $blogPost = new TestBlogPost();

        // Test that all field categories are properly configured
        $translatableFields = $blogPost->getTranslatableFields();
        $this->assertContains('title', $translatableFields);
        $this->assertContains('excerpt', $translatableFields);
        $this->assertContains('meta_description', $translatableFields);

        $jsonFields = $blogPost->getTranslatableJsonFields();
        $this->assertContains('tags', $jsonFields);
        $this->assertContains('content', $jsonFields);

        $richTextFields = $blogPost->getRichTextFields();
        $this->assertContains('rich_content', $richTextFields);

        $specialFields = $blogPost->getSpecialFields();
        $this->assertEquals('slug', $specialFields['slug']);
        $this->assertEquals('author_email', $specialFields['email']);
    }

    public function test_extractable_text_from_builder_blocks()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Text Extraction Test',
            'slug' => 'text-extraction-test',
            'content' => [
                [
                    'type' => 'heading',
                    'data' => ['content' => 'Chapter 1: Introduction'],
                ],
                [
                    'type' => 'paragraph',
                    'data' => ['content' => 'This chapter introduces the main concepts.'],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'content' => 'Learning never exhausts the mind.',
                        'author' => 'Leonardo da Vinci',
                    ],
                ],
                [
                    'type' => 'callout',
                    'data' => [
                        'title' => 'Important Note',
                        'content' => 'Pay attention to this section.',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'alt' => 'Diagram showing the architecture',
                        'caption' => 'System architecture overview',
                    ],
                ],
            ],
        ]);

        // Extract all translatable texts from builder content
        $translatableTexts = [];
        foreach ($blogPost->content as $block) {
            if (isset($block['data']['content'])) {
                $translatableTexts[] = $block['data']['content'];
            }
            if (isset($block['data']['title'])) {
                $translatableTexts[] = $block['data']['title'];
            }
            if (isset($block['data']['author'])) {
                $translatableTexts[] = $block['data']['author'];
            }
            if (isset($block['data']['alt'])) {
                $translatableTexts[] = $block['data']['alt'];
            }
            if (isset($block['data']['caption'])) {
                $translatableTexts[] = $block['data']['caption'];
            }
        }

        // Verify we can extract all text content
        $this->assertContains('Chapter 1: Introduction', $translatableTexts);
        $this->assertContains('This chapter introduces the main concepts.', $translatableTexts);
        $this->assertContains('Learning never exhausts the mind.', $translatableTexts);
        $this->assertContains('Leonardo da Vinci', $translatableTexts);
        $this->assertContains('Important Note', $translatableTexts);
        $this->assertContains('Pay attention to this section.', $translatableTexts);
        $this->assertContains('Diagram showing the architecture', $translatableTexts);
        $this->assertContains('System architecture overview', $translatableTexts);
    }

    public function test_all_field_types_in_single_blog_post()
    {
        $blogPost = TestBlogPost::create([
            // Simple text fields
            'title' => 'All Field Types Test',
            'excerpt' => 'Testing all available field types',
            'meta_description' => 'A test blog post with all field types',
            
            // Special fields
            'slug' => 'all-field-types-test',
            'author_email' => 'test@example.com',
            
            // JSON array field
            'tags' => ['Test', 'All Types', 'Fields'],
            
            // Builder/JSON content field
            'content' => [
                ['type' => 'heading', 'data' => ['content' => 'Test Heading']],
                ['type' => 'paragraph', 'data' => ['content' => 'Test paragraph']],
            ],
            
            // Rich text field
            'rich_content' => '<p>Rich <strong>text</strong> content</p>',
        ]);

        // Verify all fields are populated
        $this->assertNotNull($blogPost->title);
        $this->assertNotNull($blogPost->excerpt);
        $this->assertNotNull($blogPost->meta_description);
        $this->assertNotNull($blogPost->slug);
        $this->assertNotNull($blogPost->author_email);
        $this->assertNotNull($blogPost->tags);
        $this->assertNotNull($blogPost->content);
        $this->assertNotNull($blogPost->rich_content);

        // Verify field types
        $this->assertIsString($blogPost->title);
        $this->assertIsArray($blogPost->tags);
        $this->assertIsArray($blogPost->content);
        $this->assertStringContainsString('<strong>text</strong>', $blogPost->rich_content);
    }
}