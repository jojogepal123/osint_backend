<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cases')) {
            Schema::create('cases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('case_number')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->string('category')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('team_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open')->after('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->after('status');
            $table->string('category')->nullable()->after('priority');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('category');
            $table->unsignedBigInteger('team_id')->nullable()->after('assigned_to');
            $table->timestamp('resolved_at')->nullable()->after('team_id');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
