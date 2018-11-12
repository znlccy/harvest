<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 14:22
 * Comment: 用户控制器
 */
namespace app\index\controller;

use app\index\model\User as UserModel;
use app\index\model\UserAccelerator;
use app\index\validate\User as UserValidate;
use app\index\model\Sms as SmsModel;
use app\index\model\UserInformation as UserInformationModel;
use app\index\model\Information as InformationModel;
use app\index\model\UserAccelerator as UserAcceleratorModel;
use app\index\model\Accelerator as AcceleratorModel;
use think\Config;
use think\Request;
use think\Session;

class User extends BasicController {

    /**
     * 声明用户模型
     * @var
     */
    protected $user_model;

    /**
     * 声明短信验证码模型
     * @var
     */
    protected $sms_model;

    /**
     *
     * 声明用户信息模型
     * @var
     */
    protected $user_info_model;

    /**
     * 声明用户活动模型
     * @var
     */
    protected $user_accelerator_model;

    /**
     * 声明消息模型
     * @var
     */
    protected $information_model;

    /**
     * 声明活动模型
     * @var
     */
    protected $accelerator_model;

    /**
     * 声明用户验证器
     * @var
     */
    protected $user_validate;

    /**
     * 声明用户分页器
     * @var
     */
    protected $user_page;

    /**
     * 默认构造函数
     * User constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->user_model = new UserModel();
        $this->sms_model = new SmsModel();
        $this->user_info_model = new UserInformationModel();
        $this->information_model = new InformationModel();
        $this->user_accelerator_model = new UserAcceleratorModel();
        $this->accelerator_model = new AcceleratorModel();
        $this->user_page = config('pagination');
        $this->user_validate = new UserValidate();
    }

    /**
     * 用户登录api接口
     */
    public function login() {

        //接收客户端提交的数据
        $mobile = request()->param('mobile');
        $password = request()->param('password');
        $verify = strtolower(request()->param('verify'));

        /* 验证规则 */
        $validate_data = [
            'mobile'        => $mobile,
            'password'      => $password,
            'verify'        => $verify,
        ];

        //实例化验证器
        $result   = $this->user_validate->scene('login')->check($validate_data);

        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        $user = $this->user_model->where('mobile', '=', $mobile)
            ->where('password', '=', md5($password))
            ->find();

        $data = [
            'login_time'        => date("Y-m-d H:s:i", time()),
            'login_ip'          => request()->ip()
        ];

        $this->user_model->save($data, ['id' => $user['id']]);

        if (empty($user) ) {
            return json(['code' => '404', 'message' => '数据库中还没有该用户或者输入的账号密码错误']);
        }

        Session::set('user', $user);
        $token = general_token($mobile, $password);
        Session::set('access_token', $token);

        return json(['code' => '200', 'message'   => '登录成功',  'access_token' => $token, 'mobile' => $mobile]);
    }

    /**
     * 用户注册api接口
     */
    public function register() {
        /* 获取客户端提交过来的数据 */
        $mobile = request()->param('mobile');
        $password = request()->param('password');
        $verify = request()->param('verify');
        $code = request()->param('code');

        /* 验证规则 */
        $validate_data = [
            'mobile'        => $mobile,
            'password'      => $password,
            'verify'        => $verify,
            'code'          => $code,
        ];

        //验证结果
        $result   = $this->user_validate->scene('register')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        //实例化模型
        $sms_code = $this->sms_model->where('mobile', '=', $mobile)->find();

        if ( empty($sms_code) ){
            return json(['code' => '404', 'message' => '还没有生成对应的短信验证码']);
        }

        if (strtotime($sms_code['expiration_time']) - time() < 0) {
            return json(['code' => '406', 'message' => '短信验证码已经过期']);
        }

        if ($sms_code['code'] != $code) {
            return json(['code' => '408', 'message' => '短信验证码错误']);
        }

        $user_data = [
            'mobile'        => $mobile,
            'password'      => md5($password),
            'register_time' => date('Y-m-d H:i:s', time())
        ];

        $register_result =$this->user_model->insertGetId($user_data);
        if ($register_result) {
            $user_data['id'] = $register_result;
            Session::set('user',$user_data);
            $token = general_token($mobile, $password);
            Session::set('access_token', $token);

            // 验证码使用一次后立即失效
            $this->sms_model->where('mobile', $mobile)->update(['create_time' => date('Y-m-d H:i:s', time())]);

            return json([
                'code'      => '200',
                'message'   => '注册成功',
                'access_token' => $token,
                'mobile' => $mobile
            ]);
        } else {
            return json([
                'code'      => '402',
                'message'   => '注册失败'
            ]);
        }

    }

