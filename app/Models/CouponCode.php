<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailabelException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    // 常量定义优惠价类型
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $dates = [
        'not_before', 'not_after'
    ];

    protected $appends = ['description'];

    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00', '', $this->min_amount);
        }

        if ($this->type === self::TYPE_PERCENT) {
            return $str.'优惠'.str_replace('.00', '', $this->value).'%';
        }

        return $str.'减'.str_replace('.00', '', $this->value);
    }


    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串, 并转成大写
            $code = strtoupper(\Illuminate\Support\Str::random($length));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function checkAvailable(User $user,$orderAmount = null)
    {

        // 如果优惠券没有启用, 则等同于优惠券不存在
        if (!$this->enabled) {
            throw new CouponCodeUnavailabelException('优惠卷不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailabelException('优惠卷已被兑玩');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailabelException('优惠卷现在还不能使用');
        }

        if ($this->not_after && $this->not_aftet->lt(Carbon::now())) {
            throw new CouponCodeUnavailabelException('优惠卷已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailabelException('订单金额不满足该优惠卷最低金额');
        }

        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function ($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', Order::REFUND_STATUS_PENDING);
                });
            })
            ->exists();
        if ($used) {
            throw new CouponCodeUnavailabelException('你已经使用过这张优惠卷了');
        }
    }

    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量, 否则是减少用量
        if ($increase) {
            return $this->newQuery()->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
