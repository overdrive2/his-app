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
        Schema::create('occu_ipd_staff', function (Blueprint $table) {
            $table->id();
            $table->string('occu_staff_name',100);
            $table->integer('display_order')->nullable(); 
            $table->string('report_type',100)->nullable(); 
            $table->integer('report_order')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occu_ipd_staff');
    }
};
