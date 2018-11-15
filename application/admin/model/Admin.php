<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 10:52
 * Comment: 管理员模型
 */

namespace app\admin\model;

class Admin extends BasisModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_admin';

    /* 关联的表 */
    public function role() {
        return $this->belongsToMany('Role', 'tb_admin_role', 'role_id', 'user_id');
    }

}