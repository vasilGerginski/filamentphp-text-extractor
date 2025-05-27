<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Feature;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class BasicIntegrationTest extends TestCase
{
    public function test_can_create_blog_post_with_all_field_types()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'excerpt' => 'This is a test excerpt',
            'meta_description' => 'Test meta description',
            'author_email' => 'test@example.com',
            'tags' => ['Laravel', 'PHP', 'Testing'],
            'content' => [
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h2', 'content' => 'Test Heading'],
                ],
                [
                    'type' => 'paragraph',
                    'data' => ['content' => 'Test paragraph content'],
                ],
            ],
            'rich_content' => '<p>Rich text content</p>',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'excerpt' => 'This is a test excerpt',
            'meta_description' => 'Test meta description',
            'author_email' => 'test@example.com',
        ]);

        // Test that JSON fields are properly stored
        $this->assertEquals(['Laravel', 'PHP', 'Testing'], $blogPost->tags);
        $this->assertIsArray($blogPost->content);
        $this->assertEquals('heading', $blogPost->content[0]['type']);
    }

    public function test_trait_fields_configuration_is_accessible()
    {
        $blogPost = new TestBlogPost();
        
        // Test that all field configurations are accessible
        $this->assertNotEmpty($blogPost->getTranslatableFields());
        $this->assertNotEmpty($blogPost->getTranslatableJsonFields());
        $this->assertNotEmpty($blogPost->getLongTextFields());
        $this->assertNotEmpty($blogPost->getRichTextFields());
        $this->assertNotEmpty($blogPost->getSpecialFields());
    }

    public function test_model_with_minimal_data()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Minimal Post',
            'slug' => 'minimal-post',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Minimal Post',
            'slug' => 'minimal-post',
        ]);

        // Test that nullable fields are indeed null
        $this->assertNull($blogPost->excerpt);
        $this->assertNull($blogPost->meta_description);
        $this->assertNull($blogPost->rich_content);
        $this->assertNull($blogPost->author_email);
        $this->assertNull($blogPost->tags);
        $this->assertNull($blogPost->content);
    }

    public function test_builder_content_structure()
    {
        $builderContent = [
            [
                'type' => 'heading',
                'data' => [
                    'level' => 'h2',
                    'content' => 'Introduction',
                ],
            ],
            [
                'type' => 'paragraph',
                'data' => [
                    'content' => 'This is the first paragraph.',
                ],
            ],
            [
                'type' => 'image',
                'data' => [
                    'url' => '/images/test.jpg',
                    'alt' => 'Test image',
                    'caption' => 'Image caption',
                ],
            ],
            [
                'type' => 'quote',
                'data' => [
                    'content' => 'This is a quote.',
                    'author' => 'Test Author',
                ],
            ],
            [
                'type' => 'callout',
                'data' => [
                    'type' => 'info',
                    'title' => 'Information',
                    'content' => 'This is important information.',
                ],
            ],
        ];

        $blogPost = TestBlogPost::create([
            'title' => 'Builder Content Test',
            'slug' => 'builder-content-test',
            'content' => $builderContent,
        ]);

        $this->assertCount(5, $blogPost->content);
        
        // Test each block type
        $this->assertEquals('heading', $blogPost->content[0]['type']);
        $this->assertEquals('Introduction', $blogPost->content[0]['data']['content']);
        
        $this->assertEquals('paragraph', $blogPost->content[1]['type']);
        $this->assertEquals('This is the first paragraph.', $blogPost->content[1]['data']['content']);
        
        $this->assertEquals('image', $blogPost->content[2]['type']);
        $this->assertEquals('Test image', $blogPost->content[2]['data']['alt']);
        
        $this->assertEquals('quote', $blogPost->content[3]['type']);
        $this->assertEquals('Test Author', $blogPost->content[3]['data']['author']);
        
        $this->assertEquals('callout', $blogPost->content[4]['type']);
        $this->assertEquals('Information', $blogPost->content[4]['data']['title']);
    }
}