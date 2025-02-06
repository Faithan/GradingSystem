<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
            $table->timestamps();
        });

        DB::table('departments')->insert([
            ['name' => 'College of Arts and Social Sciences', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'College of Business Administration', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'College of Computer Science', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'College of Criminology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'College of Education', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
