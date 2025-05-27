<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Unit;

use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class BasicTraitTest extends TestCase
{
    public function test_trait_provides_field_getters()
    {
        $blogPost = new TestBlogPost();

        // Test getTranslatableFields
        $translatableFields = $blogPost->getTranslatableFields();
        $this->assertIsArray($translatableFields);
        $this->assertContains('title', $translatableFields);
        $this->assertContains('excerpt', $translatableFields);
        $this->assertContains('meta_description', $translatableFields);

        // Test getTranslatableJsonFields
        $jsonFields = $blogPost->getTranslatableJsonFields();
        $this->assertIsArray($jsonFields);
        $this->assertContains('tags', $jsonFields);
        $this->assertContains('content', $jsonFields);

        // Test getLongTextFields
        $longTextFields = $blogPost->getLongTextFields();
        $this->assertIsArray($longTextFields);
        $this->assertContains('content', $longTextFields);

        // Test getRichTextFields
        $richTextFields = $blogPost->getRichTextFields();
        $this->assertIsArray($richTextFields);
        $this->assertContains('rich_content', $richTextFields);

        // Test getSpecialFields
        $specialFields = $blogPost->getSpecialFields();
        $this->assertIsArray($specialFields);
        $this->assertArrayHasKey('slug', $specialFields);
        $this->assertEquals('slug', $specialFields['slug']);
        $this->assertArrayHasKey('email', $specialFields);
        $this->assertEquals('author_email', $specialFields['email']);
    }

    public function test_should_extract_text_returns_true_by_default()
    {
        $blogPost = new TestBlogPost();
        $this->assertTrue($blogPost->shouldExtractText());
    }

    public function test_can_disable_text_extraction()
    {
        $blogPost = new TestBlogPost();
        $blogPost->extractTranslatableText = false;
        $this->assertFalse($blogPost->shouldExtractText());
    }
}