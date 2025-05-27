# Filament Text Extractor

A Laravel package for extracting and managing translatable text from Filament models with an intuitive admin interface.

## Features

- Extract translatable text from any Eloquent model
- Manage extracted texts through Filament admin panel
- Support for multiple locales
- Queue support for bulk text extraction
- Simple text extraction service
- Trait for easy model integration
- Dashboard for extraction analytics

## Installation

You can install the package via composer:

```bash
composer require your-vendor/filament-text-extractor
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-text-extractor-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="filament-text-extractor-config"
```

## Usage

### Adding Text Extraction to Your Models

Add the `ExtractsTranslatableText` trait to any model you want to extract text from:

```php
use YourVendor\FilamentTextExtractor\Traits\ExtractsTranslatableText;

class BlogPost extends Model
{
    use ExtractsTranslatableText;
    
    protected $translatableFields = [
        'title',
        'content',
        'excerpt',
    ];
}
```

### Extracting Text

You can extract text manually:

```php
$blogPost = BlogPost::find(1);
$blogPost->extractTranslatableText();
```

Or use the Artisan command for bulk extraction:

```php
php artisan text-extractor:extract "App\Models\BlogPost"
```

### Managing Extracted Texts

The package provides a Filament resource for managing extracted texts. Simply navigate to the "Extracted Texts" section in your Filament admin panel.

## Configuration

The config file allows you to customize:

- Default locale settings
- Queue configuration for bulk extraction
- Excluded fields from extraction
- Custom extraction service implementation

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.