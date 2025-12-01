<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('g_number')->index();
            $table->dateTime('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article')->nullable();
            $table->string('tech_size')->nullable();
            $table->unsignedBigInteger('barcode')->nullable()->index();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('discount_percent')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->string('oblast')->nullable();
            $table->unsignedBigInteger('income_id')->nullable();
            $table->unsignedBigInteger('odid')->nullable()->index();
            $table->unsignedBigInteger('nm_id')->nullable()->index();
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_cancel')->nullable();
            $table->dateTime('cancel_dt')->nullable();
            $table->text('payload');
            $table->unique(['g_number', 'odid']);
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
        Schema::dropIfExists('orders');
    }
};
