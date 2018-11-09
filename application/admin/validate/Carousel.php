<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:37
 * Comment: 轮播验证器
 */

namespace app\admin\validate;

class Carousel extends BasisValidate {

    /* 验证规则 */
    protected $rule = [
        'id'            => 'number',
        'title'         => 'max:255',
        'url'           => 'url',
        'sort'          => 'number',
        'status'        => 'number|in:0,1',
        'description'   => 'max:500',
        'create_start'  => 'date',
        'create_end'    => 'date',
        'update_start'  => 'date',
        'update_end'    => 'date',
        'publish_start' => 'date',
        'publish_end'   => 'date',
        'publish_time'  => 'date',
        'page_size'     => 'number',
        'jump_page'     => 'number'
    ];

    /* 验证字段 */
    protected $field = [
        'id'            => '轮播主键',
        'title'         => '轮播标题',
        'url'           => '轮播URL',
        'sort'          => '轮播排序',
        'status'        => '轮播状态',
        'description'   => '轮播描述',
        'create_start'  => '轮播创建起始时间',
        'create_end'    => '轮播创建截止时间',
        'update_start'  => '轮播更新起始时间',
        'update_end'    => '轮播更新截止时间',
        'publish_start' => '轮播发布起始时间',
        'publish_end'   => '轮播发布截止时间',
        'publish_time'  => '轮播发布时间',
        'picture'       => '轮播图片',
        'page_size'     => '分页大小',
        'jump_page'     => '跳转页'
    ];

    /* 验证场景 */
    protected $scene = [
        'listing'   => ['id' => 'number', 'title' => 'max:255', 'url' => 'url', 'sort' => 'number', 'status' => 'number', 'description' => 'max:500', 'create_start' => 'date', 'create_end' => 'date', 'update_start' => 'date', 'update_end' => 'date', 'publish_start' => 'date', 'publish_end' => 'date', 'page_size' => 'number', 'jump_page' => 'number'],
        'save'      => ['id' => 'number', 'title' => 'require|max:255', 'url' => 'require|url', 'sort' => 'require|number', 'picture' => 'require|fileExt:jpg,jpeg', 'status' => 'require|number', 'description' => 'require|max:500', 'publish_time' => 'require|date'],
        'detail'    => ['id' => 'require|number'],
        'delete'    => ['id' => 'require|number'],
    ];
}