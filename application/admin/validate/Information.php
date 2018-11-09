<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 15:45
 * Comment: 消息验证器
 */

namespace app\admin\validate;

class Information extends BasisValidate {

    /* 验证规则 */
    protected $rule = [
        'id'            => 'number',
    ];

    /* 验证字段 */
    protected $field = [
        'id'            => '消息主键'
    ];

    /* 验证场景 */
    protected $scene = [
        'listing'       => [],
        'save'          => [],
        'detail'        => ['id' => 'require|number'],
        'delete'        => ['id' => 'require|number']
    ];

}