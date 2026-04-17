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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->constrained('insurance_companies')->cascadeOnDelete();
            $table->string('policy_number');
            $table->enum('status', ['ACTIVE', 'RENEWED', 'CANCELLED', 'EXPIRED'])->default('ACTIVE');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('premium', 8, 2);
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->decimal('commission_value', 8, 2)->nullable();
            $table->foreignId('renewed_from_id')->nullable()->constrained('policies')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['insurer_id', 'policy_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
