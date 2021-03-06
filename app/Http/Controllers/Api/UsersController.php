<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Http\Requests\Request;
use App\Http\Resources\UserResource;
use App\Models\Image;
use App\Models\User;
use EasyWeChatComposer\EasyWeChat;
use Illuminate\Auth\AuthenticationException;

class UsersController extends Controller
{
    public function activedIndex(User $user)
    {
        UserResource::wrap('data');

        return UserResource::collection($user->getActiveUsers());
    }

    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);

        if(!$verifyData){
            abort(403, '验证码已失效');
        }

        if(!hash_equals($verifyData['code'],$request->verification_code)){
            // 返回 401
            throw new AuthenticationException('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => $request->password,
        ]);

        // 注册登录成功后，清除验证码缓存
        \Cache::forget($request->verification_key);

        return (new UserResource($user))->showSensitiveFields();
    }

    public function show(User $user, Request $request)
    {
        return new UserResource($user);
    }

    public function me(Request $request)
    {
        return (new UserResource($request->user()))->showSensitiveFields();
    }

    public function update(UserRequest $request)
    {
        $user = $request->user();
        $attributes = $request->only(['name', 'email', 'introduction', 'registration_id']);

        if($request->avatar_image_id){
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }
        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }

    public function weappStore(UserRequest $request)
    {
        // 缓存中是否存在对应的key
        $verifyData = \Cache::get($request->verification_key);

        if(!$verifyData){
            abort(403, '验证码已失效');
        }

        // 判断验证码是否相等，不相等返回401错误
        if(!hash_equals((string)$verifyData['code'], $request->verification_code)){
            throw new AuthenticationException('验证码错误');
        }

        // 获取微信的openid和session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($request->code);

        if(isset($data['errcode'])){
            throw new AuthenticationException('code 不正确');
        }

        // 如果openid对应的用户已存在，报错403
        $user = User::where('weapp_openid',$data['openid'])->first();
        if($user){
            throw new AuthenticationException('微信已绑定其他用户，请直接登录');
        }

        // 创建用户
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => $request->password,
            'weapp_openid' => $data['openid'],
            'weixin_session_key' => $data['session_key'],
        ]);

        return (new UserResource($user))->showSensitiveFields();

    }
}
