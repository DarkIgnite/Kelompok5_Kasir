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
        Schema::table('tbl_pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_pengguna', 'jabatan')) {
                $table->string('jabatan', 50)->nullable()->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_pengguna', 'jabatan')) {
                $table->dropColumn('jabatan');
            }
        });
    }
};
