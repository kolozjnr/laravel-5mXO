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
        Schema::create('pickup_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->string('task')->nullable();
            $table->string('quick_lockup')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->date('appointment_date')->nullable();
            $table->time('primary_time')->nullable();
            $table->time('secondary_time')->nullable();
            $table->date('appointment_date_deadline')->nullable();
            $table->time('primary_time_deadline')->nullable();
            $table->date('earliest_date')->nullable();
            $table->time('earliest_time')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->text('flash_notes')->nullable();
            $table->text('one_time_notes')->nullable()->comment('DRIVERS WILL NOT SEE THIS NOTE');
            $table->text('permanent_notes_about_the_location')->nullable()->comment('DRIVERS WILL NOT SEE THIS NOTE');
            $table->text('notes_to_drivers')->nullable()->comment('DRIVERS NOTE SUCH AS INSTRUCTIONS');
            $table->text('permanent_notes_to_drivers')->nullable()->comment('PERMANENT NOTES TO DRIVERS SUCH AS DRIVING DIRECTION');
            $table->string('equipment_moved')->nullable()->comment('OPTIONAL');
            $table->boolean('stop_off')->nullable()->default(true)->comment('DEFAULT IS TRUE, ITS A STOP OFF');
            $table->string('Reference_po_appt_pickup_number')->nullable()->comment('PO APPT PICKUP NUMBER');
            $table->date('completed_date')->nullable();
            $table->date('check_in_date')->nullable();
            $table->time('check_in_time')->nullable();
            $table->date('check_out_date')->nullable();
            $table->time('check_out_time')->nullable();
            $table->string('container')->nullable();
            $table->string('chasis')->nullable();
            $table->string('trip_stops')->nullable();
            $table->string('payment')->nullable();
            $table->text('notes_equipment')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_locations');
    }
};
