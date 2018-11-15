<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 10:52
 * Comment: 权限模型
 */

namespace app\admin\model;

class Permission extends BasisModel {

    /* 读存时间 */
    protected $autoWriteTimestamp = 'datetime';

    /* 对应的表 */
    protected $table = 'tb_permission';

    /* 关联的表 */
    public function roles() {
        return $this->belongsToMany('Role', 'tb_role_permission', 'role_id', 'permission_id');
    }
}