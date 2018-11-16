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
use app\index\response\Code;
use app\index\validate\User as UserValidate;
use app\index\model\Sms as SmsModel;
use app\index\model\UserInformation as UserInformationModel;
use app\index\model\Information as InformationModel;
use app\index\model\Product as ProductModel;
use app\index\model\UserProduct as UserProductModel;
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
     * 声明产品模型
     * @var
     */
    protected $product_model;

    /**
     * 用户产品模型
     * @var
     */
    protected $user_product_model;

    /**
     * 声明消息模型
     * @var
     */
    protected $information_model;

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
        $this->product_model = new ProductModel();
        $this->user_product_model = new UserProductModel();
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
            'login_time'        => date("Y-m-d H:s:i", time())
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
        $type = request()->param('type');
        $mobile = request()->param('mobile');
        $password = request()->param('password');
        $verify = request()->param('verify');
        $code = request()->param('code');

        /* 验证规则 */
        $validate_data = [
            'type'          => $type,
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
            'type'          => $type,
            'create_time' =>date('Y-m-d H:i:s', time()),
            'update_time' =>date('Y-m-d H:i:s', time()),
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
        $mobile = Session::get('user.mobile');

        //实例化模型
        $personal = $this->user_model->where('mobile', '=', $mobile)->find();
        if ($personal) {
            return $this->return_message(Code::SUCCESS, '获取个人信息成功', $personal);
        } else {
            return $this->return_message(Code::FAILURE, '该手机号未注册');
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
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 更新数据 */
        $result = $this->user_model->where('mobile', '=', $mobile)->update($validate_data);

        /* 返回数据 */
        if ($result) {
            return $this->return_message(Code::SUCCESS, '保存成功');
        } else {
            return $this->return_message(Code::FAILURE, '保存失败，数据库中还没有该用户信息');
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
            return $this->return_message(Code::SUCCESS, '更新成功');
        } else {
            return $this->return_message(Code::FAILURE, '更新失败');
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

        return $this->return_message(Code::SUCCESS, '查询信息成功', $information);
    }

    /**
     * 登出api接口
     */
    public function logout(){
        if (Session::has('user') && Session::has('access_token')) {
            //删除Session中的数据
            Session::delete('user');
            Session::delete('access_token');
            return $this->return_message(Code::SUCCESS, '登出成功');
        } else {
            return $this->return_message(Code::INVALID,'您还没登录过');
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
     * 用户保存个人资料
     * @return \think\response\Json
     */
    public function save() {
        /* 接收参数 */
        $id = request()->param('id');
        $type = intval(request()->param('type'));
        $status = request()->param('status');
        $username = request()->param('username');
        $mobile = request()->param('mobile');
        $duty = request()->param('duty');
        $department = request()->param('department');
        $phone = request()->param('phone');
        $wechat = request()->param('wechat');
        $email = request()->param('email');
        $link = request()->param('link');

        switch ($type) {
            case 1:
                $enterprise = request()->param('enterprise');
                $introduce = request()->param('introduce');
                $industry = request()->param('industry');
                $capital = request()->param('capital');
                $revenue = request()->param('revenue');
                $assets = request()->param('assets');
                $address = request()->param('address');

                $validate_entrepreneur = [
                    'id'            => $id,
                    'type'          => $type,
                    'status'        => $status,
                    'enterprise'    => $enterprise,
                    'introduce'     => $introduce,
                    'industry'      => $industry,
                    'capital'       => $capital,
                    'revenue'       => $revenue,
                    'assets'        => $assets,
                    'address'       => $address,
                    'username'      => $username,
                    'mobile'        => $mobile,
                    'duty'          => $duty,
                    'department'    => $department,
                    'phone'         => $phone,
                    'wechat'        => $wechat,
                    'email'         => $email,
                    'link'          => $link
                ];

                /* 验证规则 */
                $validate_entrepreneur_rule = [
                    'id'            => 'number',
                    'type'          => 'require|number|in:1,2',
                    'status'        => 'require|number|in:0,1',
                    'enterprise'    => 'require|max:255',
                    'introduce'     => 'require|max:500',
                    'industry'      => 'require|max:255',
                    'capital'       => 'require|number',
                    'revenue'       => 'require|number|in:0,1',
                    'assets'        => 'require|number',
                    'username'      => 'require|max:120',
                    'mobile'        => 'require|max:32|unique:tb_user',
                    'duty'          => 'require|max:255',
                    'department'    => 'require|max:300',
                    'phone'         => 'require|max:60',
                    'wechat'        => 'require|max:60',
                    'email'         => 'require|email',
                    'link'          => 'require|max:800',
                ];

                /* 验证结果 */
                $result = $this->user_validate->check($validate_entrepreneur, $validate_entrepreneur_rule);

                if (true != $result) {
                    return $this->return_message(Code::INVALID, $this->user_validate->getError());
                }

                /* 返回结果 */
                if (empty($id)) {
                    if ($validate_entrepreneur['status'] !== 0) {
                        $validate_entrepreneur['status'] = 0;
                    }
                    $entrepreneur = $this->user_model->save($validate_entrepreneur);
                } else {

                    if ($validate_entrepreneur['status'] !== 0) {
                        $validate_entrepreneur['status'] = 0;
                    }
                    $validate_entrepreneur['update_time'] = date('Y-m-d H:i:s', time());
                    $entrepreneur = $this->user_model->where('id', $id)->update($validate_entrepreneur);
                }

                if ($entrepreneur) {
                    return $this->return_message(Code::SUCCESS, '创业者数据操作成功');
                } else {
                    return $this->return_message(Code::FAILURE, '创业者数据操作失败');
                }
                break;
            case 2:
                /* 接收参数 */
                $company = request()->param('company');
                $capital_body = request()->param('capital_body');
                $location = request()->param('location');
                $invest_industry = request()->param('invest_industry');
                $invest_address = request()->param('invest_address');
                $invest_amount = request()->param('invest_amount');
                $text_domain = request()->param('text_domain');

                /* 验证参数 */
                $validate_collaborator = [
                    'id'            => $id,
                    'type'          => $type,
                    'status'        => $status,
                    'company'       => $company,
                    'location'      => $location,
                    'capital_body'  => $capital_body,
                    'invest_industry'=> $invest_industry,
                    'invest_address'=> $invest_address,
                    'invest_amount' => $invest_amount,
                    'text_domain'   => $text_domain,
                    'username'      => $username,
                    'mobile'        => $mobile,
                    'duty'          => $duty,
                    'department'    => $department,
                    'phone'         => $phone,
                    'wechat'        => $wechat,
                    'email'         => $email,
                    'link'          => $link
                ];

                /* 验证规则 */
                $validate_collaborator_rule = [
                    'id'            => 'number',
                    'type'          => 'require|number|in:1,2',
                    'status'        => 'require|number|in:0,1',
                    'company'       => 'require|max:255',
                    'location'      => 'require|max:500',
                    'capital_body'       => 'require|number',
                    'invest_industry'=> 'require|max:300',
                    'invest_address'=> 'require|max:400',
                    'invest_amount' => 'require|number',
                    'text_domain'   => 'require|max:600',
                    'username'      => 'require|max:120',
                    'mobile'        => 'require|max:32|unique:tb_user',
                    'duty'          => 'require|max:255',
                    'department'    => 'require|max:300',
                    'phone'         => 'require|max:60',
                    'wechat'        => 'require|max:60',
                    'email'         => 'require|email',
                    'link'          => 'require|max:800',
                ];

                /* 验证结果 */
                $result = $this->user_validate->check($validate_collaborator, $validate_collaborator_rule);

                if (true !== $result) {
                    return $this->return_message(Code::INVALID, $this->user_validate->getError());
                }

                /* 返回结果 */
                if (empty($id)) {
                    if ($validate_collaborator['status'] !== 0) {
                        $validate_collaborator['status'] = 0;
                    }
                    $collaborator = $this->user_model->save($validate_collaborator);
                } else {
                    if ($validate_collaborator['status'] !== 0) {
                        $validate_collaborator['status'] = 0;
                    }
                    $validate_collaborator['update_time'] = date('Y-m-d H:i:s', time());
                    $collaborator = $this->user_model->where('id', $id)->update($validate_collaborator);
                    /*$collaborator = $this->user_model->save($validate_collaborator, ['id' => $id]);*/
                }

                if ($collaborator) {
                    return $this->return_message(Code::SUCCESS, '合作者数据操作成功');
                } else {
                    return $this->return_message(Code::FAILURE, '合作者数据操作失败');
                }
                break;
            default:
                return $this->return_message(Code::INVALID,'传入的用户类型不对，只能是创业者和合作者');
                break;
        }
    }

    /* 成果提交 */
    public function product() {

        /* 接收参数 */
        $user_id = Session::get('user.id');

        $name = request()->param('name');
        $description = request()->param('description');
        $detail = request()->file('detail');

        /* 移动成果详情 */
        if ($detail) {
            $config = [
                'ext'   => 'rar,zip'
            ];
            $info = $detail->validate($config)->move(ROOT_PATH . 'public' . DS . 'images');
            if ($info) {
                $sub_path = str_replace('\\', '/', $info->getSaveName());
                $detail = '/images/' . $sub_path;
            } else {
                return $this->return_message(Code::INVALID, '上传文件格式必须是Rar和Zip格式');
            }
        }

        /* 验证参数 */
        $validate_data = [
            'user_id'       => $user_id,
            'name'          => $name,
            'description'   => $description,
            'status'        => 0,
            'recommend'     => 1,
            'detail'        => $detail
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('product')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 判断用户是否登录 */
        if (is_null($user_id) || empty($user_id)) {
            return $this->return_message(Code::INVALID, '用户还没有登录');
        }

        $user = $this->user_model->where('id', $user_id)->find();

        if (empty($user)) {
            return $this->return_message(Code::FAILURE, '用户不存在');
        }

        $product = $this->product_model->save($validate_data);
        /*$user_product = $this->user_product_model->where(['user_id' => $user_id, 'product_id' => $product_id])->find();*/
        /*if ($user_product) {
            return $this->return_message(Code::INVALID, '该用户已经创建该产品了');
        } else {
            $product = $this->user_product_model->save(['user_id' => $user_id, 'product_id' => $product_id]);
        }*/

        if ($product) {
            return $this->return_message(Code::SUCCESS, '发布成果成功');
        } else {
            return $this->return_message(Code::FAILURE, '发布成果失败');
        }

    }

    /* 发布的科技成果 */
    public function product_listing() {

        $user_id = session('user.id');

        if (is_null($user_id) || empty($user_id)) {
            return $this->return_message(Code::FAILURE, '用户还没登录');
        }

        $user = $this->user_model->where('id', $user_id)->find();

        if (empty($user)) {
            return $this->return_message(Code::FAILURE, '不存在该用户');
        }

        /* 接收参数 */
        $page_size = request()->param('page_size', $this->user_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->user_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('product_listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /*$product = $this->user_product_model
            ->alias('upm')
            ->where('upm.user_id', '=', $user_id)
            ->join('tb_product tp', 'upm.product_id = tp.id')
            ->paginate($page_size, false, ['page' => $jump_page]);*/
        $product = $this->product_model
            ->where(['user_id' => $user_id, 'status' => 1])
            ->order('id','asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($product) {
            return $this->return_message(Code::SUCCESS, '获取创业者发布的成果列表成功',$product);
        } else {
            return $this->return_message(Code::FAILURE, '获取创业者发布的成果列表失败');
        }

    }

    /* 成果详情 */
    public function product_detail() {

        /* 获取客户端提供的数据 */
        $id = request()->param('id');
        // 用户手机号
        $user_id = session('user.id');

        /* 验证规则 */
        $validate_data = [
            'id'        => $id,
        ];

        //验证结果
        $result   = $this->user_validate->scene('product_detail')->check($validate_data);
        if (!$result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        if (is_null($user_id) || empty($user_id)) {
            return $this->return_message(Code::FAILURE, '您还没有登录');
        }

        $product = $this->product_model->where('id', $id)->find();

        if ($product) {
            return $this->return_message(Code::SUCCESS, '获取成果详情成功',$product);
        }
        return $this->return_message(Code::FAILURE, '获取成果详情失败');
    }

    /* 合作产品列表 */
    public function cooperate_listing() {
        $user_id = session('user.id');

        if (is_null($user_id) || empty($user_id)) {
            return $this->return_message(Code::FAILURE, '用户还没登录');
        }

        $user = $this->user_model->where('id', $user_id)->find();

        if (empty($user)) {
            return $this->return_message(Code::FAILURE, '不存在该用户');
        }

        /* 接收参数 */
        $page_size = request()->param('page_size', $this->user_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->user_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('product_listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        $product = $this->user_product_model
            ->alias('upm')
            ->where('upm.user_id', '=', $user_id)
            ->join('tb_product tp', 'upm.product_id = tp.id')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($product) {
            return $this->return_message(Code::SUCCESS, '获取用户合作的成果列表成功',$product);
        } else {
            return $this->return_message(Code::FAILURE, '获取用户合作的成果列表失败');
        }
    }

}