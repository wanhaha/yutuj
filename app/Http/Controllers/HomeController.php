<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Message;
use App\Models\Order;
use App\Models\Travel;
use App\Rules\Code;
use App\Rules\Mobile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('www.home.setting');
    }

    /**
     * 更新主页背景图片
     * @param Request $request
     * @return array
     */
    public function backgroundImage(Request $request)
    {
        $this->validate($request, [
            'bg' => 'required|image'
        ]);
        $path = $request->file('bg')->store('bg');
        $user = $request->user();
        $user->bg_home = $path;
        $user->save();

        return ['path' => Storage::url($path), 'message' => '主页背景已更新。'];
    }

    /**
     * 更新头像
     * @param Request $request
     * @return array
     */
    public function uploadAvatar(Request $request)
    {
        $this->validate($request, [
            'avatar' => 'required|image'
        ]);
        $path = $request->file('avatar')->store('avatar');
        $user = $request->user();
        $user->avatar = $path;
        $user->save();

        return ['path' => Storage::url($path), 'message' => '头像已更新。'];
    }

    /**
     * 更新个人资料
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $data = $this->validate($request, [
            'name' => 'filled|string|between:3,20',
            'sex' => 'filled|string|in:F,M',
            'province' => 'filled|string',
            'city' => 'filled|string',
            'birthday' => 'filled|date',
            'description' => 'filled|string|between:3,250',
        ]);

        $request->user()->update($data);
        return ['message' => '个人资料已更新。'];
    }

    /**
     * 更新个人密码
     * @param Request $request
     * @return array
     */
    public function updatePwd(Request $request)
    {
        $this->validate($request, [
            'code' => ['bail', 'required', 'string', 'min:4', new Code('forgot')],
            'password' => 'required|string|confirmed|min:6',
        ]);

        $request->user()->password = bcrypt($request->password);
        $request->user()->save();
        return ['message' => '个人密码已更新。'];
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return array
     */
    public function updateMobile(Request $request)
    {
        $op = Auth::user()->mobile ? 'update' : 'register';
        $this->validate($request, [
            'mobile' => ['required', 'string', new Mobile(), 'unique:users,mobile'],
            'code' => ['bail', 'required', 'string', 'min:4', new Code($op)],
        ]);
        $request->user()->mobile = $request->get('mobile');
        $request->user()->save();
        return ['message' => '绑定手机已更新。'];
    }


    /**
     * 会员给游记点赞
     * @param Travel $travel
     * @return array
     */
    public function likeTravel(Travel $travel)
    {
        $travel->likes()->toggle(Auth::id());
        return ['likes_count' => $travel->likes()->count()];
    }

    /**
     * 关注会员成为粉丝
     * @param User $user
     * @return array
     */
    public function fans(User $user)
    {
        $gz = Follow::firstOrNew(['user_id' => Auth::id(), 'gz_id' => $user->id]);
        if ($gz->id) {
            $is_fans = false;
            $gz->delete();
        } else {
            $is_fans = true;
            $gz->save();
        };
        return ['fans_count' => $user->fans()->count(), 'follows_count' => $user->follows()->count(), 'is_fans' => $is_fans];
    }

    /**
     * 是否已经是粉丝
     * @param User $user
     * @return array
     */
    public function isFans(User $user)
    {
        $num = Follow::where(['user_id' => Auth::id(), 'gz_id' => $user->id])->count();
        return ['is_fans' => !!$num];
    }

    // 订单列表
    public function order(Request $request)
    {
        $this->validate($request, [
            'status' => 'nullable|string|in:wait,cancel',
            'tno' => 'nullable',
        ]);
        $orders = $request->user()->orders()->whereHas('tuan', function ($query) use ($request) {
            if ($request->filled('tno')) {
                $query->where('start_time', '>', today());
            }
        })->where(function ($query) use ($request) {
            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }
        })->with(['tuan.activity' => function ($query) {
            $query->select('id', 'title', 'thumb');
        }])->withCount('baomings')->paginate();

        return view('www.home.order', compact('orders'));
    }

    // 订单详情
    public function orderInfo(Order $order)
    {
        return view('www.home.order_info', compact('order'));
    }

    // 消息列表
    public function message()
    {
        $messages = Auth::user()->messages()->latest()->paginate();
        return view('www.home.message', compact('messages'));
    }

    // 删除多条消息
    public function destroyMessages(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'numeric'
        ]);
        $ids = Message::find($request->get('ids'))->map(function ($message) {
            if ($message->user_id === Auth::id()) {
                return $message->id;
            }
        })->toArray();

        $row = Message::destroy($ids);
        return ['message' => $row . '条记录被删除。'];
    }

    // 已读多条消息
    public function readMessages(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'numeric'
        ]);
        $ids = Message::find($request->get('ids'))->map(function ($message) {
            if ($message->user_id === Auth::id()) {
                return $message->id;
            }
        });

        $row = Message::whereIn('id', $ids)->update(['read' => true]);
        return ['message' => $row . '条记录被标记已读。'];
    }
}
