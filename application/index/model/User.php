<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 18:18
 * Comment: 前台用户模型
 */
namespace app\index\model;

class User extends BasicModel {

    /**
     * 自动写入和读取时间
     * @var string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * @var string
     */
    protected $table = 'tb_user';

    /* 关联的表 */
    public function products() {
        return $this->belongsToMany('Product', 'tb_user_product', 'product_id', 'user_id');
    }
}