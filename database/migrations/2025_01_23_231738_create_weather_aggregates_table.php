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
        Schema::create('weather_aggregates', function (Blueprint $table) {
            $table->unsignedBigInteger('town_id')->primary();
            $table->string('duration')->primary();
            $table->dateTime('start_time'); // Aggregation start time
            $table->dateTime('end_time'); // Aggregation end time
            $table->decimal('avg_temp_c', 8, 2)->nullable(); // Average temperature in Celsius
            $table->decimal('avg_temp_f', 8, 2)->nullable(); // Average temperature in Fahrenheit
            $table->decimal('avg_humidity', 8, 2)->nullable(); // Average humidity
            $table->decimal('avg_pressure_mb', 8, 2)->nullable(); // Average pressure in mb
            $table->decimal('avg_precip_mm', 8, 2)->nullable(); // Average precipitation in mm
            $table->decimal('max_temp_c', 8, 2)->nullable(); // Maximum temperature in Celsius
            $table->decimal('min_temp_c', 8, 2)->nullable(); // Minimum temperature in Celsius
            $table->decimal('max_humidity', 8, 2)->nullable(); // Maximum humidity
            $table->decimal('min_humidity', 8, 2)->nullable(); // Minimum humidity
            $table->unsignedInteger('total_records')->default(0); // Total records in the aggregation
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('town_id')->references('id')->on('towns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_aggregates');
    }
};
