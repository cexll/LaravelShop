@extends('layouts.app')
@section('title', '订单列表')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">订单列表</div>
                <div class="card-body">

                    @foreach($orders as $order)
                        @foreach($order->items as $index => $item)
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="float-left">
                                <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                            </div>
                            <div class="float-right">
                                @if($index === 0)
                                    @if($order->paid_at)
                                       @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                            已支付
                                        @else
                                            {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                        @endif
                                    @elseif($order->closed)
                                        已关闭
                                   @else
                                       未支付 ( 请于 {{ $order->created_at->addSeconds(config('app.order_ttl'))->format('H:i') }} 前完成支付,否则订单将自动关闭 )
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                    <img src="{{ $item->product->image_url }}" class="card-img" style="max-width: 150px; max-height: 150px;" alt="">
                               </a>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body" style="font-size: 14px">
                                    <div class="card-title">12GB+256GB 钛银黑</div>
                                    <div class="card-text">单价: ￥{{ $item->price }}</div>
                                    <div class="card-text">数量: {{ $item->amount }}</div>
                                    <div class="card-footer bg-transparent border-success" style="color: red; width: 100%;text-align: right;font-size: 16px;">实支付: ￥{{ $order->total_amount }}</div>
                                </div>

                            </div>
                        </div>
                        <div class="col" style="margin-bottom: 10px;">
                            <a class="btn btn-primary btn-sm" href="{{ route('orders.show', ['order'=>$order->id]) }}">查看订单</a>
                            @if($index === 0)
                                @if($order->paid_at)
                                    <a class="btn btn-success btn-sm" href="{{ route('orders.review.show', ['order' => $order->id]) }}">
                                        {{ $order->reviewed ? '查看评价' : '评价' }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                        @endforeach
                    @endforeach

                    <div class="float-right">{{ $orders->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
