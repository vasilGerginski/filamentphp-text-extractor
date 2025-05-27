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
            $table->string('field_name');
            $table->string('field_type');
            $table->text('original_text');
            $table->text('translated_text')->nullable();
            $table->json('context')->nullable();
            $table->boolean('is_translated')->default(false);
            $table->timestamp('last_extracted_at')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index(['field_name', 'field_type']);
            $table->index('is_translated');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extracted_texts');
    }
};