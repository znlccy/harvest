<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:31
 * Comment: 管理员控制器
 */

namespace app\admin\controller;

use think\Request;
use app\admin\model\Admin as AdminModel;
use app\admin\validate\Admin as AdminValidate;

class Admin extends BasisController {

    /* 声明管理员模型 */
    protected $admin_model;

    /* 声明管理员验证器 */
    protected $admin_validate;

    /* 声明管理员分页器 */
    protected $admin_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->admin_model = new AdminModel();
        $this->admin_validate = new AdminValidate();
        $this->admin_page = config('pagination');
    }

    /* 管理员列表 */
    public function listing() {

    }

    /* 管理员添加更新 */
    public function save() {

    }

    /* 管理员详情 */
    public function detail() {

    }

    /* 管理员删除 */
    public function delete() {

    }

}