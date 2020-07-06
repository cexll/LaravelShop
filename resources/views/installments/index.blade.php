@extends('layouts.app')
@section('title', '分期付款列表')

@section('content')

    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="card">
                <div class="card-header">分期付款列表</div>
                <div class="">
                    <table class="table">
                        <thead class="thead-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">编号</th>
                            <th scope="col">金额</th>
                            <th scope="col">期数</th>
                            <th scope="col">费率</th>
                            <th scope="col">状态</th>
                            <th scope="col">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($installments as $installment)
                            <tr>
                                <th scope="row"></th>
                                <td>{{ $installment->no }}</td>
                                <td>{{ $installment->total_amount }}</td>
                                <td>{{ $installment->count }}</td>
                                <td>{{ $installment->fee_rate }}%</td>
                                <td>{{ \App\Models\Installment::$statusMap[$installment->status] }}</td>
                                <td><a class="btn btn-primary btn-xs" href="{{ route('installments.show', ['installment'=>$installment->id]) }}">查看</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pull-right">{{ $installments->render() }}</div>
            </div>
        </div>
    </div>
@endsection
