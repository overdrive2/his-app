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
        Schema::create('ipds', function (Blueprint $table) {
            $table->id();
            $table->string('an',9);
            $table->string('vn',12);
            $table->integer('patient_id');
            $table->integer('adm_officer_id')->nullable();
            $table->date('regdate');
            $table->time('regtime');
            $table->integer('spclty_id')->nullable();
            $table->integer('firstward_id')->nullable();
            $table->integer('pttype_id')->nullable();
            $table->integer('ipd_severe_id')->nullable();
            $table->integer('ipd_admit_type_id')->nullable();
            $table->text('admit_for',150)->nullable();
            $table->jsonb('drainages',150)->nullable();
            $table->boolean('line_noty')->nullable();
            $table->boolean('is_screen_asses')->nullable();
            $table->string('is_vs_new',2)->nullable();
            $table->boolean('is_do_med')->nullable();
            $table->string('is_nn_new',2)->nullable();
            $table->integer('reasonadmit_type_id')->nullable();
            $table->integer('o2_type_id')->nullable();
            $table->integer('occu_type_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('current_bedmove_id');
            $table->integer('prediag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipds');
    }
};
