<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Universal user preferences table (key-value JSON).
     * Namespace pattern: {namespace}.{entity}.{sub_entity}
     * Examples:
     *   - format.rep._default          -> global number format
     *   - format.col.020101.penerimaan -> specific column format
     *   - ui.theme                     -> theme config
     *   - table.020101.pageSize        -> table preferences
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('namespace', 50);
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'namespace', 'key']);
            $table->index(['user_id', 'namespace']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
