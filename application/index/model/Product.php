<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/15
 * Time: 16:50
 * Comment: 成果模型
 */

namespace app\index\model;

class Product extends BasicModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_product';

    /* 关联的表 */
    public function users() {

    }
}