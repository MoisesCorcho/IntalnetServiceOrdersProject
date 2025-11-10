<?php

use App\Enums\EnumServiceOrderStatus;
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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Unique identifier provided for the service order');
            $table->string('title')->comment('Short summary describing the service order');
            $table->text('description')->nullable()->comment('Detailed description of the service request');
            $table->string('state')
                ->default(EnumServiceOrderStatus::CREATED->value)
                ->comment('Current status of the service order');
            $table->date('check_in_date')->comment('Date when the service order was received');
            $table->timestamp('scheduled_at')->nullable()->comment('Planned date and time for the service visit');

            $table->foreignId('assigned_user_id')
                ->nullable()
                ->comment('Identifier of the technician assigned to the service order')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->comment('Identifier of the customer linked to the service order')
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('customer_name_snapshot')->comment('Customer name captured at the time the order was created');
            $table->text('customer_address_snapshot')->nullable()->comment('Service location captured at the time the order was created');
            $table->string('customer_phone_snapshot')->nullable()->comment('Primary contact phone captured at the time the order was created');
            $table->string('customer_email_snapshot')->nullable()->comment('Contact email captured at the time the order was created');

            $table->timestamp('completed_at')->nullable()->comment('Actual completion date and time of the service order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};

