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
        // 1. Agar tamu bisa masuk tanpa email
        $table->string('email')->nullable()->change(); 
        
        // 2. Agar tamu bisa masuk tanpa password dulu
        $table->string('password')->nullable()->change(); 
        
        // 3. Tambah kolom no hp (jika belum ada)
        if (!Schema::hasColumn('users', 'phone_number')) {
            $table->string('phone_number')->nullable()->after('name');
        }
    });
}

public function down()
{
    // ...
}


};
