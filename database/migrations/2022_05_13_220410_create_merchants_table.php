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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('domain')->unique();
            $table->string('display_name');
            $table->boolean('turn_customers_into_affiliates')->default(true);
            $table->decimal('default_commission_rate',5,2)->default(0.1);
            $table->timestamps();
        });

        /* Reason to Use Decimal Data Type instead of Float,
                Floating-point numbers are approximate and can represent a wide range of values but The decimal data type is used for exact decimal representations with a fixed precision and scale.
                Float stored 4 to 8 bytes but decimal stored 8 to 16 bytes.
                Decimal numbers are not subject to the rounding errors associated with floating-point numbers.
                Decimal numbers are suitable when precision is crucial, such as in financial applications, where you need precise calculations and accurate representations of decimal values.
             */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
};
