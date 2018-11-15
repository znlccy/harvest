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

    /* 验证规则 */
    protected $rule = [
        'id'            => 'number',
        'type'          => 'number|in:1,2',
        'status'        => 'number|in:0,1',
        'username'      => 'max:60',
        'mobile'        => 'length:11|unique:tb_user|regex:mobile',
        'duty'          => 'max:400',
        'department'    => 'max:400',
        'phone'         => 'length:13|regex:phone',
        'wechat'        => 'max:200',
        'email'         => 'email',
        'link'          => 'max:300',
        'enterprise'    => 'max:300',
        'introduce'     => 'max:400',
        'industry'      => 'max:500',
        'capital'       => 'max:400',
        'revenue'       => 'number',
        'assets'        => 'number',
        'address'       => 'max:500',
        'company'       => 'max:500',
        'location'      => 'max:400',
        'invest_industry'=> 'max:300',
        'invest_address'=> 'max:400',
        'text_domain'   => 'max:500',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number',
    ];

    /* 验证消息 */
    protected $field = [
        'id'            => '用户主键',
        'type'          => '用户类型',
        'status'        => '用户状态',
        'username'      => '用户状态',
        'mobile'        => '用户手机',
        'duty'          => '用户职务',
        'department'    => '用户部门',
        'phone'         => '用户座机',
        'wechat'        => '用户微信/QQ',
        'email'         => '用户电子邮件',
        'link'          => '用户联系地址',
        'enterprise'    => '企业名称',
        'introduce'     => '企业介绍',
        'industry'      => '所属行业',
        'capital'       => '注册资金',
        'revenue'       => '是否营收',
        'assets'        => '当前净资产',
        'address'       => '注册地址',
        'company'       => '公司名称',
        'location'      => '公司所在地址',
        'invest_industry'=> '投资行业',
        'invest_address'=> '投资地区',
        'text_domain'   => '文本域',
        'create_start'  => '用户创建起始时间',
        'create_end'    => '用户创建截止时间',
        'update_start'  => '用户更新起始时间',
        'update_end'    => '用户更新截止时间',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    /* 验证场景 */
    protected $scene = [
        'listing'       => ['id' => 'number', 'type' => 'number', 'status' => 'number', 'username' => 'max:80', 'page_size' => 'number', 'jump_page' => 'number'],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number'],
        'auditor'       => ['id' => 'require|number', 'status' => 'require|number']
    ];
}