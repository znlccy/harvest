<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 14:46
 * Comment: 消息控制器
 */

namespace app\admin\model;

class Information extends BasisModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_information';

    /* 关联的表 */
    public function user() {
        return $this->belongsToMany('User', 'tb_user_info','user_id','info_id');
    }

    /* 设置富文本 */
    public function setRichTextAttr($value) {
        return htmlspecialchars($value);
    }

    /* 获取富文本 */
    public function getRichTextAttr($value) {
        return htmlspecialchars_decode($value);
    }

}