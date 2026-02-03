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
    Schema::create('menu_items', function (Blueprint $table) {
        $table->id(); // Ini sama dengan `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY
        $table->string('name'); // varchar(255)
        $table->text('description')->nullable(); // text, boleh kosong
        $table->decimal('price', 10, 2); // decimal(10,2)
        $table->string('image_url')->nullable(); // varchar(255), boleh kosong
        $table->string('category', 50); // varchar(50)
        $table->boolean('is_available')->default(true); // tinyint(1) default 1
        $table->boolean('is_favorite')->default(false); // tinyint(1) default 0
        $table->boolean('show_on_homepage')->default(false); // tinyint(1) default 0
        $table->timestamps(); // Ini akan membuat `created_at` dan `updated_at` secara otomatis
    });
}
};
