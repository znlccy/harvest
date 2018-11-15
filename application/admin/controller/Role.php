<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/6
 * Time: 10:52
 * Comment： 角色控制器
 */

namespace app\admin\controller;

use app\admin\model\Role as RoleModel;
use app\admin\model\RolePermission as RolePermissionModel;
use app\admin\model\Permission as PermissionModel;
use app\admin\response\Code;
use app\admin\validate\Role as RoleValidate;
use gmars\rbac\Rbac;
use think\Request;

class Role extends BasisController {

    /* 声明角色模型 */
    protected $role_model;

    /* 声明角色权限模型 */
    protected $role_permission_model;

    /* 声明权限模型 */
    protected $permission_model;

    /* 声明角色验证器 */
    protected $role_validate;

    /* 声明角色分页 */
    protected $role_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->role_model = new RoleModel();
        $this->role_permission_model = new RolePermissionModel();
        $this->permission_model = new PermissionModel();
        $this->role_validate = new RoleValidate();
        $this->role_page = config('pagination');
    }

    /* 角色列表 */
    public function listing() {
        /* 获取客户端提供的数据 */
        $id = request()->param('id');
        $parent_id = request()->param('parent_id');
        $status = request()->param('status');
        $name = request()->param('name');
        $description = request()->param('description');
        $sort = request()->param('sort');
        $level = request()->param('level');
        $create_start = request()->param('create_start');
        $create_end = request()->param('create_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $page_size = request()->param('page_size', $this->role_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->role_page['JUMP_PAGE']);

        //验证数据
        $validate_data = [
            'id'             => $id,
            'status'         => $status,
            'name'           => $name,
            'parent_id'      => $parent_id,
            'description'    => $description,
            'sort'           => $sort,
            'level'          => $level,
            'create_start'   => $create_start,
            'create_end'     => $create_end,
            'update_start'   => $update_start,
            'update_end'     => $update_end,
            'page_size'      => $page_size,
            'jump_page'      => $jump_page,
        ];

        //验证结果
        $result   = $this->role_validate->scene('entry')->check($validate_data);
        if (!$result) {
            return $this->return_message(Code::INVALID, $this->role_validate->getError());
        }

        //筛选条件
        $conditions = [];

        if ($id) {
            $conditions['id'] = $id;
        }
        if (is_null($status)) {
            $conditions['status'] = ['in',[0,1]];
        } else {
            switch ($status) {
                case 0:
                    $conditions['status'] = $status;
                    break;
                case 1:
                    $conditions['status'] = $status;
                    break;
                default:
                    break;
            }
        }
        if ($name) {
            $conditions['name'] = ['like', '%' . $name . '%'];
        }
        if ($parent_id) {
            $conditions['parent_id'] = $parent_id;
        }
        if ($description) {
            $conditions['description'] = ['like', '%' . $description . '%'];
        }
        if ($sort) {
            $conditions['sort'] = $sort;
        }
        if ($level) {
            $conditions['level'] = $level;
        }
        if ($create_start || $create_end) {
            $conditions['create_time'] = ['between time', [$create_start, $create_end]];
        }
        if ($update_start || $update_end) {
            $conditions['update_time'] = ['between time', [$update_start, $update_end]];
        }

        //返回数据
        $role = $this->role_model->where($conditions)
            ->order('sort', 'desc')
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($role) {
            return $this->return_message(Code::SUCCESS, '获取角色列表成功', $role);
        } else {
            return $this->return_message(Code::FAILURE, '获取角色列表失败');
        }
    }

    /* 添加更新角色 */
    public function save() {
        /* 获取客户端提供的 */
        $id = request()->param('id');
        $name = request()->param('name');
        $parent_id = request()->param('parent_id', 1);
        $level = request()->param('level', 1);
        $description = request()->param('description');
        $status = request()->param('status',1);
        $sort = request()->param('sort', 0);

        //验证数据
        $validate_data = [
            'id'            => $id,
            'name'          => $name,
            'description'   => $description,
            'status'        => $status,
            'parent_id'     => $parent_id,
            'sort'          => $sort,
            'level'         => $level,
            'create_time'   => date('Y-m-d H:i:s', time()),
        ];

        //验证结果
        $result   = $this->role_validate->scene('save')->check($validate_data);
        if (!$result) {
            return $this->return_message(Code::INVALID, $this->role_validate->getError());
        }

        $rbac = new Rbac();
        /* 封装用户数据为数组 */

        if (!empty($id)) {
            $update_data = [
                'id'            => $id,
                'name'          => $name,
                'description'   => $description,
                'status'        => $status,
                'parent_id'     => $parent_id,
                'sort'          => $sort,
                'level'         => $level,
                'update_time'   => date('Y-m-d H:i:s', time()),
            ];
            $update_result = $rbac->editRole($update_data);
            if($update_result) {
                return $this->return_message(Code::SUCCESS, '更新角色成功');
            }
        } else {
            $insert_result = $rbac->createRole($validate_data);
            if($insert_result) {
                return $this->return_message(Code::SUCCESS, '添加角色成功');
            }
        }
    }

    /* 角色详情 */
    public function detail() {
        //获取客户端提交过来的数据
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'        => $id
        ];

        //验证结果
        $result = $this->role_validate->scene('detail')->check($validate_data);
        if (!$result) {
            return json([
                'code'      => '401',
                'message'   => $this->role_validate->getError()
            ]);
        }

        //返回数据
        $role = $this->role_model->where('id', $id)->find();
        if ($role) {
            return $this->return_message(Code::SUCCESS, '获取角色详情成功', $role);
        } else {
            return $this->return_message(Code::FAILURE, '获取角色详情失败');
        }
    }

    /* 删除角色 */
    public function delete() {
        //获取客户端提交过来的数据
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'        => $id
        ];

        //验证结果
        $result = $this->role_validate->scene('delete')->check($validate_data);
        if (!$result) {
            return $this->return_message(Code::INVALID, $this->role_validate->getError());
        }

        //返回结果
        $manual_result = $this->role_model->where('id', $id)->delete();
        if ($manual_result) {
            return $this->return_message(Code::SUCCESS,'删除数据成功');
        } else {
            return $this->return_message(Code::FAILURE, '删除数据失败');
        }
    }

    /* 分配角色权限 */
    public function assign_role_permission() {

        /* 获取客户端提交过来的角色主键 */
        $role_id = request()->param('id');
        $permission_id = request()->param('permission_id/a');

        //验证数据
        $validate_data = [
            'id'            => $role_id,
            'permission_id' => $permission_id
        ];

        //验证结果
        $result   = $this->role_validate->scene('assign_role_permission')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->role_validate->getError()]);
        }

        $role = $this->role_permission_model->where('role_id', '=', $role_id)->find();
        //如果有了就更新没有就添加
        if ($role) {
            $delete = $this->role_permission_model->where('role_id', '=', $role_id)->delete();

            $rbacObj = new Rbac();
            $assign_result = $rbacObj->assignRolePermission($role_id, $permission_id);
            if ($assign_result) {
                return $this->return_message(Code::SUCCESS, '分配权限成功');
            } else {
                return $this->return_message(Code::FAILURE, '分配权限失败');
            }
        } else {
            $rbacObj = new Rbac();
            $assign_result = $rbacObj->assignRolePermission($role_id, $permission_id);
            if ($assign_result) {
                return $this->return_message(Code::SUCCESS, '分配权限成功');
            } else {
                return $this->return_message(Code::FAILURE, '分配权限失败');
            }
        }
    }

    /* 获得角色权限 */
    public function get_role_permission() {
        /* 获取客户端提供的数据 */
        $id = request()->param('id');

        //验证数据
        $validate_data = [
            'id'    => $id
        ];

        //验证结果
        $result   = $this->role_validate->scene('get_role_permission')->check($validate_data);
        if (!$result) {
            return json(['code' => '401', 'message' => $this->role_validate->getError()]);
        }

        $user_role = $this->role_permission_model-> where('role_id', '=', $id)->select();

        $user_role_list = [];
        foreach ( $user_role as $value ){
            $user_role_list[] = $value['permission_id'];
        }

        $role_data = $this->permission_model->select();
        for ( $i = 0; $i < count($role_data); $i++ ){
            if (in_array($role_data[$i]['id'], $user_role_list)) {
                $role_data[$i]['role_status'] = 1;
            } else {
                $role_data[$i]['role_status'] = 0;
            }
        }

        $role_data = $this->build_trees($role_data, 0);

        if ($role_data) {
            return $this->return_message(Code::SUCCESS, '获取角色权限成功',$role_data);
        } else {
            return $this->return_message(Code::FAILURE, '获取角色权限失败');
        }
    }

    /* 声明树机构 */
    public function build_trees($data, $pid) {
        $tree_nodes = array();
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pid)
            {
                $v['child'] = $this->build_trees($data, $v['id']);
                $tree_nodes[] = $v;
            }
        }
        return $tree_nodes;
    }
}