    /**
     * 密码找回api接口
     */
    public function recover_pass() {

        /* 获取客户端提供的数据 */
        $mobile = request()->param('mobile');
        $code = request()->param('code');
        $verify = request()->param('verify');

        /* 验证数据 */
        $validate_data = [
            'mobile' => $mobile,
            'code'   => $code,
            'verify' => $verify,
        ];

        //验证结果
        $result   = $this->user_validate->scene('recover_pass')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        //实例化模型
        $sms_code = $this->sms_model->where('mobile', '=', $mobile)->find();

        if (empty($sms_code) ){
            return json(['code' => '404', 'message' => '还没有生成对应的短信验证码']);
        }

        if (strtotime($sms_code['expiration_time']) - time() < 0) {
            return json(['code' => '406', 'message' => '短信验证码已经过期']);
        }

        if ($sms_code['code'] != $code) {
            return json(['code' => '408', 'message' => '短信验证码错误']);
        }

        // 获取账号信息
        $user = $this->user_model->where('mobile', '=', $mobile)->find();
        // 有效时间(10分钟)
        $effective_time = time() + 600;
        $json = json_encode(['user' => $user['mobile'], 'effective_time' => $effective_time]);
        // 加密串(用于修改密码)
        $key = Config::get('secret_key');
        $encrypted_str = passport_encrypt($json, $key);

        return json([
            'code'      => '200',
            'message'   => '验证成功，请在10分钟内完成下一步',
            'data'      => $encrypted_str
        ]);

    }

    /**
     * 找回密码 - 修改密码api接口
     */
    public function change_pass() {
        /* 获取客户端提供的数据 */
        // $mobile = request()->param('mobile');
        $password = request()->param('password');
        $confirm_pass = request()->param('confirm_pass');
        $encrypted_str = request()->param('encrypted_str');

        /* 验证数据 */
        $validate_data = [
            'password' => $password,
            'confirm_pass'   => $confirm_pass,
            'encrypted_str' => $encrypted_str,
        ];

        //验证结果
        $result   = $this->user_validate->scene('change_pass')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        // 解码加密串
        $key = Config::get('secret_key');
        $arr = json_decode(passport_decrypt($encrypted_str, $key),true);
        // 用户手机号
        $mobile = $arr['user'];
        // 有效时间
        $effective_time = $arr['effective_time'];
        // 判断是否在有效时间内
        if (time() > $effective_time) {
            return json([ 'code' => '406', 'message'   => '操作时间过长，请重新发送验证码']);
        }

        //更新密码
        $passwordData = [
            'password'  => md5($password)
        ];

        //实例化模型
        $modify_result = $this->user_model->where('mobile', '=', $mobile)->update($passwordData);
        if ($modify_result) {
            // 验证码使用一次后立即失效
            $this->sms_model->where('mobile', $mobile)->update(['create_time' => date('Y-m-d H:i:s', time())]);
            return json(['code' => '200', 'message' => '密码更改成功']);
        } else {
            return json(['code' => '406', 'message' => '密码更改失败']);
        }
    }

    /**
     * 个人信息api接口
     */
    public function info() {
        // 用户手机号
        $mobile = session('user.mobile');

        //实例化模型
        $personal = $this->user_model->where('mobile', '=', $mobile)->find();
        if ($personal) {
            return json([
                'code'      => '200',
                'message'   => '查找成功',
                'data'      => $personal
            ]);
        } else {
            return json([
                'code'      => '404',
                'message'   => '该手机号未注册'
            ]);
        }
    }

    /**
     * 更改个人信息api接口
     */
    public function modify_info() {

        /* 获取客户端提交的数据 */
        $mobile = Session::get('user.mobile');
        $username = request()->param('username');
        $email = request()->param('email');
        $company = request()->param('company');
        $industry = request()->param('industry');
        $duty = request()->param('duty');

        /* 验证数据 */
        $validate_data = [
            'mobile'        => $mobile,
            'username'      => $username,
            'email'         => $email,
            'company'       => $company,
            'industry'      => $industry,
            'duty'          => $duty,
        ];

        //实例化验证器
        $result   = $this->user_validate->scene('modify_info')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        /* 更新数据 */
        $result = $this->user_model->where('mobile', '=', $mobile)->update($validate_data);

        /* 返回数据 */
        if ($result) {
            return json(['code' => '200', 'message' => '保存成功']);
        } else {
            return json(['code' => '402', 'message' => '保存失败，数据库中还没有该用户信息']);
        }
    }

