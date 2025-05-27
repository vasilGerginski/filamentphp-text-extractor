<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Unit;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class ExtractsTranslatableTextTraitTest extends TestCase
{
    public function test_get_translatable_fields_returns_correct_fields()
    {
        $blogPost = new TestBlogPost();

        $translatableFields = $blogPost->getTranslatableFields();

        $this->assertIsArray($translatableFields);
        $this->assertContains('title', $translatableFields);
        $this->assertContains('excerpt', $translatableFields);
        $this->assertContains('meta_description', $translatableFields);
    }

    public function test_get_translatable_json_fields_returns_correct_fields()
    {
        $blogPost = new TestBlogPost();

        $jsonFields = $blogPost->getTranslatableJsonFields();

        $this->assertIsArray($jsonFields);
        $this->assertContains('tags', $jsonFields);
        $this->assertContains('content', $jsonFields);
    }

    public function test_get_long_text_fields_returns_correct_fields()
    {
        $blogPost = new TestBlogPost();

        $longTextFields = $blogPost->getLongTextFields();

        $this->assertIsArray($longTextFields);
        $this->assertContains('content', $longTextFields);
    }

    public function test_get_rich_text_fields_returns_correct_fields()
    {
        $blogPost = new TestBlogPost();

        $richTextFields = $blogPost->getRichTextFields();

        $this->assertIsArray($richTextFields);
        $this->assertContains('rich_content', $richTextFields);
    }

    public function test_get_special_fields_returns_correct_mapping()
    {
        $blogPost = new TestBlogPost();

        $specialFields = $blogPost->getSpecialFields();

        $this->assertIsArray($specialFields);
        $this->assertArrayHasKey('slug', $specialFields);
        $this->assertEquals('slug', $specialFields['slug']);
        $this->assertArrayHasKey('email', $specialFields);
        $this->assertEquals('author_email', $specialFields['email']);
    }

    public function test_get_all_translatable_fields_combines_all_field_types()
    {
        $blogPost = new TestBlogPost();

        $allFields = $blogPost->getAllTranslatableFields();

        $this->assertIsArray($allFields);
        
        // Check that it includes fields from all categories
        $this->assertContains('title', $allFields); // from translatableFields
        $this->assertContains('tags', $allFields); // from translatableJsonFields
        $this->assertContains('rich_content', $allFields); // from richTextFields
        $this->assertContains('slug', $allFields); // from specialFields (actual field name)
        $this->assertContains('author_email', $allFields); // from specialFields (mapped field)
        
        // Ensure no duplicates
        $this->assertEquals(count($allFields), count(array_unique($allFields)));
    }

    public function test_is_field_translatable_correctly_identifies_fields()
    {
        $blogPost = new TestBlogPost();

        // Translatable fields
        $this->assertTrue($blogPost->isFieldTranslatable('title'));
        $this->assertTrue($blogPost->isFieldTranslatable('excerpt'));
        $this->assertTrue($blogPost->isFieldTranslatable('tags'));
        $this->assertTrue($blogPost->isFieldTranslatable('rich_content'));
        $this->assertTrue($blogPost->isFieldTranslatable('slug'));
        $this->assertTrue($blogPost->isFieldTranslatable('author_email'));

        // Non-translatable fields
        $this->assertFalse($blogPost->isFieldTranslatable('id'));
        $this->assertFalse($blogPost->isFieldTranslatable('created_at'));
        $this->assertFalse($blogPost->isFieldTranslatable('updated_at'));
        $this->assertFalse($blogPost->isFieldTranslatable('non_existent_field'));
    }

    public function test_get_field_type_returns_correct_type()
    {
        $blogPost = new TestBlogPost();

        $this->assertEquals('text', $blogPost->getFieldType('title'));
        $this->assertEquals('text', $blogPost->getFieldType('excerpt'));
        $this->assertEquals('json', $blogPost->getFieldType('tags'));
        $this->assertEquals('builder', $blogPost->getFieldType('content')); // content is in both json and longText
        $this->assertEquals('richtext', $blogPost->getFieldType('rich_content'));
        $this->assertEquals('slug', $blogPost->getFieldType('slug'));
        $this->assertEquals('email', $blogPost->getFieldType('author_email'));
        $this->assertNull($blogPost->getFieldType('non_existent_field'));
    }

    public function test_extract_fields_for_translation_returns_field_value_pairs()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Title',
            'slug' => 'test-slug',
            'excerpt' => 'Test excerpt',
            'meta_description' => 'Test meta',
            'tags' => ['tag1', 'tag2'],
            'rich_content' => '<p>Rich content</p>',
            'author_email' => 'test@example.com',
        ]);

        $extractedFields = $blogPost->extractFieldsForTranslation();

        $this->assertIsArray($extractedFields);
        $this->assertArrayHasKey('title', $extractedFields);
        $this->assertEquals('Test Title', $extractedFields['title']);
        $this->assertArrayHasKey('slug', $extractedFields);
        $this->assertEquals('test-slug', $extractedFields['slug']);
        $this->assertArrayHasKey('tags', $extractedFields);
        $this->assertEquals(['tag1', 'tag2'], $extractedFields['tags']);
    }

    public function test_should_extract_field_respects_configuration()
    {
        $blogPost = new TestBlogPost();

        // Test with default configuration (should extract all)
        $this->assertTrue($blogPost->shouldExtractField('title'));
        $this->assertTrue($blogPost->shouldExtractField('tags'));

        // Test with configuration to exclude certain field types
        config(['filament-text-extractor.extract_field_types' => ['text', 'json']]);
        $this->assertTrue($blogPost->shouldExtractField('title')); // text field
        $this->assertTrue($blogPost->shouldExtractField('tags')); // json field
        $this->assertFalse($blogPost->shouldExtractField('rich_content')); // richtext field

        // Test with configuration to exclude specific fields
        config(['filament-text-extractor.exclude_fields' => ['title', 'tags']]);
        $this->assertFalse($blogPost->shouldExtractField('title'));
        $this->assertFalse($blogPost->shouldExtractField('tags'));
        $this->assertTrue($blogPost->shouldExtractField('excerpt'));
    }
}