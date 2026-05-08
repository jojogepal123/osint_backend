<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('cms_role', ['auditor', 'supervisor'])->default('auditor')->after('is_admin');
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('cms_role');
            $table->foreignId('active_case_id')->nullable()->constrained('cases')->nullOnDelete()->after('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['active_case_id']);
            $table->dropColumn(['cms_role', 'team_id', 'active_case_id']);
        });
    }
};
