<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open')->after('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('status');
            $table->string('category')->nullable()->after('priority');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('category');
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete()->after('assigned_to');
            $table->timestamp('resolved_at')->nullable()->after('team_id');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['team_id']);
            $table->dropColumn([
                'description',
                'status',
                'priority',
                'category',
                'assigned_to',
                'team_id',
                'resolved_at',
                'closed_at',
            ]);
            $table->enum('status', ['open', 'closed', 'archived'])->default('open');
        });
    }
};
