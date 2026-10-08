<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delivery Drivers / Couriers
        Schema::create('delivery_drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 50);
            $table->string('vehicle_type', 50)->default('car'); // car, motorcycle, van, bicycle
            $table->string('license_plate', 50)->nullable();
            $table->string('status', 30)->default('available'); // available, on_delivery, offline
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index(['tenant_id', 'status'], 'idx_delivery_drivers_status');
        });

        // 2. Delivery Shipments
        Schema::create('delivery_shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('delivery_driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->string('shipment_number', 50);
            $table->string('status', 30)->default('pending'); // pending, assigned, picked_up, in_transit, delivered, failed
            $table->text('delivery_address');
            $table->string('recipient_name', 150)->nullable();
            $table->string('recipient_phone', 50)->nullable();
            $table->timestampTz('scheduled_slot_start')->nullable();
            $table->timestampTz('scheduled_slot_end')->nullable();
            $table->decimal('cod_amount', 12, 2)->default(0.00);
            $table->decimal('cod_collected', 12, 2)->default(0.00);
            $table->timestampTz('dispatched_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['tenant_id', 'shipment_number'], 'uq_delivery_shipments_number');
            $table->index(['tenant_id', 'status'], 'idx_delivery_shipments_status');
            $table->index(['tenant_id', 'order_id'], 'idx_delivery_shipments_order');
        });

        // 3. Digital Proof of Delivery (POD)
        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('delivery_shipment_id')->constrained('delivery_shipments')->cascadeOnDelete();
            $table->string('received_by_name', 150);
            $table->string('signature_url', 500)->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestampTz('delivered_at')->useCurrent();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['tenant_id', 'delivery_shipment_id'], 'idx_delivery_proofs_shipment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('delivery_shipments');
        Schema::dropIfExists('delivery_drivers');
    }
};
