<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 14:33
 * Comment: 成员验证器
 */

namespace app\admin\validate;

class User extends BasisValidate {

    //手机验证正则表达式
    protected $regex = [ 'mobile' => '/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/'];

    //座机验证正则表达式
    protected $regexp = ['phone' => '/^(0[0-9]{2,3}/-)?([2-9][0-9]{6,7})+(/-[0-9]{1,4})?$/'];

    //验证规则
    protected $rule = [
        'page_size'     => 'number',
        'jump_page'     => 'number',
        'id'            => 'number',
        'status'        => 'number',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'login_start'   => 'date',
        'login_end'     => 'date',
        'mobile'        => 'length:11|unique:tb_user',
        'username'      => 'max:50',
        'email'         => 'email',
        'company'       => 'max:80',
        'career'        => 'max:120',
        'occupation'    => 'max:200'
    ];

    //验证领域
    protected $field = [
        'page_size'     => '每页显示多少条数据',
        'jump_page'     => '跳转至第几页',
        'id'            => '用户主键',
        'status'        => '用户状态',
        'create_start'  => '创建起始时间',
        'create_end'    => '创建截止时间',
        'login_start'   => '登录起始时间',
        'login_end'     => '登录截止时间',
        'mobile'        => '手机号',
        'password'      => '密码',
        'confirm_pass'  => '确认密码',
    ];

    //验证场景
    protected $scene = [
        'entry' => ['id' => 'number', 'mobile'=> 'length:11|regex:mobile', 'page_size' => 'number', 'jump_page' => 'number', 'auditor' => 'number', 'status' => 'number', 'create_start' => 'date', 'create_end' => 'date', 'login_start' => 'date', 'login_end' => 'date'],
        'save' => ['id' => 'number', 'mobile' => 'require|length:11|regex:mobile|unique:tb_user', 'password' => 'require|alphaDash|length:8,25', 'confirm_pass' => 'require|alphaDash|length:8,25|confirm:password', 'username' => 'require|max:255', 'email' => 'require|email', 'company' => 'require、max:255', 'occupation' => 'require|max:255', 'status' => 'require|number|in:0,1'],
        'update' => ['id' => 'require|number','mobile' => 'regex:mobile|length:8', 'username' => 'max:255', 'status' => 'number', 'email' => 'email', 'company' => 'max:255', 'career' => 'max:255', 'occupation' => 'max:255'],
        'detail' => ['id' => 'require|number'],
        'delete' => ['id' => 'require|number'],
    ];
}