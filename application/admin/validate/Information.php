<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20
 * Time: 18:52
 * Comment: 消息验证器
 */

namespace app\admin\validate;

class Information extends BasisValidate {

    //验证规则
    protected $rule = [
        'id'            => 'number',
        'status'        => 'number',
        'title'         => 'max:255',
        'publisher'     => 'max:255',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    //验证消息
    protected $message = [

    ];

    //验证字段
    protected $field = [
        'id'            => '消息主键',
        'status'        => '消息状态',
        'title'         => '消息标题',
        'publisher'     => '发布人',
        'create_start'  => '消息创建起始时间',
        'create_end'    => '消息创建截止时间',
        'update_start'  => '消息更新开始时间',
        'update_end'    => '消息更新截止时间',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    //验证场景
    protected $scene = [
        'entry'         => ['id' => 'number', 'title' => 'max:180', 'publisher' => 'max:120', 'status' => 'number', 'create_start' => 'date', 'create_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'page_size' => 'number', 'jump_page' => 'number'],
        'save'          => ['id' => 'number', 'title' => 'max:180', 'status' => 'require|number', 'publish_time' => 'date'],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number']
    ];
}