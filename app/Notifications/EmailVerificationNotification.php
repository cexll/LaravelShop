<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Cache;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // 使用 Laravel 内置 Str 类生成随机字符串的函数,参数就是要生产的字符串长度
        $token = \Illuminate\Support\Str::random(16);
        // 往缓存中写入这个随机字符串, 有效期为30分钟
        \Illuminate\Support\Facades\Cache::put('email_verification_'.$notifiable->email, $token, 30);
        $url = route('email_verification.verify', ['email'=>$notifiable->email, 'token'=>$token]);
        return (new MailMessage)
                    ->greeting($notifiable->name. '你好: ')
                    ->subject('注册成功, 请验证你的邮箱')
                    ->line('请点击下方链接验证你的邮箱')
                    ->action('验证E-mail', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
