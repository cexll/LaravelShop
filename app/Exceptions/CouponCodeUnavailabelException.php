<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class CouponCodeUnavailabelException extends Exception
{
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 异常触发时 调用 render 方法输出
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg'=>$this->message], $this->code);
        }

        return redirect()->back()->withErrors(['coupon_code'=>$this->message]);
    }
}
