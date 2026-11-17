<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Open schedule (Jadwal 2027): participants combine any topic with any date slot and any venue.
 * Each chosen combination becomes a Training ("kelas"), so invoices/certificates keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->default('bimtek');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->longText('materials')->nullable();
            $table->text('target_participants')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->date('start_date')->unique();
            $table->date('end_date');
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('maps_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->foreignId('schedule_date_id')->nullable()->after('topic_id')->constrained()->nullOnDelete();
            $table->foreignId('venue_id')->nullable()->after('schedule_date_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_id');
            $table->dropConstrainedForeignId('schedule_date_id');
            $table->dropConstrainedForeignId('topic_id');
        });
        Schema::dropIfExists('venues');
        Schema::dropIfExists('schedule_dates');
        Schema::dropIfExists('topics');
    }
};
