<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 13:35
 * Comment: 通知消息模型
 */

namespace app\admin\model;

class Information extends BasisModel {

    /**
     * 自动写入和读取时间
     * @var string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 关联的数据表
     * @var string
     */
    protected $table = 'tb_information';

}