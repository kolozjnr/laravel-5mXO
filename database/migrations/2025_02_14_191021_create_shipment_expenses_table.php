<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipment_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->string('expense_type')->nullable();
            $table->string('units')->nullable();
            $table->string('rate')->nullable();
            $table->string('amount')->nullable();
            $table->string('credit_reimbursement_amount')->nullable();
            $table->string('vendor_invoice_name')->nullable();
            $table->string('vendor_invoice_number')->nullable();
            $table->text('payment_reference_note')->nullable();
            $table->text('disputed_note')->nullable();
            $table->string('disputed_amount')->nullable();
            $table->string('internal_notes')->nullable();
            $table->boolean('billed')->default(false)->comment('IF THE CHARGE HAS BEEN BILLED');
            $table->boolean('paid')->default(false)->comment('IF THE CHARGE HAS BEEN PAID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_expenses');
    }
};
