<?php

namespace VasilGerginski\FilamentTextExtractor\Commands;

use Illuminate\Console\Command;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;
use VasilGerginski\FilamentTextExtractor\Services\LangFileGenerator;

class ExtractTranslatableTextCommand extends Command
{
    protected $signature = 'text-extractor:extract {model?} {--id=} {--generate-lang-files : Generate language files after extraction}';
    
    protected $description = 'Extract translatable text from models';

    public function handle(TextExtractionService $service): int
    {
        $modelClass = $this->argument('model');
        $id = $this->option('id');

        if ($modelClass) {
            if (!class_exists($modelClass)) {
                $modelClass = 'App\\Models\\' . $modelClass;
                
                if (!class_exists($modelClass)) {
                    $this->error("Model class {$modelClass} not found!");
                    return 1;
                }
            }

            if ($id) {
                $model = $modelClass::find($id);
                if (!$model) {
                    $this->error("Model with ID {$id} not found!");
                    return 1;
                }
                
                $this->info("Extracting text from {$modelClass} ID: {$id}...");
                $service->extractFromModel($model);
                $this->info('Text extraction completed!');
            } else {
                $this->info("Extracting text from all {$modelClass} records...");
                $records = $modelClass::all();
                $bar = $this->output->createProgressBar($records->count());
                
                foreach ($records as $record) {
                    $service->extractFromModel($record);
                    $bar->advance();
                }
                
                $bar->finish();
                $this->newLine();
                $this->info('Text extraction completed!');
            }
        } else {
            $this->info('Extracting text from all models with ExtractsTranslatableText trait...');
            $service->extractAllModels();
            $this->info('Text extraction completed!');
        }

        // Generate language files if requested
        if ($this->option('generate-lang-files')) {
            $this->info('Generating language files...');
            $generator = new LangFileGenerator();
            
            if ($modelClass) {
                $generator->generateForModel($modelClass);
            } else {
                // Generate for all models
                $models = \VasilGerginski\FilamentTextExtractor\Models\ExtractedText::distinct('model_type')->pluck('model_type');
                foreach ($models as $model) {
                    $generator->generateForModel($model);
                }
            }
            
            $this->info('Language files generated successfully!');
        }

        return 0;
    }
}