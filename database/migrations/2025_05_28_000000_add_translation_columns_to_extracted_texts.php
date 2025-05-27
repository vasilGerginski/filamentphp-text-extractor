<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('extracted_texts', function (Blueprint $table) {
            if (!Schema::hasColumn('extracted_texts', 'original_value')) {
                $table->text('original_value')->nullable()->after('text_value');
            }
            if (!Schema::hasColumn('extracted_texts', 'translated_value')) {
                $table->text('translated_value')->nullable()->after('original_value');
            }
        });
        
        // Migrate existing data
        DB::table('extracted_texts')->whereNull('original_value')->update([
            'original_value' => DB::raw('text_value')
        ]);
    }

    public function down()
    {
        Schema::table('extracted_texts', function (Blueprint $table) {
            $table->dropColumn(['original_value', 'translated_value']);
        });
    }
};