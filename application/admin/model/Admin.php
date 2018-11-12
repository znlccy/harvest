<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 12:03
 * Comment: 管理员模型
 */
namespace app\admin\model;

class Admin extends BasisModel {

    /**
     * 自动写入读取时间
     * @var string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 关联的数据表
     * @var string
     */
    protected $table = 'tb_admin';

    /**
     * 关联用户角色
     * @return \think\model\relation\BelongsToMany
     */
    public function role() {
        return $this->belongsToMany('Role', 'tb_admin_role', 'role_id', 'user_id');
    }
}