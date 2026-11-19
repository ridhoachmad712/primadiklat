<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Room for a main keyword plus comma-separated supporting keywords.
    public function up(): void
    {
        Schema::table('posts', fn (Blueprint $table) => $table->string('focus_keyword', 255)->nullable()->change());
    }

    public function down(): void
    {
        Schema::table('posts', fn (Blueprint $table) => $table->string('focus_keyword', 100)->nullable()->change());
    }
};
