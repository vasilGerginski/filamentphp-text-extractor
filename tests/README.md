# Filament Text Extractor Tests

This test suite validates the functionality of the Filament Text Extractor package using the BlogPost model as a comprehensive test case.

## Test Structure

### Unit Tests
- `BasicTraitTest.php` - Tests the ExtractsTranslatableText trait methods
  - Field getter methods (getTranslatableFields, getTranslatableJsonFields, etc.)
  - Text extraction configuration
  - shouldExtractText() functionality

### Feature Tests
- `BasicIntegrationTest.php` - Integration tests for model creation and field handling
  - Creating blog posts with all field types
  - Minimal data handling
  - Builder content structure validation
  
- `BlogPostResourceTest.php` - Comprehensive tests for the BlogPost model
  - Builder form content with all block types
  - Field configuration for text extraction
  - Extractable text identification from builder blocks
  - All field types in a single model

## Running Tests

```bash
# Run all tests
./run-tests.sh

# Run specific test suites
./run-tests.sh unit
./run-tests.sh feature
./run-tests.sh blogpost

# Run with coverage
./run-tests.sh coverage
```

## Test BlogPost Model

The tests use a comprehensive BlogPost model that includes:

### Field Types
1. **Simple Text Fields**: title, excerpt, meta_description
2. **JSON Array Fields**: tags, content (builder)
3. **Rich Text Fields**: rich_content
4. **Special Fields**: slug, author_email
5. **Long Text Fields**: content (when used as plain text)

### Builder Block Types
The content field supports these builder blocks:
- **heading**: With level and content
- **paragraph**: With content
- **image**: With url, alt text, and caption
- **quote**: With content and author
- **code**: With language and code content
- **callout**: With type, title, and content

## Key Test Scenarios

1. **Field Configuration**: Validates that the trait properly exposes all field configurations
2. **Model Creation**: Tests creating models with various field combinations
3. **Builder Content**: Comprehensive testing of the builder form structure
4. **Text Extraction**: Identifies all translatable text from different field types
5. **Edge Cases**: Handles minimal data and null fields properly

## Notes

- Auto-extraction is disabled in tests to prevent conflicts with test database schema
- Tests use an in-memory SQLite database for speed
- The test suite focuses on the trait functionality and model behavior rather than the actual extraction service