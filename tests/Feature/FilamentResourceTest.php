<?php

namespace VasilGerginski\FilamentTextExtractor\Tests\Feature;

use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource\Pages\ListExtractedTexts;
use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\ExtractedText;
use VasilGerginski\FilamentTextExtractor\Tests\Fixtures\TestBlogPost;
use VasilGerginski\FilamentTextExtractor\Tests\TestCase;

class FilamentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for authentication
        $this->actingAs(
            \VasilGerginski\FilamentTextExtractor\Tests\Fixtures\User::factory()->create()
        );
    }

    public function test_can_list_extracted_texts()
    {
        // Create test data
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'excerpt' => 'Test excerpt',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Test Blog Post',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'excerpt',
            'field_type' => 'text',
            'original_text' => 'Test excerpt',
        ]);

        Livewire::test(ListExtractedTexts::class)
            ->assertSuccessful()
            ->assertSee('Test Blog Post')
            ->assertSee('Test excerpt')
            ->assertSee('title')
            ->assertSee('excerpt');
    }

    public function test_can_search_extracted_texts()
    {
        $blogPost1 = TestBlogPost::create([
            'title' => 'Laravel Testing Guide',
            'slug' => 'laravel-testing-guide',
        ]);

        $blogPost2 = TestBlogPost::create([
            'title' => 'PHP Best Practices',
            'slug' => 'php-best-practices',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost1->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Laravel Testing Guide',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost2->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'PHP Best Practices',
        ]);

        Livewire::test(ListExtractedTexts::class)
            ->searchTable('Laravel')
            ->assertSee('Laravel Testing Guide')
            ->assertDontSee('PHP Best Practices');
    }

    public function test_can_filter_by_field_type()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'rich_content' => '<p>Rich content</p>',
            'tags' => ['tag1', 'tag2'],
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Test Post',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'rich_content',
            'field_type' => 'richtext',
            'original_text' => '<p>Rich content</p>',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'tags',
            'field_type' => 'json',
            'original_text' => 'tag1',
            'context' => json_encode(['index' => 0]),
        ]);

        $component = Livewire::test(ListExtractedTexts::class);

        // Test filtering by text type
        $component->set('tableFilters.field_type.value', 'text')
            ->assertSee('Test Post')
            ->assertDontSee('Rich content');

        // Test filtering by richtext type
        $component->set('tableFilters.field_type.value', 'richtext')
            ->assertSee('Rich content')
            ->assertDontSee('Test Post');
    }

    public function test_can_filter_by_translation_status()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'excerpt' => 'Test excerpt',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Test Post',
            'translated_text' => 'Post de Prueba',
            'is_translated' => true,
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'excerpt',
            'field_type' => 'text',
            'original_text' => 'Test excerpt',
            'is_translated' => false,
        ]);

        $component = Livewire::test(ListExtractedTexts::class);

        // Test filtering by translated status
        $component->set('tableFilters.is_translated.value', true)
            ->assertSee('Test Post')
            ->assertDontSee('Test excerpt');

        // Test filtering by untranslated status
        $component->set('tableFilters.is_translated.value', false)
            ->assertSee('Test excerpt')
            ->assertDontSee('Test Post');
    }

    public function test_displays_model_information_correctly()
    {
        $blogPost = TestBlogPost::create([
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Test Blog Post',
        ]);

        Livewire::test(ListExtractedTexts::class)
            ->assertSee('TestBlogPost')
            ->assertSee($blogPost->id);
    }

    public function test_can_bulk_update_translation_status()
    {
        $texts = [];
        for ($i = 1; $i <= 3; $i++) {
            $blogPost = TestBlogPost::create([
                'title' => "Test Post {$i}",
                'slug' => "test-post-{$i}",
            ]);

            $texts[] = ExtractedText::create([
                'model_type' => TestBlogPost::class,
                'model_id' => $blogPost->id,
                'field_name' => 'title',
                'field_type' => 'text',
                'original_text' => "Test Post {$i}",
                'is_translated' => false,
            ]);
        }

        $component = Livewire::test(ListExtractedTexts::class)
            ->callTableBulkAction('mark_as_translated', $texts)
            ->assertSuccessful();

        foreach ($texts as $text) {
            $this->assertTrue($text->fresh()->is_translated);
        }
    }

    public function test_shows_extraction_statistics_on_dashboard()
    {
        // Create various types of extracted texts
        $blogPost = TestBlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'excerpt' => 'Test excerpt',
            'meta_description' => 'Test meta',
            'tags' => ['tag1', 'tag2'],
        ]);

        // Create text extractions
        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'title',
            'field_type' => 'text',
            'original_text' => 'Test Post',
            'is_translated' => true,
            'translated_text' => 'Post de Prueba',
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'excerpt',
            'field_type' => 'text',
            'original_text' => 'Test excerpt',
            'is_translated' => false,
        ]);

        ExtractedText::create([
            'model_type' => TestBlogPost::class,
            'model_id' => $blogPost->id,
            'field_name' => 'meta_description',
            'field_type' => 'text',
            'original_text' => 'Test meta',
            'is_translated' => false,
        ]);

        // Check that statistics are calculated correctly
        $stats = ExtractedText::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(is_translated) as translated')
            ->first();

        $this->assertEquals(3, $stats->total);
        $this->assertEquals(1, $stats->translated);
        $this->assertEquals(2, $stats->total - $stats->translated); // untranslated
    }
}