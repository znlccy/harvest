<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:31
 * Comment: 角色控制器
 */

namespace app\admin\controller;

use think\Request;
use app\admin\model\Role as RoleModel;
use app\admin\validate\Role as RoleValidate;

class Role extends BasisController {

    /* 声明角色模型 */
    protected $role_model;

    /* 声明角色验证器 */
    protected $role_validate;

    /* 声明角色分页 */
    protected $role_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->role_model = new RoleModel();
        $this->role_validate = new RoleValidate();
        $this->role_page = config('pagination');
    }

    /* 角色列表 */
    public function listing() {

    }

    /* 角色添加更新 */
    public function save() {

    }

    /* 角色详情 */
    public function detail() {

    }

    /* 角色删除 */
    public function delete() {

    }

}