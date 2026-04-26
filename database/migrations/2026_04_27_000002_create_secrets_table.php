<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secrets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // password, note, command, ssh_key, database, email, api_key, pem_file, custom
            $table->longText('encrypted_data'); // JSON encrypted blob with all key-value fields
            $table->text('tags')->nullable(); // comma-separated tags for searching
            $table->string('url')->nullable(); // associated URL/hostname
            $table->boolean('is_favorite')->default(false);
            $table->foreignUuid('category_id')->nullable()->constrained('secret_categories')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secrets');
    }
};
