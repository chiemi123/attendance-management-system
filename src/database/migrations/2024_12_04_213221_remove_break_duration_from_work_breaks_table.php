<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBreakDurationFromWorkBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_breaks', function (Blueprint $table) {
            $table->dropColumn('break_duration'); // カラムを削除
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_breaks', function (Blueprint $table) {
            $table->integer('break_duration')->default(0); // 削除したカラムを再追加
        });
    }
}
