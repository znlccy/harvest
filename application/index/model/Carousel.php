<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/14
 * Time: 11:11
 * Comment: 轮播模型
 */

namespace app\index\model;

class Carousel extends BasicModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_carousel';
}