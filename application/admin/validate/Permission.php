<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 12:03
 * Comment: 权限验证器
 */
namespace app\admin\validate;

class Permission extends BasisValidate {

    //验证规则
    protected $rule = [
        'id'            => 'number',
        'status'        => 'number',
        'name'          => 'number',
        'path'          => 'max:255',
        'pid'           => 'number',
        'description'   => 'max:255',
        'sort'          => 'number',
        'level'         => 'number',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    //验证字段
    protected $field = [
        'id'            => '权限主键',
        'status'        => '权限状态',
        'name'          => '权限名称',
        'path'          => '权限路径',
        'pid'           => '权限父节点',
        'description'   => '权限描述',
        'sort'          => '权限排序',
        'level'         => '权限等级',
        'create_start'  => '权限创建起始时间',
        'create_end'    => '权限创建截止时间',
        'update_start'  => '权限更新起始时间',
        'update_end'    => '权限更新截止时间',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    //验证场景
    protected $scene = [
        'entry'         => ['id' => 'number', 'status' => 'number', 'name' => 'max:255', 'path' => 'max:255', 'pid' => 'number', 'description' => 'max:255', 'sort' => 'number|min:1', 'level' => 'number', 'create_start' => 'date', 'create_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'page_size' => 'number', 'jump_page' => 'number'],
        'save'          => ['id' => 'number'],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number'],
    ];
}