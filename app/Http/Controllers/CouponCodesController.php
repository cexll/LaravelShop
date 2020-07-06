<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponCodeUnavailabelException;
use App\Models\CouponCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponCodesController extends Controller
{
    public function show($code, Request $request)
    {
        // 判断优惠券是否存在
        if (!$record = CouponCode::where('code', $code)->first()) {
            throw new CouponCodeUnavailabelException('优惠卷不存在');
        }

        $record->checkAvailable($request->user());

        return $record;
    }
}
