<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->boolean('recurring')->default(false)->after('note');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('recurring');
            $table->date('recurring_until')->nullable()->after('frequency');
            $table->date('last_generated_at')->nullable()->after('recurring_until');
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['recurring', 'frequency', 'recurring_until', 'last_generated_at']);
        });
    }
};
