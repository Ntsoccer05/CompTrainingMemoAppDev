<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_states', function (Blueprint $table) {
            $table->id();
            //外部キーに紐づくテーブルはreferences->onかconstrained
            // cascadeOnDelete()は親のテーブルのレコードが削除された場合に一緒に削除
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            //外部キーの制約（null許容）はforeignIdの直後
            $table->integer('bodyWeight')->nullable();
            $table->date('recorded_at');
            $table->timestamps();                        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('record_states');
    }
};
