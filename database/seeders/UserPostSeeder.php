<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userCount = 1000000;    // 1 миллион пользователей
        $postCount = 5000000;    // 5 миллионов постов
        $batchSize = 5000;       // вставка по 5000 записей за раз
        $now = now();

        // --- 1) Вставка пользователей ---
        for ($i = 0; $i < $userCount; $i += $batchSize) {
            $rows = [];
            $limit = min($batchSize, $userCount - $i);
            for ($j = 0; $j < $limit; $j++) {
                $rows[] = [
                    'name' => 'User_' . uniqid() . '_' . ($i + $j),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('users')->insert($rows);
            echo "Inserted users: " . ($i + $limit) . PHP_EOL;
        }

        // Получаем все user_id для постов
        $userIds = DB::table('users')->pluck('id')->toArray();
        $userTotal = count($userIds);

        // --- 2) Вставка постов ---
        for ($i = 0; $i < $postCount; $i += $batchSize) {
            $rows = [];
            $limit = min($batchSize, $postCount - $i);
            for ($j = 0; $j < $limit; $j++) {
                $rows[] = [
                    'title' => 'Post_' . uniqid() . '_' . ($i + $j),
                    'body' => 'This is the body of post ' . ($i + $j),
                    'user_id' => $userIds[($i + $j) % $userTotal],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('posts')->insert($rows);
            echo "Inserted posts: " . ($i + $limit) . PHP_EOL;
        }
    }
}
