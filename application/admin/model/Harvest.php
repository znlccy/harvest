<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 14:57
 * Comment: 成果模型
 */

namespace app\admin\model;

class Harvest extends BasisModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_harvest';

    /* 加密富文本 */
    public function setRichTextAttr($value) {
        return htmlspecialchars($value);
    }

    /* 解密富文本 */
    public function getRichTextAttr($value) {
        return htmlspecialchars_decode($value);
    }
}