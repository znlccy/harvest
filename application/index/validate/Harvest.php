<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 13:05
 * Comment: 成果验证器
 */

namespace app\index\validate;

class Harvest extends BasicValidate {

    /* 验证规则 */
    protected $rule = [
        'page_size'     => 'number',
        'jump_page'     => 'number',
        'id'            => 'number',
    ];

    /* 验证字段 */
    protected $field = [
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页',
        'id'            => '成果主键',
    ];

    /* 验证场景 */
    protected $scene = [
        'index'     => ['page_size' => 'number', 'jump_page' => 'number'],
        'detail'    => ['id' => 'require|number'],
    ];

}