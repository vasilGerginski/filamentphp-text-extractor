<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extracted_texts', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('field_name')->nullable();
            $table->text('text_key');
            $table->text('text_value'); // Deprecated, kept for backward compatibility
            $table->text('original_value')->nullable();
            $table->text('translated_value')->nullable();
            $table->string('locale', 5)->default('en');
            $table->boolean('is_translated')->default(false);
            $table->timestamp('last_extracted_at')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index(['locale', 'is_translated']);
            $table->unique(['model_type', 'model_id', 'text_key', 'locale'], 'unique_extracted_text');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extracted_texts');
    }
};