<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:23
 * Comment: 角色模型
 */

namespace app\admin\model;

class Role extends BasisModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_role';

    /* 关联的表 */
    public function permissions() {

    }
}