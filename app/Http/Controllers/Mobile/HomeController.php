<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 个人设置
     * @param string $edit
     * @return mixed
     */
    public function index($edit = null)
    {
        return view()->first([
            "m.home.setting.{$edit}",
            'm.home.setting.index',
        ]);
    }

    // 订单列表
    public function order(Request $request)
    {
        $this->validate($request, [
            'status' => 'nullable|string|in:wait,cancel',
            'tno' => 'nullable',
        ]);
        $orders = $request->user()->orders()->whereHas('tuan', function ($query) use ($request) {
            if ($request->get('tno')) {
                $query->where('start_time', '>', today());
            }
        })->where(function ($query) use ($request) {
            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }
        })->with(['tuan.activity' => function ($query) {
            $query->select('id', 'title', 'thumb');
        }])->withCount('baomings')->paginate();

        return view('m.home.order', compact('orders'));
    }

    // 订单详情
    public function orderInfo(Order $order)
    {
        return view('m.home.order_info', compact('order'));
    }

}