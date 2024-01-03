<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // 20件のデータを生成します
        for ($i = 1; $i <= 20; $i++) {
            $data = [
                'user_id' => 1,
                'category_id' => 1,
                'menu_id' => 1,
                'record_state_id' =>rand(1, 10),
                // 'weight' => rand(20,100),
                // バッチ処理テスト用
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('record_menus')->insert($data);
        }
    }
}
