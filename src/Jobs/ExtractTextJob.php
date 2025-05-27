<?php

namespace VasilGerginski\FilamentTextExtractor\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VasilGerginski\FilamentTextExtractor\Services\TextExtractionService;

class ExtractTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Model $model
    ) {}

    public function handle(TextExtractionService $service): void
    {
        $service->extractFromModel($this->model);
    }
}