<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_transactions', 'branch_id')) {
                $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            }
            if (! Schema::hasColumn('payment_transactions', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('payment_transactions', 'refunded_amount')) {
                $table->dropColumn('refunded_amount');
            }
        });
    }
};
