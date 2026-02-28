<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->string('currency', 10)->default('IDR')->after('amount');
            // Stored rate: how many IDR equals 1 unit of `currency`. Always ≥ 1 for IDR.
            $table->decimal('exchange_rate', 15, 6)->default(1)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });
    }
};
