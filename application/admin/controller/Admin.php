<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 12:03
 * Comment: 管理员控制器
 */
namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\admin\validate\Admin as AdminValidate;
use app\admin\model\Role as RoleModel;
use app\admin\model\AdminRole as AdminRoleModel;
use app\admin\model\Sms as SmsModel;
use think\Request;
use think\Session;
use think\Validate;
use gmars\rbac\Rbac;

class Admin extends BasisController {

    /**
     * 声明管理员模型
     * @var
     */
    protected $admin_model;

    /**
     * 声明角色模型
     * @var
     */
    protected $role_model;

    /**
     * 声明短信验证码模型
     * @var
     */
    protected $sms_model;

    /**
     * 管理员分页器
     * @var
     */
    protected $admin_page;

    /**
     * 管理员验证器
     * @var
     */
    protected $admin_validate;

    /**
     * 默认构造函数
     * Admin constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->admin_model = new AdminModel();
        $this->admin_validate = new AdminValidate();
        $this->role_model = new RoleModel();
        $this->sms_model = new SmsModel();
        $this->admin_page = config('pagination');
    }

    /**
     * 管理员手机登录api接口
     */
    public function mobile_login() {

        /* 获取客户端提供的数据 */
        $mobile = request()->param('mobile');
        $code = request()->param('code');

        /* 验证规则 */
        $validate_data = [
            'mobile'     => $mobile,
            'code'       => $code,
        ];

        //实例化验证器
        $result   = $this->admin_validate->scene('mobile_login')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->admin_validate->getError()]);
        }

        //实例化模型
        $admin = $this->admin_model->where('mobile', '=', $mobile)
            ->where('status', '=','1')
            ->find();

        if (empty($admin) ){
            return json(['code' => '402', 'message' => '登录失败']);
        }

        //比对短信验证码
        $sms_code = $this->sms_model->where('mobile', '=', $mobile)->find();

        if (empty($sms_code)) {
            return json(['code' => '404', 'message' => '该手机还没有生成注册码']);
        }

        if (strtotime($sms_code['expiration_time']) - time() < 0) {
            return json(['code' => '405', 'message' => '验证码已经过期']);
        }

        if ($sms_code['code'] != $code) {
            return json(['code' => '407', 'message' => '登录失败']);
        }

        //更新用户登陆记录
        $data = [
            'login_time'     => date('Y-m-d H:i:s',time()),
            'login_ip'       => request()->ip(),
            'authentication' => 1
        ];

        //更新用户登录数据
        $result = $this->admin_model->where('mobile', '=', $mobile)->update($data);

        if ($result) {
            Session::set('admin',$admin);
            $token = general_token($mobile, time());
            Session::set('admin_token', $token);

            // 验证码使用一次后立即失效
            $this->sms_model->where('mobile', $mobile)->update(['create_time' => date('Y-m-d H:i:s', time())]);
            return json(['code' => '200', 'message' => '登录成功', 'admin_token' => $token, 'real_name' => $admin['real_name']]);
        }else{
            return json(['code' => '408', 'message' => '登录失败']);
        }
    }

    /**
     * 管理员账号登录api接口
     */
    public function account_login() {

        //接收参数
        $mobile = request()->param('mobile');
        $password = request()->param('password');

        /* 验证规则 */
        $validate_data = [
            'mobile'     => $mobile,
            'password'   => $password,
        ];

        //实例化验证器
        $result   = $this->admin_validate->scene('account_login')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->admin_validate->getError()]);
        }
        /* 进行逻辑处理 */

        //数据库实例化
        $admin = $this->admin_model->where('mobile', '=', $mobile)
            ->where('password', '=', md5($password))
            ->where('status', '=', '1')
            -> find();

        //不存在该用户名
        if (empty($admin) ){
            return json(['code' => '402', 'message' => '登录失败']);
        }

        //检查是否实名
        if ( !($admin['authentication'] === 1) ){
            $authentication_data = ['mobile'=>$mobile];
            return json(['code' => '302', 'message' => '需进行手机真实性认证', 'data' => $authentication_data ]);
        }

        Session::set('admin',$admin);
        $token = general_token($mobile, $password);
        Session::set('admin_token', $token);
        return json(['code' => '200', 'message' => '登录成功', 'admin_token' => $token, 'real_name' => $admin['real_name']]);
    }

    /**
     * 管理员列表api接口
     */
    public function entry() {
        /* 获取客户端提供的数据 */
        $id = request()->param('id');
        $mobile = request()->param('mobile');
        $status = request()->param('status/d');
        $real_name = request()->param('real_name');
        $register_start = request()->param('register_start');
        $register_end = request()->param('register_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $login_start = request()->param('login_start');
        $login_end = request()->param('login_end');
        $login_ip = request()->param('login_ip');
        $create_ip = request()->param('create_ip');
        $page_size = request()->param('page_size',$this->admin_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->admin_page['JUMP_PAGE']);

        //验证数据
        $validate_data = [
            'id'             => $id,
            'mobile'         => $mobile,
            'status'         => $status,
            'real_name'      => $real_name,
            'register_start' => $register_start,
            'register_end'   => $register_end,
            'update_start'   => $update_start,
            'update_end'     => $update_end,
            'login_start'    => $login_start,
            'login_end'      => $login_end,
            'login_ip'       => $login_ip,
            'create_ip'      => $create_ip,
            'page_size'      => $page_size,
            'jump_page'      => $jump_page,
        ];

        //实例化验证器
        $result = $this->admin_validate->scene('entry')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->admin_validate->getError()]);
        }

        //过滤条件
        $conditions = [];
        if ($id) {
            $conditions['id'] = $id;
        }
        if ($mobile) {
            $conditions['mobile'] = $mobile;
        }
        if (is_null($status)) {
            $conditions['status'] = ['in',[0,1]];
        } else {
            switch ($status) {
                case 0:
                    $conditions['status'] = $status;
                    break;
                case 1:
                    $conditions['status'] = $status;
                    break;
                default:
                    break;
            }
        }
        if ($real_name) {
            $conditions['real_name'] = ['like', '%' . $real_name . '%'];
        }
        if ($register_start && $register_end) {
            $conditions['create_time'] = ['between time', [$register_start, $register_end]];
        }
        if ($update_start && $update_end) {
            $conditions['update_time'] = ['between time', [$update_start, $update_end]];
        }
        if ($login_start && $login_end) {
            $conditions['login_time'] = ['between time', [$login_start, $login_end]];
        }

        if ($login_ip) {
            $conditions['login_ip'] = ['like', '%' . $login_ip . '%'];
        }

        if ($create_ip) {
            $conditions['create_ip'] = ['like', '%' . $create_ip . '%'];
        }

        //返回数据
        $admin_data = $this->admin_model->where($conditions)
            ->with(['role' => function($query){
                $query->withField("id, name");
            }])->paginate($page_size, false, ['page' => $jump_page]);

        return json([
            'code'      => '200',
            'message'   => '获取列表成功',
            'data'      => $admin_data
        ]);
    }

    /**
     * 管理员详情api接口
     */
    public function detail() {
        //获取客户端提交的数据
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'            => $id
        ];

        //验证结果
        $result = $this->admin_validate->scene('detail')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => $this->admin_validate->getError()
            ]);
        }

        //返回数据
        $service = $this->admin_model->where('id', $id)->find();
        if ($service) {
            return json([
                'code'      => '200',
                'message'   => '查询数据成功',
                'data'      => $service
            ]);
        } else {
            return json([
                'code'      => '404',
                'message'   => '查询数据失败,数据不存在'
            ]);
        }
    }

    /**
     * 管理员删除
     */
    public function delete() {
        //获取客户端提交过来的数据
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'            => $id
        ];

        //验证结果
        $result = $this->admin_validate->scene('delete')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => $this->admin_validate->getError()
            ]);
        }

        if ($id == '1') {
            return json([
                'code'      => '402',
                'message'   => '超级管理员不允许被删除'
            ]);
        } else {
            //返回结果
            $delete = $this->admin_model->where('id', $id)->delete();
            if ($delete) {
                return json([
                    'code'      => '200',
                    'message'   => '删除数据成功'
                ]);
            } else {
                return json([
                    'code'      => '401',
                    'message'   => '删除数据失败'
                ]);
            }
        }
    }

    /**
     * 分配用户角色权限api接口
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function assign_admin_role() {
        //实例化权限控制器
        $rbac = new Rbac();

        /* 接收客户端提供的数据 */
        $id = request()->param('id');
        $mobile = request()->param('mobile');
        $password = request()->param('password');
        $confirm_pass = request()->param('confirm_pass');
        $real_name = request()->param('real_name');
        $status = request()->param('status');
        $role_id = request()->param('role_id/a');

        $admin_model = new AdminModel();
        $admin_role_model = new AdminRoleModel();
        $rule = [
            'id'            => 'number',
            'password'      => 'alphaDash|length:8,25',
            'confirm_pass'  => 'alphaDash|length:8,25|confirm:password',
            'real_name'     => 'require|max:40',
            'status'        => 'require|number',
            'role_id'       => 'require|array'
        ];

        //如果是更新修改passwrod验证规则
        if (empty($id)){
            $rule_add = [
                'mobile'        => 'require|length:11|unique:tb_admin',
                'password'      => 'require|alphaDash|length:8,25',
                'confirm_pass'  => 'require|alphaDash|length:8,25|confirm:password',
            ];
            $rule = array_merge($rule, $rule_add);
        }

        $message = [
            'id'            => 'ID',
            'mobile'        => '手机号',
            'password'      => '密码',
            'confirm_pass'  => '确认密码',
            'real_name'     => '姓名',
            'status'        => '状态',
            'role_id'       => '角色ID',
        ];

        $validate_data = [
            'id'            => $id,
            'mobile'        => $mobile,
            'password'      => $password,
            'confirm_pass'  => $confirm_pass,
            'real_name'     => $real_name,
            'status'        => $status,
            'role_id'       => $role_id
        ];

        $validate = new Validate($rule, [], $message);
        $result = $validate->check($validate_data);

        if (!$result) {
            return json(['code' => '401', 'message' => $validate->getError()]);
        }

        /* 封装数据 */
        $user_data = [
            'real_name'      => $real_name,
            'status'         => $status,
            'create_ip'      => request()->ip()
        ];

        if ( empty($id) ){
            $data_add = [
                'create_time'  => date('Y-m-d H:i:s'),
                'mobile'       => $mobile,
                'password'     => md5($password)
            ];
            $user_data = array_merge($user_data, $data_add);
        }else{
            if ( !empty($password) && !empty($confirmpass) ){
                $data_add = [
                    'password'  => md5($password)
                ];
                $user_data = array_merge($user_data, $data_add);
            }
        }

        $rbacObj = new Rbac();
        if (!empty($id)) {

            $update_result = $admin_model->where('id','=', $id)->update($user_data);
            $admin_role_model->where('user_id',$id)->delete();
            $result = $rbacObj->assignUserRole($id, $role_id);

            if ($result) {
                return json([
                    'code'      => '200',
                    'message'   => '更新成功'
                ]);
            }
        } else {
            /* 添加用户表之后，再添加用户角色表 */
            $uid = $admin_model->insertGetId($user_data);

            /* 添加用户角色表 */
            if ($uid) {
                /* 用户添加成功后，添加用户角色表 */
                $result = $rbacObj->assignUserRole($uid, $role_id);

                if ($result) {
                    return json([
                        'code'      => '200',
                        'message'   => '添加成功'
                    ]);
                } else {
                    return json([
                        'code'      => '403',
                        'message'   => '添加失败'
                    ]);
                }
            } else {
                return json([
                    'code'      => '403',
                    'message'   => '添加失败'
                ]);
            }
        }
    }

    /**
     * 角色下拉列表api接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function spinner() {
        $roles = $this->role_model->where('status', '=', '1')->field('id, name')->select();
        if (!empty($roles)) {
            return json([
                'code'          => '200',
                'message'       => '获取角色列表成功',
                'data'          => $roles
            ]);
        } else {
            return json([
                'code'          => '404',
                'message'       => '获取角色列表失败'
            ]);
        }
    }

    /**
     * 管理员个人信息api接口
     */
    public function info() {
        //获取用户手机
        $admin = Session::get('admin');
        //获取用户id
        $id = $admin['id'];

        //返回数据
        if ($id) {
            $admin_data = $this->admin_model->where('id', $id)->find();
            return json([
                'code'          => '200',
                'message'       => '查询数据成功',
                'data'          => $admin_data
            ]);
        } else {
            return json([
                'code'          => '404',
                'message'       => '查询数据失败',
            ]);
        }
    }

    /**
     * 管理员修改密码api接口
     */
    public function change_password() {

        //获取客户端提交过来的数据
        $password = request()->param('password');
        $confirm_pass = request()->param('confirm_pass');

        //验证数据
        $validate_data = [
            'password'      => $password,
            'confirm_pass'  => $confirm_pass
        ];

        //验证结果
        $result = $this->admin_validate->scene('info')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => $this->admin_validate->getError()
            ]);
        }

        //获取Session中的数据
        $admin = Session::get('admin');
        $id = $admin['id'];

        //返回数据
        if ($id) {
            $update_data = [
                'password'      => md5($password)
            ];
            $this->admin_model->save($update_data, ['id' => $id]);
            return json([
                'code'      => '200',
                'message'   => '更新密码成功'
            ]);
        } else {
            return json([
                'code'      => '404',
                'message'   => '更新密码失败'
            ]);
        }
    }

    /**
     * 管理员退出api接口
     * @return \think\response\Json
     */
    public function logout() {
        Session::delete('admin');
        Session::delete('admin_token');
        if (Session::get('admin') == null && Session::get('admin_token') == null) {
            return json([
                'code'      => '200',
                'message'   => '管理员退出成功'
            ]);
        } else {
            return json([
                'code'      => '401',
                'message'   => '管理员退出失败'
            ]);
        }
    }
}
