<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // Tambah kolom no hp (unik)
        $table->string('phone_number')->unique()->nullable()->after('email');
        
        // Ubah email & password jadi nullable (karena tamu mungkin gak isi email)
        $table->string('email')->nullable()->change();
        $table->string('password')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
