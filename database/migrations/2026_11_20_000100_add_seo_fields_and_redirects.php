<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('thumbnail');
            $table->boolean('noindex')->default(false)->after('meta_description');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('thumbnail');
            $table->boolean('noindex')->default(false)->after('meta_description');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('is_published');
            $table->boolean('noindex')->default(false)->after('meta_description');
        });

        // Old URL -> new URL, recorded automatically when a public slug changes.
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn(['meta_title', 'noindex']));
        Schema::table('trainings', fn (Blueprint $table) => $table->dropColumn(['image_alt', 'noindex']));
        Schema::table('posts', fn (Blueprint $table) => $table->dropColumn(['image_alt', 'noindex']));
    }
};
