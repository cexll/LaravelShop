<?php

namespace App\Jobs;

use App\Exceptions\InvalidRequestException;
use App\Models\InstallmentItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 如果商品订单支付方式不是分期付款, 订单未支付, 订单退款状态不是退款中, 则不执行后面的逻辑
        if ($this->order->payment_method !== 'installment'
            || !$this->order->paid_at
            || $this->order->refund_status !== Order::REFUND_STATUS_PROCESSING) {
            return;
        }

        if (!$installment = Installment::query()->where('order_id', $this->order->id)->first()) {
            return;
        }

        // 遍历对应分期付款的所以还款计划
        foreach ($installment->items as $item) {
            // 如果还款计划 未支付, 或者退款状态为退款成功或退款中, 则跳过
            if (!$item->paid_at || in_array($item->refund_status, [
                    InstallmentItem::REFUND_STATUS_SUCCESS,
                    InstallmentItem::REFUND_STATUS_PROCESSING,
                ])) {
                continue;
            }

            // 调用具体的退款逻辑
            try {
                $this->refundInstallmentItem($item);
            } catch (\Exception $e) {
                Log::warning('分期退款失败: '.$e->getMessage(), [
                    'installment_item_id' => $item->id
                ]);

                // 假如某个还款计划退款报错了, 则暂时跳过, 继续处理下一个还款计划的退款
                continue;
            }
        }
        $installment->refreshRefundStatus();
    }

    protected function refundInstallmentItem(InstallmentItem $item)
    {
        // 退款单号使用商品订单的退款号与当前还款计划的序号拼接而成
        $refundNo = $this->order->refund_no.'_'.$item->sequence;
        switch ($item->payment_method) {
            case 'wechat':
                app('wechat_pay')->refund([
                    'transaction_id' => $item->payment_no,
                    'total_fee' => $item->total * 100, // 原订单金额, 单位分
                    'refund_fee' => $item->base * 100, // 退款金额
                    'out_refund_no' => $refundNo,
                    'notify_url' => route('installments.wechat.refund_notify'),
                ]);
                $item->update([
                    'refund_status' => InstallmentItem::REFUND_STATUS_PROCESSING,
                ]);
                break;
            case 'alipay':
                $ret = app('alipay')->refund([
                    'trade_no' => $item->payment_no,
                    'refund_amount' => $item->base,
                    'out_request_no' => $refundNo
                ]);
                // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
                if ($ret->sub_code) {
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
                    ]);
                } else {
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                throw new InvalidRequestException('未知订单支付方式: '.$item->payment_method);
                break;
        }
    }
}
