<?php

namespace VasilGerginski\FilamentTextExtractor\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use VasilGerginski\FilamentTextExtractor\Models\ExtractedText;
use VasilGerginski\FilamentTextExtractor\Filament\Resources\ExtractedTextResource\Pages;

class ExtractedTextResource extends Resource
{
    protected static ?string $model = ExtractedText::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-language';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?string $label = 'Extracted Text';
    
    protected static ?string $pluralLabel = 'Text Extraction';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Original Text')
                    ->schema([
                        Forms\Components\TextInput::make('text_key')
                            ->label('Original Text (English)')
                            ->disabled()
                            ->columnSpanFull(),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('model_type')
                                    ->label('Model Type')
                                    ->disabled()
                                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                                    
                                Forms\Components\TextInput::make('field_name')
                                    ->label('Field Name')
                                    ->disabled(),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Tabs::make('translations')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('bg')
                                    ->label('🇧🇬 Bulgarian')
                                    ->schema([
                                        Forms\Components\Hidden::make('translations.bg.locale')
                                            ->default('bg'),
                                        Forms\Components\Textarea::make('translations.bg.text_value')
                                            ->label('Bulgarian Translation')
                                            ->rows(3)
                                            ->placeholder('Enter Bulgarian translation...')
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('translations.bg.is_translated')
                                            ->label('Translation Complete')
                                            ->default(false),
                                    ]),
                                    
                                Forms\Components\Tabs\Tab::make('es')
                                    ->label('🇪🇸 Spanish')
                                    ->schema([
                                        Forms\Components\Hidden::make('translations.es.locale')
                                            ->default('es'),
                                        Forms\Components\Textarea::make('translations.es.text_value')
                                            ->label('Spanish Translation')
                                            ->rows(3)
                                            ->placeholder('Enter Spanish translation...')
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('translations.es.is_translated')
                                            ->label('Translation Complete')
                                            ->default(false),
                                    ]),
                                    
                                Forms\Components\Tabs\Tab::make('fr')
                                    ->label('🇫🇷 French')
                                    ->schema([
                                        Forms\Components\Hidden::make('translations.fr.locale')
                                            ->default('fr'),
                                        Forms\Components\Textarea::make('translations.fr.text_value')
                                            ->label('French Translation')
                                            ->rows(3)
                                            ->placeholder('Enter French translation...')
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('translations.fr.is_translated')
                                            ->label('Translation Complete')
                                            ->default(false),
                                    ]),
                                    
                                Forms\Components\Tabs\Tab::make('de')
                                    ->label('🇩🇪 German')
                                    ->schema([
                                        Forms\Components\Hidden::make('translations.de.locale')
                                            ->default('de'),
                                        Forms\Components\Textarea::make('translations.de.text_value')
                                            ->label('German Translation')
                                            ->rows(3)
                                            ->placeholder('Enter German translation...')
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('translations.de.is_translated')
                                            ->label('Translation Complete')
                                            ->default(false),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('text_key')
                    ->label('Original Text')
                    ->limit(50)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('translation_status')
                    ->label('Translation Status')
                    ->getStateUsing(function ($record) {
                        $locales = ['bg' => '🇧🇬', 'es' => '🇪🇸', 'fr' => '🇫🇷', 'de' => '🇩🇪'];
                        $translations = ExtractedText::where('model_type', $record->model_type)
                            ->where('model_id', $record->model_id)
                            ->where('text_key', $record->text_key)
                            ->whereIn('locale', array_keys($locales))
                            ->where('is_translated', true)
                            ->pluck('locale')
                            ->toArray();
                        
                        $flags = [];
                        foreach ($locales as $locale => $flag) {
                            if (in_array($locale, $translations)) {
                                $flags[] = $flag;
                            }
                        }
                        
                        return implode(' ', $flags) ?: '⚠️ No translations';
                    })
                    ->html(),
                    
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('field_name')
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('last_extracted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label('Translation Status')
                    ->options([
                        'all_translated' => '✅ Fully Translated',
                        'partially_translated' => '⚠️ Partially Translated',
                        'not_translated' => '❌ Not Translated',
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }
                        
                        return $query->where('locale', 'en')->where(function ($q) use ($data) {
                            if ($data['value'] === 'all_translated') {
                                $q->whereRaw('(
                                    SELECT COUNT(*) FROM extracted_texts AS et 
                                    WHERE et.model_type = extracted_texts.model_type 
                                    AND et.model_id = extracted_texts.model_id 
                                    AND et.text_key = extracted_texts.text_key 
                                    AND et.locale IN ("bg", "es", "fr", "de") 
                                    AND et.is_translated = 1
                                ) = 4');
                            } elseif ($data['value'] === 'partially_translated') {
                                $q->whereRaw('(
                                    SELECT COUNT(*) FROM extracted_texts AS et 
                                    WHERE et.model_type = extracted_texts.model_type 
                                    AND et.model_id = extracted_texts.model_id 
                                    AND et.text_key = extracted_texts.text_key 
                                    AND et.locale IN ("bg", "es", "fr", "de") 
                                    AND et.is_translated = 1
                                ) BETWEEN 1 AND 3');
                            } else {
                                $q->whereRaw('(
                                    SELECT COUNT(*) FROM extracted_texts AS et 
                                    WHERE et.model_type = extracted_texts.model_type 
                                    AND et.model_id = extracted_texts.model_id 
                                    AND et.text_key = extracted_texts.text_key 
                                    AND et.locale IN ("bg", "es", "fr", "de") 
                                    AND et.is_translated = 1
                                ) = 0');
                            }
                        });
                    }),
                    
                Tables\Filters\SelectFilter::make('model_type')
                    ->options(function () {
                        return ExtractedText::distinct('model_type')
                            ->pluck('model_type')
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                            ->toArray();
                    }),
                    
                Tables\Filters\SelectFilter::make('field_name')
                    ->options(function () {
                        return ExtractedText::distinct('field_name')
                            ->whereNotNull('field_name')
                            ->pluck('field_name', 'field_name')
                            ->toArray();
                    }),
            ])
            ->defaultSort('last_extracted_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->where('locale', 'en'))
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_translate')
                        ->label('Bulk Add Translations')
                        ->icon('heroicon-o-language')
                        ->form([
                            Forms\Components\Select::make('locale')
                                ->label('Target Language')
                                ->options([
                                    'bg' => '🇧🇬 Bulgarian',
                                    'es' => '🇪🇸 Spanish',
                                    'fr' => '🇫🇷 French',
                                    'de' => '🇩🇪 German',
                                ])
                                ->required(),
                                
                            Forms\Components\Repeater::make('translations')
                                ->schema([
                                    Forms\Components\TextInput::make('text_key')
                                        ->label('Original Text')
                                        ->disabled()
                                        ->dehydrated(false),
                                        
                                    Forms\Components\Textarea::make('translation')
                                        ->label('Translation')
                                        ->rows(2)
                                        ->required(),
                                ])
                                ->disableItemCreation()
                                ->disableItemDeletion()
                                ->disableItemMovement()
                                ->default(function ($records) {
                                    return $records->map(fn ($record) => [
                                        'text_key' => $record->text_key,
                                        'translation' => '',
                                    ])->toArray();
                                }),
                        ])
                        ->action(function ($records, array $data) {
                            $locale = $data['locale'];
                            
                            foreach ($data['translations'] as $index => $translation) {
                                $record = $records->skip($index)->first();
                                if ($record && !empty($translation['translation'])) {
                                    ExtractedText::updateOrCreate([
                                        'model_type' => $record->model_type,
                                        'model_id' => $record->model_id,
                                        'text_key' => $record->text_key,
                                        'locale' => $locale,
                                    ], [
                                        'text_value' => $translation['translation'],
                                        'field_name' => $record->field_name,
                                        'is_translated' => true,
                                        'last_extracted_at' => now(),
                                    ]);
                                }
                            }
                            
                            Notification::make()
                                ->title('Success')
                                ->body('Translations added successfully.')
                                ->success()
                                ->send();
                        }),
                        
                    Tables\Actions\BulkAction::make('export_to_lang')
                        ->label('Export to Lang Files')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            app(\VasilGerginski\FilamentTextExtractor\Services\TextExtractionService::class)
                                ->exportToLangFiles($records);
                            
                            Notification::make()
                                ->title('Success')
                                ->body('Texts exported to language files.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExtractedTexts::route('/'),
            'create' => Pages\CreateExtractedText::route('/create'),
            'edit' => Pages\EditExtractedText::route('/{record}/edit'),
            'dashboard' => Pages\TextExtractionDashboard::route('/dashboard'),
        ];
    }
}