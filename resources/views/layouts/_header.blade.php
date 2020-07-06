<nav class="navbar navbar-expand-lg navbar-light bg-light navbar-static-top">
    <div class="container">
        <!-- Branding Image -->
        <div>
            <div class="navbar-brand">
                    @if(substr(\Illuminate\Support\Facades\URL::full(), -8) !== 'products')
                        <a class="navbar-brand" href="#" onclick="javascript:history.back(-1);">
                            <img src="https://img.icons8.com/flat_round/64/000000/back--v1.png" style="max-width: 30px;max-height: 30px;">
                        </a>

                    @endif
                    <a class="navbar-brand" href="{{ url('/') }}">
                        Laravel Shop
                    </a>
            </div>

        </div>


        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                @if(isset($categoryTree))
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button"  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">所有类目</button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        @each('layouts._category_item', $categoryTree, 'category')
                    </div>
                </div>
                @endif

            </ul>
            <ul class="navbar-nav navbar-right">
                @guest
                    <li class="nav-item"><a class="nav-link
" href="{{ route('login') }}">登录</a></li>
                    <li class="nav-item"><a class="nav-link
" href="{{ route('register') }}">注册</a></li>
                @else
                    <li class="nav-item">
                        <a class="nav-link mt-1" href="{{ route('cart.index') }}"><i class="fa fa-shopping-cart"></i><svg style="width: 25px; height: 25px" t="1591604019649" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1224" width="200" height="200"><path d="M742.013651 742.471069c-42.266639 0-76.848249 34.58161-76.848249 76.848249 0 42.266639 34.58161 76.848249 76.848249 76.848249s76.848249-34.58161 76.848249-76.848249C818.860876 777.052678 784.279267 742.471069 742.013651 742.471069zM127.228683 127.686101l0 76.848249 76.848249 0 138.326439 292.022936-53.79316 92.218308c-3.842515 11.527544-7.68503 26.89658-7.68503 38.424124 0 42.266639 34.58161 76.848249 76.848249 76.848249l461.08847 0 0-76.848249-445.719434 0c-3.842515 0-7.68503-3.842515-7.68503-7.68503l0-3.842515 34.58161-65.320705 284.337907 0c30.739095 0 53.79316-15.370059 65.320705-38.424124l138.326439-249.756297c7.68503-7.68503 7.68503-11.527544 7.68503-19.211551 0-23.054065-15.370059-38.424124-38.424124-38.424124l-568.676837 0-34.58161-76.848249L127.228683 127.687124zM357.772406 742.471069c-42.266639 0-76.848249 34.58161-76.848249 76.848249 0 42.266639 34.58161 76.848249 76.848249 76.848249 42.266639 0 76.848249-34.58161 76.848249-76.848249C434.620655 777.052678 400.039046 742.471069 357.772406 742.471069z" p-id="1225"></path></svg></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="user-avatar pull-left" style="margin-right: 8px; margin-top: -5px;">
                                <img src="https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/60/h/60" class="img-responsive img-circle" width="30px" height="30px">
                            </span>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a href="{{ route('user_addresses.index') }}" class="dropdown-item">收货地址</a>
                            <a href="{{ route('orders.index') }}" class="dropdown-item">我的订单</a>
                            <a href="{{ route('installments.index') }}" class="dropdown-item">分期付款</a>
                            <a href="{{ route('products.favorites') }}" class="dropdown-item">我的收藏</a>
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">退出登录</a>

                            <form action="{{ route('logout') }}" id="logout-form" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endguest
{{--                登录注册链接结束--}}
            </ul>
        </div>
    </div>
</nav>
