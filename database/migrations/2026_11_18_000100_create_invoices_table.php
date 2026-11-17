<?php

use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('institution');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone', 30);
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->string('payment_proof')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('receipt_number')->nullable()->unique();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('training_id')->constrained()->nullOnDelete();
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_number');
        });

        // Every existing registration gets its own invoice so old data keeps working.
        Invoice::createForOrphans();
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn('certificate_issued_at');
        });
        Schema::dropIfExists('invoices');
    }
};
