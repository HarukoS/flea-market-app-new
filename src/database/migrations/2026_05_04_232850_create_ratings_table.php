<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
        $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
        $table->unsignedTinyInteger('rating');
        $table->timestamps();
        $table->unique(['purchase_id', 'reviewer_id']);
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
