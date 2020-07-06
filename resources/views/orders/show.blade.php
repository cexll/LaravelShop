@extends('layouts.app')
@section('title', '查看订单')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header" style="font-size: 14px;">
                    订单详情
                    <a class="float-right" href="{{ route('orders.index') }}">返回</a>
                </div>
                <div class="card-body">
                    @foreach($order->items as $index => $item)
                        <div class="card mb-3">
                            <div class="row no-gutters">
                                <div class="col-md-4">
                                    <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                        <img src="{{ $item->product->image_url }}" class="card-img" alt="" style="max-width: 200px; max-height: 200px;">
                                    </a>
{{--                                    <img src="{{ $item->product->image_url }}" class="card-img" alt="" style="max-width: 200px; max-height: 200px;">--}}
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                                        </h5>
                                        <p class="card-text">{{ $item->productSku->title }}</p>
                                        <p class="card-text">单价: ￥{{ $item->price }}</p>
                                        <p class="card-text">数量: {{ $item->amount }}</p>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ join(' ', $order->address) }}</h5>
                                        <p class="card-text">订单备注:
                                            {{ $order->remark ?: '-' }}</p>
                                        <p class="card-text">订单编号:
                                            {{ $order->no }}</p>
                                        <p class="card-text">物流状态:
                                            {{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card-body">
                                        <p style="font-size: 14px">
                                            @if($order->ship_data)
                                                物流信息: {{ $order->ship_data['express_company'] }} {{ $order->ship_data['express_no'] }}
                                                </div>
                                            @endif
                                            @if($order->paid_at && $order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                                                退款状态: {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                    <br>
                                                退款理由: {{ $order->extra['refund_reason'] }}
                                            @endif
                                        </p>
                                    <div class="card-body">
                                        <p style="font-size: 14px;">
                                            @if($order->couponCode)
                                                优惠信息: {{ $order->couponCode->description }}
                                            @endif
                                        </p>
                                        <h5 style="color: red">实支付:￥{{ $order->total_amount }}</h5>
                                        <p style="font-size: 14px;">订单状态:
                                            @if($order->paid_at)
                                                @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                    已支付
                                                @else
                                                    {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                @endif
                                            @elseif($order->closed)
                                                已关闭
                                            @else
                                                未支付
                                            @endif
                                        </p>
                                        <p style="font-size: 14px;">
                                            @if(isset($order->extra['refund_disagree_reason']))
                                                拒绝退款理由: {{ $order->extra['refund_disagree_reason'] }}
                                            @endif
                                        </p>
                                    </div>

                                       <div class="card-body mb-4">
                                           {{-- 支付按钮开始--}}
                                           @if(!$order->paid_at && !$order->closed)
                                               <div class="payment-buttons">
                                                   <a class="btn btn-primary btn-sm" href="{{ route('payment.alipay', ['order'=> $order->id]) }}">支付宝支付</a>
                                                   <a id="btn-wechat" class="btn btn-success btn-sm" href="{{ route('payment.wechat', ['order'=> $order->id]) }}">微信支付</a>
                                                   <!-- 分期支付按钮开始 -->
                                                   <!-- 仅当订单总金额大等于分期最低金额时才展示分期按钮 -->
                                                   @if($order->total_amount >= config('app.min_installment_amount'))
                                                       <p></p>
                                                       <button class="btn btn-sm btn-info" id="btn-installment">分期付款</button>
                                                   @endif
                                                    <!-- 分期支付按钮结束 -->
                                               </div>
                                           @endif
                                       <!-- 如果订单的发货状态为已发货则展示确认收货按钮 -->
                                           @if($order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
                                               <div class="receive-button">
                                                   <!-- 将原本的表单替换成下面这个按钮 -->
                                                   <button type="button" id="btn-receive" class="btn btn-sm btn-success">确认收货</button>
                                               </div>
                                           @endif
                                       <!-- 不是众筹订单，已支付，且退款状态是未退款时展示申请退款按钮 -->
                                           @if($order->type !== \App\Models\Order::TYPE_CROWDFUNDING && $order->paid_at && $order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                               <div class="refund-button">
                                                   <button type="button" class="btn btn-sm btn-danger" id="btn-apply-refund">申请退款</button>
                                               </div>
                                           @endif
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 分期弹框开始 -->
    <div class="modal fade" id="installment-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">选择分期期数</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>

                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th class="text-center">期数</th>
                                <th class="text-center">费率</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(config('app.installment_fee_rate') as $count => $rate)
                                <tr>
                                    <td>{{ $count }}期</td>
                                    <td>{{ $rate }}%</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-select-installment" data-count="{{ $count }}">选择</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        $(document).ready(function () {
            // 微信支付按钮时间
            $('#btn-wechat').click(function () {
                swal({
                    // content 参数可以是一个 DOM 元素，这里我们用 jQuery 动态生成一个 img 标签，并通过 [0] 的方式获取到 DOM 元素
                    content: $('<img src="{{ route('payment.wechat', ['order'=>$order->id]) }}"/>')[0],
                    // buttons 参数可以设置按钮显示的文案
                    buttons: ['关闭', '已完成支付'],
                })
                    .then(function (result) {
                        // 如果用户点击了已完成支付,则重新加载页面
                        if (result) {
                            location.reload();
                        }
                    })
            });
            // 确认收货按钮事件
            $('#btn-receive').click(function () {
                // 弹出确认框
                swal({
                    title: '确认已经收到商品?',
                    icon: 'warning',
                    dangerMode: true,
                    buttons: ['取消', '确认收到'],
                })
                    .then(function (ret) {
                        // 如果点击取消按钮则不做任何操作
                        if (!ret) {
                            return;
                        }
                        // ajax 提交确认操作
                        axios.post('{{ route('orders.received', [$order->id]) }}')
                            .then(function() {
                                // 刷新页面
                                location.reload();
                            });
                    });
            });
            // 退款按钮事件
            $('#btn-apply-refund').click(function () {
                swal({
                    text: '请输入退款理由',
                    content: 'input',
                }).then(function (input) {
                    if (!input) {
                        swal('退款理由不可空', '', 'error');
                        return;
                    }

                    axios.post('{{ route('orders.apply_refund', [$order->id]) }}', {reason: input})
                        .then(function () {
                            swal('申请退款成功', '', 'success').then(function () {
                                location.reload();
                            });
                        });
                });
            });

            // 分期付款按钮点击事件
            $('#btn-installment').click(function () {
               // 展示分期弹框
               $('#installment-modal').modal();
            });
            // 选择分期期数按钮点击事件
            $('.btn-select-installment').click(function () {
               // 调用创建分期付款接口
               axios.post('{{ route('payment.installment', ['order'=>$order->id]) }}', { count: $(this).data('count')})
                .then(function (response) {
                    location.href = '/installments/' + response.data.id;
                })
            });
        });
    </script>
@endsection
