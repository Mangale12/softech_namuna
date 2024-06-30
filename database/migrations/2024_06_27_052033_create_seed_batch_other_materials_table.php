<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeedBatchOtherMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seed_batch_other_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seed_batch_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('supplier_id');
            $table->float('unit_price', 8, 2);
            $table->float('total_cost', 8, 2);
            $table->foreign('seed_batch_id')->references('id')->on('seed_batches')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->string('name');
            // Add any additional columns for the pivot table here, such as quantity
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seed_batch_other_materials');
    }
}
