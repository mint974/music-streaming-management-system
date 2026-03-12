<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1NF: Replace JSON `artist_packages.features` column with a proper relational table.
 *
 * Before: artist_packages.features JSON ["Feature A", "Feature B"]
 * After : artist_package_features(id, package_id, feature, sort_order)
 *
 * BCNF: PK `id` is the sole determinant; {package_id, sort_order} can also serve
 *       as a natural ordering key without creating a non-superkey FD issue.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create artist_package_features table ──────────────────────────
        Schema::create('artist_package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')
                  ->constrained('artist_packages')
                  ->cascadeOnDelete();
            $table->string('feature', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->index(['package_id', 'sort_order']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('artist_package_features');
    }
};
