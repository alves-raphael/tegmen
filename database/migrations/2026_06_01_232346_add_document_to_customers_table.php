<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('document', 14)->nullable()->unique()->after('cpf');
            $table->enum('type', ['person', 'company'])->nullable()->after('document');
            $table->string('cpf', 14)->nullable()->change();
            $table->date('birth_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['document']);
            $table->dropColumn(['document', 'type']);
            $table->string('cpf', 14)->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
        });
    }
};
