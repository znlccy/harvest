<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 10:55
 * Comment: 角色验证器
 */

namespace app\admin\validate;

class Role extends BasisValidate {

    //验证规则
    protected $rule = [
        'id'            => 'number',
        'name'          => 'max:255',
        'description'   => 'max:255',
        'parent_id'     => 'number',
        'level'         => 'number',
        'status'        => 'number',
        'sort'          => 'number',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    //验证字段
    protected $field = [
        'id'            => '角色主键',
        'name'          => '角色名称',
        'description'   => '角色描述',
        'parent_id'     => '角色父节点',
        'level'         => '角色等级',
        'status'        => '角色状态',
        'sort'          => '角色排序',
        'create_start'  => '角色创建起始时间',
        'create_end'    => '角色创建截止时间',
        'update_start'  => '角色更新起始时间',
        'update_end'    => '角色更新截止时间',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    //验证场景
    protected $scene = [
        'entry'         => ['id' => 'number', 'name' => 'max:255', 'status' => 'number', 'parent_id' => 'number', 'description' => 'max:255', 'sort' => 'number', 'level' => 'number', 'create_start' => 'date', 'create_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'page_size' => 'number', 'jump_page' => 'number'],
        'save'          => ['id'=> 'number', 'name' => 'require|max:255', 'description' => 'require|max:255', 'parent_id' => 'number', 'level' => 'require|number', 'status' => 'require|number', 'sort' => 'require|number'],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number'],
        'assign_role_permission' => ['id' => 'require|number', 'permission_id' => 'require|array'],
        'get_role_permission' => ['id' => 'require|number']
    ];
}