    /**
     * 已登陆 - 修改密码接口
     */
    public function modify_pass() {
        /* 获取客户端提供的数据 */
        $user_id = Session::get('user.id');
        $old_password = request()->param('old_password');
        $password = request()->param('password');
        $confirm_pass = request()->param('confirm_pass');

        /* 验证数据 */
        $validate_data = [
            'old_password'      => $old_password,
            'password'          => $password,
            'confirm_pass'      => $confirm_pass,
        ];

        //实例化验证器
        $result   = $this->user_validate->scene('modify_pass')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        $db_password_old = $this->user_model->where('id','=', $user_id)
            ->where('password', '=', md5($old_password))
            ->field('password')
            ->find();

        if ( empty($db_password_old) ){
            return json(['code'=>'406','message'=>'原密码错误']);
        }

        if ($db_password_old['password'] == md5($password)) {
            return json(['code'=>'405','message'=>'该密码已经使用了，重新换一个']);
        }

        $data = [
            'password' => md5($password)
        ];

        $result =$this->user_model->where('id', '=', $user_id)->update($data);
        if ($result) {
            return json([
                'code'      => '200',
                'message'   => '更新成功'
            ]);
        } else {
            return json([
                'code'      => '403',
                'message'   => '更新失败'
            ]);
        }
    }

    /**
     * 通知消息api接口
     */
    public function notification() {

        /* 获取客户端提供的 */
        $page_size = request()->param('page_size', $this->user_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->user_page['JUMP_PAGE']);

        // 用户id
        $user_id = session('user.id');

        /* 验证数据 */
        $validate_data = [
            'page_size'         => $page_size,
            'jump_page'         => $jump_page,
        ];

        //验证结果
        $result   = $this->user_validate->scene('notification')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        $users = $this->user_info_model->where('user_id', $user_id)->field('info_id')->select();

        $info = [];
        foreach ($users as $key => $value) {
            $info[] = $value['info_id'];
        }

        $information = $this->information_model
            ->alias('in')
            ->field('in.id, in.title, in.publish_time, a.nick_name as publisher')
            ->join('tb_admin a', 'in.publisher = a.id')
            ->paginate($page_size, false, ['page' => $jump_page])->each(function($item, $key) use ($info){
                if (in_array($item['id'], $info)) {
                    $item['read_status'] = 1;
                } else {
                    $item['read_status'] = 0;
                }
                return $item;
            });

        /* 返回数据 */
        return json([
            'code'      => '200',
            'message'   => '获取通知信息成功',
            'data'      => $information
        ]);
    }

    /**
     * 通知信息详情api接口
     */
    public function notification_detail() {

        /* 获取客户端提供的数据 */
        $id = request()->param('id');
        // 用户手机号
        $user_id = session('user.id');


        /* 验证规则 */
        $validate_data = [
            'id'        => $id,
        ];

        //验证结果
        $result   = $this->user_validate->scene('notification_detail')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        $information = $this->information_model->alias('in')
            ->where('in.id', '=', $id)
            ->join('tb_admin a', 'in.publisher = a.id')
            ->field('in.id, in.title, in.publish_time, in.rich_text, a.nick_name as publisher')
            ->find();

        if ( empty($information) ){
            return json([
                'code'      => '404',
                'message'   => '消息不存在',
            ]);
        }

        $data = $this->user_info_model->where('user_id', '=', $user_id)
            ->where('info_id', '=', $id)
            ->find();

        if ( empty($data) ){
            $this->user_info_model->insert(['user_id' => $user_id, 'info_id' => $id, 'status' => 1]);
        }

        return json([
            'code'      => '200',
            'message'   => '查询信息成功',
            'data'      => $information
        ]);
    }

    /**
     * 登出api接口
     */
    public function logout(){
        if (Session::has('user') && Session::has('access_token')) {
            //删除Session中的数据
            Session::delete('user');
            Session::delete('access_token');
            return json(['code' => '200', 'message'   => '登出成功']);
        } else {
            return json(['code' => '200', 'message'   => '您还没登录过']);
        }
    }

    /**
     * 验证token
     * @param $mobile
     */
    protected function token($mobile) {
        $now = date('Y-m-d', time());
        $expired = date('Y-m-d', strtotime("+1 day",strtotime(time())));
    }

