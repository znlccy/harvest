<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 13:35
 * Comment: 短信验证码模型
 */
namespace app\index\model;

class Sms extends BasicModel {

    /**
     * @var string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 关联的数据表
     * @var string
     */
    protected $table = 'tb_sms';
}