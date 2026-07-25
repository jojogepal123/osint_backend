<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id')->unique();
            $table->string('query_hash', 64)->nullable()->after('query');
            $table->index(['type', 'query_hash']);
        });

        // Backfill existing rows so future lookups keep working and so the unique
        // public_id index can be created.
        \DB::table('search_queries')->whereNull('public_id')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                \DB::table('search_queries')
                    ->where('id', $row->id)
                    ->update([
                        'public_id' => \App\Services\SearchCache::generatePublicId(),
                        'query_hash' => \App\Services\SearchCache::hash($row->type ?? 'unknown', $row->query),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('search_queries', function (Blueprint $table) {
            $table->dropIndex(['type', 'query_hash']);
            $table->dropUnique(['public_id']);
            $table->dropColumn(['public_id', 'query_hash']);
        });
    }
};
