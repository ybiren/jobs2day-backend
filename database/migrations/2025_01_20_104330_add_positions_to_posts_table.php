<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionsToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('total_positions')->default(0)->nullable()->after('status');
            $table->unsignedInteger('remaining_positions')->default(0)->nullable()->after('total_positions');
            $table->unsignedInteger('total_application_requests')->default(0)->nullable()->after('remaining_positions');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('total_positions');
            $table->dropColumn('remaining_positions');
            $table->dropColumn('total_application_requests');
        });
    }
}
