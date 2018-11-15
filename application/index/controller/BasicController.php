<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 13:11
 * Comment: 基础控制器
 */

namespace app\index\controller;

use think\Controller;
use think\Hook;
use think\Request;
use think\Session;

class BasicController extends Controller {

    // 用户id
    protected $user_id = 0;
    // 当前控制器名称
    protected $controller;
    // 当前操作名称
    protected $action;
    // 无需验证方法
    protected  $except_auth = [
        'User' => ['login', 'register', 'recover_pass', 'change_pass'],
        'Harvest' => ['index', 'detail'],
        'Product' => ['listing', 'detail'],
        'Index' => ['index'],
        'Verify' => ['attain'],
        'Sms' => ['attain'],
        'Image' => ['upload']
    ];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->controller =  $request->controller();
        $this->action =  $request->action();

        //过滤不需要登陆的行为
        if (isset($this->except_auth[$this->controller]) && in_array($this->action, $this->except_auth[$this->controller])) {
            return true;
        } else {
            // 判断用户登录状态
            if (session('?user')) {
                /* 验证token */
                // 获取客户端传来的token
                $client_token = $request->header('access_token');
                if ( !(!empty($client_token) && $this->check_token($client_token)) ) {
                    return $this->return_message(401, '请先登录系统');
                }
            } else {
                return $this->return_message(401, '请先登录系统');
            }
        }
    }

    /**
     * Token验证
     * @param $client_token
     * @return bool
     */
    public function check_token($client_token) {
        if (Session::has('access_token')){
            // 获取服务端存储的token
            $server_token = Session::get('access_token');
            if ($server_token == $client_token) {
                return true;
            }
        }
        return false;
    }

    /**
     * 监听服务
     */
    public function listener() {
        Hook::listen('response_send');
    }

    /* 返回信息 */
    public function return_message($code = 200, $message = '', $data = []) {

        if (is_null($data) || empty($data)) {
            return json([
                'code'      => $code,
                'message'   => $message
            ]);
        } else {
            return json([
                'code'      => $code,
                'message'   => $message,
                'data'      => $data
            ]);
        }
    }

}