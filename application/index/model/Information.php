<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 18:18
 * Comment: 信息模型
 */
namespace app\index\model;

use think\Model;

class Information extends Model {

    /**
     * 关联的数据表
     * @var string
     */
    protected $table = 'tb_information';

    /* 设置富文本 */
    public function setRichTextAttr($value)
    {
        return htmlspecialchars($value);
    }

    /* 获取富文本 */
    public function getRichTextAttr($value)
    {
        return htmlspecialchars_decode($value);
    }
    public function user()
    {
        return $this->hasOne('User', 'id', 'publisher');
    }
}
