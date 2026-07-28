<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cases', 'public_id')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->uuid('public_id')->nullable()->after('id')->unique();
            });

            \DB::table('cases')->whereNull('public_id')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    \DB::table('cases')
                        ->where('id', $row->id)
                        ->update(['public_id' => (string) \Illuminate\Support\Str::uuid()]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
