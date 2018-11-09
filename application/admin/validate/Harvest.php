<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 15:04
 * Comment: 成果验证器
 */

namespace app\admin\validate;

class Harvest extends BasisValidate {

    /* 验证规则 */
    protected $rule = [
        'id'            => 'number',
        'name'          => 'max:255',
        'description'   => 'max；500',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number',

    ];

    /* 验证字段 */
    protected $field = [
        'id'            => '成果主键',
        'name'          => '成果名称',
        'description'   => '成果描述',
        'create_start'  => '成果创建起始时间',
        'create_end'    => '成果创建截止时间',
        'update_start'  => '成果更新起始时间',
        'update_end'    => '成果更新截止时间',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页',
    ];

    /* 验证场景 */
    protected $scene = [
        'listing'       => ['id' => 'number','name' => 'max:255', 'description' => 'max:500', 'create_start' => 'date', 'create_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'page_size' => 'number', 'jump_page' => 'number'],
        'save'          => ['id' => 'number', 'name' => 'require|max:255', 'description' => 'require|max:500', 'picture' => 'require', 'rich_text' => 'max:800'],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number']
    ];
}