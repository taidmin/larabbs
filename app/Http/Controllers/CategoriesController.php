<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Link;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function show(Category $category,Request $request, Topic $topic, User $user, Link $link)
    {
        // 读取分类 ID 关联的话题，并按每页 20 条分页
        $topics = $topic->withOrder($request->order)
                        ->where('category_id',$category->id)
                        ->with('user', 'category') // 预加载防止 N+1 问题
                        ->paginate(20);

        // 获取活跃用户
        $active_users = $user->getActiveUsers();

        // 资源推荐
        $links = $link->getAllCached();

        return view('topics.index',compact('category', 'topics', 'active_users', 'links'));
    }
}
