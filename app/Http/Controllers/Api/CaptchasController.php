<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha_' . \Str::random(15);
        $phone = $request->phone;

        $captcha = $captchaBuilder->build();
        $expireAt = now()->addMinutes(5);
        \Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expireAt);

        $result = [
            'captcha_key' => $key,
            'expired_at' => $expireAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline(),
        ];

        return response()->json($result)->setStatusCode(201);
    }
}
