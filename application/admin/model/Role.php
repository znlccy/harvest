<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 12:03
 * Comment: 角色模型
 */
namespace app\admin\model;

class Role extends BasisModel {

    /**
     * 自动写入和读取时间
     * @var string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 关联的数据表
     * @var string
     */
    protected $table = 'tb_role';

    /**
     * 关联的权限表
     * @return \think\model\relation\BelongsToMany
     */
    public function permission() {
        return $this->belongsToMany('Permission', 'tb_role_permission', 'permission_id', 'role_id');
    }

}