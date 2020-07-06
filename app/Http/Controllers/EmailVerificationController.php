<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\InvalidRequestException;

class EmailVerificationController extends Controller
{

    public function verify(Request $request)
    {
        // 从 url 中获取 `Email` 和 `token`
        $email = $request->input('email');
        $token = $request->input('token');

        // 如果有一个为空说明不是合法验证链接,直接抛出异常
        if (!$email || !$token) {
            throw new InvalidRequestException('验证链接不正确');
        }

        // 从缓存中读取数据, 我们把从 url 中获取的 `token` 与缓存中的值做对比
        // 如果缓存不存在或者返回值与 url 中的 `token` 不一致就抛出异常
        if ($token != Cache::get('email_verification_'.$email)) {
            throw new InvalidRequestException('验证链接不正确或已过期');
        }

        // 根据邮箱中从数据库中获取对应用户
        // 通常来说能通过 token 校验的情况下不可能出现用户不存在
        // 但是为了代码的健壮性我们还是需要做一个判断
        if (!$user = User::where('email', $email)->first()) {
            throw new InvalidRequestException('用户不存在');
        }

        // 将指定 key 从缓存中删除, 由于已经完成了验证, 这个缓存没有必要继续保留.
        Cache::forget('email_verification_'.$email);
        // 将对应用户的 `email_verified` 字段改为 `true`
        $user->update(['email_verified'=>true]);

        // 验证成功
        return view('pages.success', ['msg'=>'验证邮箱成功']);
    }

    public function send(Request $request)
    {
        $user = $request->user();
        // 判断用户是否已经激活
        if ($user->email_verified) {
            throw new InvalidRequestException('你已经验证过邮箱了');
        }
        // 调用 notify() 方法来发送我们定义好的通知类
        $user->notify(new EmailVerificationNotification());

        return view('pages.success', ['msg'=>'邮件发送成功']);
    }
}
