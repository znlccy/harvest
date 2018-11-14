<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/14
 * Time: 11:06
 */

namespace app\index\validate;

class Index extends BasicValidate {

    /* 验证规则 */
    protected $rule = [
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    /* 验证字段 */
    protected $field = [
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    /* 验证场景 */
    protected $scene = [
        'index'         => ['page_size' => 'number', 'jump_page' => 'number']
    ];

}