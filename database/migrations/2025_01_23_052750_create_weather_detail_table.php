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
        Schema::create('weather_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('town_id')->constrained('towns');
            $table->string('city_name');
            $table->string('region');
            $table->string('country');
            $table->decimal('lat', 8, 5);
            $table->decimal('lon', 8, 5);
            $table->string('tz_id');
            $table->timestamp('localtime')->nullable();
            $table->decimal('temp_c', 5, 2);
            $table->decimal('temp_f', 5, 2);
            $table->boolean('is_day');
            $table->jsonb('condition');
            $table->decimal('wind_mph', 5, 2);
            $table->decimal('wind_kph', 5, 2);
            $table->integer('wind_degree');
            $table->string('wind_dir');
            $table->decimal('pressure_mb', 7, 2);
            $table->decimal('pressure_in', 5, 2);
            $table->decimal('precip_mm', 5, 2);
            $table->decimal('precip_in', 5, 2);
            $table->decimal('humidity', 5, 2);
            $table->integer('cloud');
            $table->decimal('feelslike_c', 5, 2);
            $table->decimal('feelslike_f', 5, 2);
            $table->decimal('windchill_c', 5, 2);
            $table->decimal('windchill_f', 5, 2);
            $table->decimal('heatindex_c', 5, 2);
            $table->decimal('heatindex_f', 5, 2);
            $table->decimal('dewpoint_c', 5, 2);
            $table->decimal('dewpoint_f', 5, 2);
            $table->decimal('vis_km', 5, 2);
            $table->decimal('vis_miles', 5, 2);
            $table->integer('uv');
            $table->decimal('gust_mph', 5, 2);
            $table->decimal('gust_kph', 5, 2);
            $table->timestamp('recorded_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
