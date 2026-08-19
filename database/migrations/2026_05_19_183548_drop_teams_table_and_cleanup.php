<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotNull('team_id')->update(['team_id' => null]);
        DB::table('cases')->whereNotNull('team_id')->update(['team_id' => null]);
        // users.team_id carries a real FK to teams; drop it first so the table
        // can go on drivers that keep enforcing the dangling reference.
        if (Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['team_id']);
            });
        }

        // FOREIGN_KEY_CHECKS is MySQL-only; skip it on other drivers (the test
        // suite runs on sqlite) so the migration stays portable.
        $isMysql = DB::getDriverName() === 'mysql';

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        Schema::dropIfExists('teams');

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void {}
};
