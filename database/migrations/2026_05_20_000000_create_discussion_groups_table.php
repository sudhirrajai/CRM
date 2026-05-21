<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create discussion_groups table
        Schema::create('discussion_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Create discussion_group_members table
        Schema::create('discussion_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('group_id')->constrained('discussion_groups')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        // 3. Modify project_discussions table
        Schema::table('project_discussions', function (Blueprint $table) {
            $table->uuid('project_id')->nullable()->change();
            $table->foreignUuid('group_id')->nullable()->after('project_id')->constrained('discussion_groups')->onDelete('cascade');
        });

        // 4. Modify discussion_reads table
        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->uuid('project_id')->nullable()->change();
            $table->foreignUuid('group_id')->nullable()->after('project_id')->constrained('discussion_groups')->onDelete('cascade');
            $table->unique(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->dropUnique(['discussion_reads_group_id_user_id_unique']);
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->uuid('project_id')->nullable(false)->change();
        });

        Schema::table('project_discussions', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->uuid('project_id')->nullable(false)->change();
        });

        Schema::dropIfExists('discussion_group_members');
        Schema::dropIfExists('discussion_groups');
    }
};
