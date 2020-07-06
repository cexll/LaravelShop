@extends('layouts.app')

@section('title', '收货地址列表')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="card">
                <div class="card-header">
                    收货地址列表
                    <a href="{{ route('user_addresses.create') }}" class="float-right">新增收货地址</a>
                </div>
                <div class="card-body">
                    @foreach($addresses as $address)
                    <div class="card" style="padding: 0 10px; margin-top: 10px;">

                        <div class="card-body">
                            <h5 class="card-title">{{ $address->contact_name }}</h5>
                            <div class="card-text" >{{ $address->contact_phone }}</div>
                            <div class="card-text">{{ $address->zip }}</div>
                            <div class="card-title">{{ $address->full_address }}</div>
                            <div class="card-group" style="float: right">
                                <a href="{{ route('user_addresses.edit', ['user_address'=>$address->id]) }}" class="btn btn-primary">修改</a>
                                <button style="margin-left: 10px" class="btn btn-danger btn-del-address" type="button" data-id="{{ $address->id }}">删除</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scriptsAfterJs')
    <script>
        $(document).ready(function() {
            $('.btn-del-address').click(function() {
                // 获取按钮 data-id 值
                var id = $(this).data('id');

                // 调用sweetalert
                swal({
                    title: "确认要删除该地址?",
                    icon: "warning",
                    buttons: ['取消', '确定'],
                    dangerMode: true,
                })
                    .then(function(willDelete) {
                        // 用户点击确定 willDelete 值为 true， 否则为 false
        // 用户点了取消，啥也不做
                        if (!willDelete) {
                            return;
                        }
                        // 调用删除接口，用 id 来拼接出请求的 url
                        axios.delete('/user_addresses/' + id)
                            .then(function() {
                                // 请求成功之后重新加载页面
                                location.reload();
                            })
                    });
            });
        });
    </script>
@endsection