    /**
     * 已经报名加速器api接口
     */
    public function apply() {

        /* 获取客户端提供的数据 */
        $user_id = Session::get('user.id');

        /* 客户端提交过来的分页数据 */
        $page_size = request()->param('page_size', $this->user_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->user_page['JUMP_PAGE']);

        /* 验证规则 */
        $validate_data = [
            'page_size'     => $page_size,
            'jump_page'     => $jump_page,
        ];

        //验证结果
        $result   = $this->user_validate->scene('apply')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        $data = $this->user_accelerator_model
            ->alias('ua')
            ->where('ua.user_id', '=', $user_id)
            ->join('tb_accelerator a', 'ua.accelerator_id = a.id')
            ->join('tb_user tu', 'ua.user_id = tu.id')
            ->field('a.status , a.name, a.id, a.apply_time, tu.mobile')
            ->paginate($page_size, false, ['page' => $jump_page]);

        return json(['code' => '200', 'message' => '读取成功', 'data' => $data]);

    }

    /**
     * 获取加速器详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail() {

        //获取客户端提交过来的数据
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'        => $id
        ];

        //验证结果
        $result = $this->user_validate->scene('detail')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => ''
            ]);
        }

        //返回客户端结果
        $accelerator = $this->accelerator_model->where('id', $id)->find();
        if ($accelerator) {
            return  json([
                'code'      => '200',
                'message'   => '查询加速器成功',
                'data'      => $accelerator
            ]);
        } else {
            return  json([
                'code'      => '404',
                'message'   => '查询加速器失败，数据库中不存在'
            ]);
        }
    }

    /**
     * 取消沙龙报名
     */
    public function cancel(){
        /* 获取客户端提供的数据 */
        $user_id = Session::get('user.id');

        /* 需要取消的活动ID */
        $id = request()->param('id');

        /* 验证规则 */
        $validate_data = [
            'id'        => $id,
        ];

        //验证结果
        $result   = $this->user_validate->scene('cancel')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->user_validate->getError()]);
        }

        //确认是否报名
        $user_active = $this->user_accelerator_model
            -> where('user_id', '=', $user_id)
            -> where('accelerator_id', '=', $id)
            -> select();

        if ( empty($user_active) ){
            return json(['code' => '401', 'message' => '未报名']);
        }

        $result = $this->user_accelerator_model
            -> where('user_id', '=', $user_id)
            -> where('accelerator_id', '=', $id)
            -> delete();
        if ($result) {
            // 活动人数-1
            $this->accelerator_model->where(['id' => $id])->setDec('register');
            return json(['code' => '200', 'message' => '提交成功']);
        } else {
            return json(['code' => '404', 'message' => '报名失败']);
        }
    }

    /**
     * 用户成员列表
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index() {

        $company = $this->user_group_model->alias('gm')
            ->order('gm.group_id', 'desc')
            ->join('tb_user tu', 'gm.user_id = tu.id')
            ->join('tb_group tg', 'gm.group_id = tg.id')
            ->select();

        if ($company) {
            return json([
                'code'      => '200',
                'message'   => '获取信息成功',
                'data'      => $company
            ]);
        }
    }

    /**
     * 用户保存个人资料
     * @return \think\response\Json
     */
    public function save() {
        //获取客户端提交的数据
        $id = request()->param('id');
        $company = request()->param('company');
        $industry = request()->param('industry');
        $duty = request()->param('duty');
        $status = request()->param('status', 1);
        $mobile = request()->param('mobile');
        $email = request()->param('email');
        $login_ip = request()->ip();

        //验证数据
        $validate_data = [
            'id'                => $id,
            'company'           => $company,
            'industry'          => $industry,
            'duty'              => $duty,
            'mobile'            => $mobile,
            'status'            => $status,
            'email'             => $email,
            'login_ip'          => $login_ip,
            'update_time'       => date('Y-m-d H:s:i', time()),
            'register_time'       => date('Y-m-d H:s:i', time())
        ];

        //验证结果
        $result = $this->user_validate->scene('apply')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => $this->user_validate->getError()
            ]);
        }
        if (empty($id)) {
            $apply_result = $this->user_model->save($validate_data);
        } else {
            $apply_result = $this->user_model->save($validate_data, ['id' => $id]);
        }
        //返回数据
        if ($apply_result) {
            return json([
                'code'      => '200',
                'message'   => '操作成功'
            ]);
        }
    }

}