<?php

use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicsTableSeeder extends Seeder
{
    public function run()
    {
        $user_ids = \App\Models\User::all()->pluck('id')->toArray();

        $category_ids = \App\Models\Category::all()->pluck('id')->toArray();

        // 获取 faker 实例
        $faker = app(Faker\Generator::class);

        $topics = factory(Topic::class)->times(100)->make()->each(function($topic, $index) use($user_ids, $category_ids, $faker){
            // 从用户 ID 数组中随机取出一个赋值
            $topic->user_id = $faker->randomElement($user_ids);

            // 话题分类，同上
            $topic->category_id = $faker->randomElement($category_ids);
        });

        // 将数据集合转为数组，并插入到数据库中
        Topic::insert($topics->toArray());
    }

}

