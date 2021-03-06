<nav class="nav">
    <a class="nav-link {{ Route::is('home.travel*') || Route::is('home') ? 'active' : ''}}" href="{{ route('home.travel.index') }}">我的游记</a>
    <a class="nav-link {{ Route::is('home.message') ? 'active' : ''}}" href="{{ route('home.message') }}">我的消息 @if(auth()->user()->messages()->where('read', false)->count()) <i class="fa fa-bell-o text-warning"></i> @endif</a>
    <a class="nav-link {{ Route::is('home.order*') ? 'active' : ''}}" href="{{ url('home/order') }}">我的订单</a>
    <a class="nav-link {{ Route::is('home.setting') ? 'active' : ''}}" href="{{ route('home.setting') }}">设置中心</a>
</nav>