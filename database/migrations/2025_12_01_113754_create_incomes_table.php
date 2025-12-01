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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('income_id')->index();
            $table->string('number')->nullable();
            $table->date('date')->nullable();
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article')->nullable();
            $table->string('tech_size')->nullable();
            $table->unsignedBigInteger('barcode')->nullable()->index();
            $table->integer('quantity')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->date('date_close')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->unsignedBigInteger('nm_id')->nullable()->index();
            $table->text('payload')->nullable();
            $table->unique(['income_id', 'barcode']);
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
        Schema::dropIfExists('incomes');
    }
};
