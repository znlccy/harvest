<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:31
 * Comment: 权限控制器
 */

namespace app\admin\controller;

use think\Request;
use app\admin\model\Permission as PermissionModel;
use app\admin\validate\Permission as PermissionValidate;

class Permission extends BasisController {

    /* 声明权限模型 */
    protected $permission_model;

    /* 声明权限验证器 */
    protected $permission_validate;

    /* 声明权限分页 */
    protected $permission_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->permission_model = new PermissionModel();
        $this->permission_validate = new PermissionValidate();
        $this->permission_page = config('pagination');
    }

    /* 权限列表 */
    public function listing() {

    }

    /* 权限添加更新 */
    public function save() {

    }

    /* 权限详情 */
    public function detail() {

    }

    /* 权限删除 */
    public function delete() {

    }

}