<?php

namespace VasilGerginski\FilamentTextExtractor\Commands;

use Illuminate\Console\Command;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;

class ExtractTranslatableTextCommand extends Command
{
    protected $signature = 'text:extract {model?} {--id=}';
    
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

        return 0;
    }
}