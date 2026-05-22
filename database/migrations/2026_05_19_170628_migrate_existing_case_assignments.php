<?php

use App\Models\CaseModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        CaseModel::whereNotNull('assigned_to')
            ->where('assigned_to', '>', 0)
            ->chunkById(100, function ($cases) {
                foreach ($cases as $case) {
                    $exists = DB::table('case_assignments')
                        ->where('case_id', $case->id)
                        ->where('user_id', $case->assigned_to)
                        ->exists();

                    if (! $exists) {
                        DB::table('case_assignments')->insert([
                            'case_id' => $case->id,
                            'user_id' => $case->assigned_to,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        DB::table('case_assignments')->truncate();
    }
};