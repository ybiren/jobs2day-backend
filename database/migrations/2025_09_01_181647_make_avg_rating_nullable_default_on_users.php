<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', fn ($t) => $t->float('avg_rating_new')->nullable()->default(0));
            DB::table('users')->update(['avg_rating_new' => DB::raw('COALESCE(avg_rating,0)')]);
            Schema::table('users', fn ($t) => $t->dropColumn('avg_rating'));
            Schema::table('users', fn ($t) => $t->renameColumn('avg_rating_new', 'avg_rating'));
        } else {
            DB::statement("ALTER TABLE users MODIFY avg_rating DOUBLE NULL DEFAULT 0");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', fn ($t) => $t->float('avg_rating_new')); // NOT NULL no default
            DB::table('users')->update(['avg_rating_new' => DB::raw('COALESCE(avg_rating,0)')]);
            Schema::table('users', fn ($t) => $t->dropColumn('avg_rating'));
            Schema::table('users', fn ($t) => $t->renameColumn('avg_rating_new', 'avg_rating'));
        } else {
            DB::statement("ALTER TABLE users MODIFY avg_rating DOUBLE NOT NULL");
        }
    }
};
