<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 10:53
 * Comment: 管理员验证器
 */

namespace app\admin\validate;

class Admin extends BasisValidate {

    //手机验证正则表达式
    protected $regex = [ 'mobile' => '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/'];

    //验证规则
    protected $rule = [
        'id'            => 'number',
        'mobile'        => 'length:11|regex:mobile',
        'status'        => 'number',
        'real_name'     => 'max:80',
        'register_start'=> 'date',
        'register_end'  => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'login_start'   => 'date',
        'login_end'     => 'date',
        'login_ip'      => 'max:255',
        'create_ip'     => 'max:255',
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    //验证字段
    protected $field = [
        'id'            => '管理员主键',
        'mobile'        => '管理员手机号码',
        'status'        => '管理员状态',
        'real_name'     => '管理员真实姓名',
        'register_start'=> '管理员注册起始时间',
        'register_end'  => '管理员注册截止时间',
        'update_start'  => '管理员更新起始时间',
        'update_end'    => '管理员更新截止时间',
        'login_start'   => '管理员登陆起始时间',
        'login_end'     => '管理员登陆截止时间',
        'login_ip'      => '管理员登陆ip地址',
        'create_ip'     => '管理员创建ip地址',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    //验证场景
    protected $scene = [
        'mobile_login'       => ['mobile' => 'require|length:11|regex:mobile', 'code' => 'require|length:6|number'],
        'account_login'      => ['mobile' => 'require|length:11|regex:mobile', 'password' => 'require|length:8,25|alphaDash'],
        'entry'              => ['id' => 'number', 'mobile' => 'length:11|regex:mobile', 'status' => 'number', 'real_name' => 'max:80', 'register_start' => 'date', 'register_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'login_start' => 'date', 'login_end' => 'date', 'login_ip' => 'max:255', 'create_ip' => 'max:255', 'page_size' => 'number', 'jump_page' => 'number'],
        'detail'             => ['id' => 'require|number'],
        'delete'             => ['id' => 'require|number'],
        'change_password'    => ['password' => 'require|length:8,25|alphaDash', 'confirm_pass' => 'require|length:8,25|confirm:password']
    ];
}