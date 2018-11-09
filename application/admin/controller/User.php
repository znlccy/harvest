<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:34
 * Comment: 用户控制器
 */

namespace app\admin\controller;

use think\Request;
use app\admin\model\User as UserModel;
use app\admin\validate\User as UserValidate;

class User extends BasisController {

    /* 声明用户模型 */
    protected $user_model;

    /* 声明用户验证器 */
    protected $user_validate;

    /* 声明用户分页器 */
    protected $user_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->user_model = new UserModel();
        $this->user_validate = new UserValidate();
        $this->user_page = config('pagination');
    }

    /* 用户列表 */
    public function listing() {

    }

    /* 用户添加更新 */
    public function save() {

    }

    /* 用户详情 */
    public function detail() {

    }

    /* 用户删除 */
    public function delete() {

    }

}