<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:34
 * Comment: 用户控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
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

        /* 接收参数 */
        $id = request()->param('id');
        $type = request()->param('type');
        $status = request()->param('status');
        $username = request()->param('username');
        $mobile = request()->param('mobile');
        $duty = request()->param('duty');
        $department = request()->param('department');
        $phone = request()->param('phone');
        $wechat = request()->param('wechat');
        $email = request()->param('email');
        $link = request()->param('link');
        /* 创业者筛选字段 */
        $enterprise = request()->param('enterprise');
        $introduce = request()->param('introduce');
        $revenue = request()->param('revenue');
        $address = request()->param('address');
        /* 合作者筛选字段 */
        $company = request()->param('company');
        $location = request()->param('location');
        $invest_industry = request()->param('invest_industry');
        $invest_address = request()->param('invest_address');
        $text_domain = request()->param('text_domain');
        $create_start = request()->param('create_start');
        $create_end = request()->param('create_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $page_size = request()->param('page_size',$this->user_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->user_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'type'          => $type,
            'status'        => $status,
            'username'      => $username,
            'mobile'        => $mobile,
            'duty'          => $duty,
            'department'    => $department,
            'phone'         => $phone,
            'wechat'        => $wechat,
            'email'         => $email,
            'link'          => $link,
            'enterprise'    => $enterprise,
            'introduce'     => $introduce,
            'revenue'       => $revenue,
            'address'       => $address,
            'company'       => $company,
            'location'      => $location,
            'invest_industry'=> $invest_industry,
            'invest_address'=> $invest_address,
            'text_domain'   => $text_domain,
            'create_start'  => $create_start,
            'create_end'    => $create_end,
            'update_start'  => $update_start,
            'update_end'    => $update_end,
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 筛选条件 */
        $conditions = [];

        if ($id) {
            $conditions['id'] = $id;
        }

        if ($type) {
            $conditions['type'] = $type;
        }

        if (is_null($status)) {
            $conditions['status'] = ['in',[0,1,2]];
        } else {
            switch ($status) {
                case 0:
                    $conditions['status'] = $status;
                    break;
                case 1:
                    $conditions['status'] = $status;
                    break;
                case 2:
                    $conditions['status'] = $status;
                    break;
                default:
                    break;
            }
        }

        if ($username) {
            $conditions['username'] = ['like', '%'. $username .'%'];
        }

        if ($mobile) {
            $conditions['mobile'] = ['like', '%' . $mobile .'%'];
        }

        if ($duty) {
            $conditions['duty'] = ['like', '%' . $duty . '%'];
        }

        if ($department) {
            $conditions['department'] = ['like', '%' . $department . '%'];
        }

        if ($phone) {
            $conditions['phone'] = ['like', '%' . $phone .'%'];
        }

        if ($wechat) {
            $conditions['wechat'] = ['like', '%' . $wechat . '%'];
        }

        if ($email) {
            $conditions['email'] = ['like', '%' .$email . '%'];
        }

        if ($link) {
            $conditions['link'] = ['like', '%' . $link . '%'];
        }

        if ($enterprise) {
            $conditions['enterprise'] = ['like', '%' . $enterprise . '%'];
        }

        if ($introduce) {
            $conditions['introduce'] = ['like', '%' . $introduce . '%'];
        }

        if (is_null($revenue)) {
            $conditions['revenue'] = ['in',[0,1]];
        } else {
            switch ($revenue) {
                case 0:
                    $conditions['revenue'] = $revenue;
                    break;
                case 1:
                    $conditions['revenue'] = $revenue;
                    break;
                default:
                    break;
            }
        }

        if ($address) {
            $conditions['address'] = ['like', '%' . $address . '%'];
        }

        if ($company) {
            $conditions['company'] = ['like', '%' . $company . '%'];
        }

        if ($location) {
            $conditions['location'] = ['like', '%' . $location . '%'];
        };

        if ($invest_industry) {
            $conditions['invest_industry'] = ['like', '%' . $invest_industry . '%'];
        }

        if ($invest_address) {
            $conditions['invest_address'] = ['like', '%' . $invest_address . '%'];
        }

        if ($text_domain) {
            $conditions['text_domain'] = ['like', '%' . $text_domain . '%'];
        }

        if ($create_start && $create_end) {
            $conditions['create_time'] = ['between time', [$create_start, $create_end]];
        }

        if ($update_start && $update_end) {
            $conditions['update_time'] = ['between time', [$update_start, $update_end]];
        }

        /* 返回结果 */
        $user = $this->user_model
            ->where($conditions)
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($user) {
            return $this->return_message(Code::SUCCESS, '获取用户列表成功', $user);
        } else {
            return $this->return_message(Code::FAILURE, '获取用户列表失败');
        }
    }


    /* 用户添加更新 */
    public function save() {

        /* 接收参数 */
        $id = request()->param('id');
        $type = intval(request()->param('type'));
        $status = request()->param('status');
        $username = request()->param('username');
        $mobile = request()->param('mobile');
        $duty = request()->param('duty');
        $department = request()->param('department');
        $phone = request()->param('phone');
        $wechat = request()->param('wechat');
        $password = request()->param('password');
        $confirm_password = request()->param('confirm_password');
        $email = request()->param('email');
        $link = request()->param('link');

        switch ($type) {
            case 1:
                $enterprise = request()->param('enterprise');
                $introduce = request()->param('introduce');
                $industry = request()->param('industry');
                $capital = request()->param('capital');
                $revenue = request()->param('revenue');
                $assets = request()->param('assets');
                $address = request()->param('address');

                $validate_entrepreneur = [
                    'id'            => $id,
                    'type'          => $type,
                    'status'        => $status,
                    'enterprise'    => $enterprise,
                    'introduce'     => $introduce,
                    'industry'      => $industry,
                    'capital'       => $capital,
                    'revenue'       => $revenue,
                    'assets'        => $assets,
                    'address'       => $address,
                    'username'      => $username,
                    'mobile'        => $mobile,
                    'password'      => md5($password),
                    'confirm_password'=> md5($confirm_password),
                    'duty'          => $duty,
                    'department'    => $department,
                    'phone'         => $phone,
                    'wechat'        => $wechat,
                    'email'         => $email,
                    'link'          => $link
                ];

                /* 验证规则 */
                $validate_entrepreneur_rule = [
                    'id'            => 'number',
                    'type'          => 'require|number|in:1,2',
                    'status'        => 'require|number|in:0,1',
                    'enterprise'    => 'require|max:255',
                    'introduce'     => 'require|max:500',
                    'industry'      => 'require|max:255',
                    'capital'       => 'require|number',
                    'revenue'       => 'require|number|in:0,1',
                    'assets'        => 'require|number',
                    'username'      => 'require|max:120',
                    'password'      => 'require|alphaDash',
                    'confirm_password'=> 'confirm:password',
                    'mobile'        => 'require|max:32|unique:tb_user',
                    'duty'          => 'require|max:255',
                    'department'    => 'require|max:300',
                    'phone'         => 'require|max:60',
                    'wechat'        => 'require|max:60',
                    'email'         => 'require|email',
                    'link'          => 'require|max:800',
                ];

                /* 验证结果 */
                $result = $this->user_validate->check($validate_entrepreneur, $validate_entrepreneur_rule);

                if (true != $result) {
                    return $this->return_message(Code::INVALID, $this->user_validate->getError());
                }

                /* 返回结果 */
                if (empty($id)) {
                    if (!empty($validate_entrepreneur['confirm_password'])) {
                        unset($validate_entrepreneur['confirm_password']);
                    }
                    if ($validate_entrepreneur['status'] !== 0) {
                        $validate_entrepreneur['status'] = 0;
                    }
                    $entrepreneur = $this->user_model->save($validate_entrepreneur);
                } else {

                    if (!empty($validate_entrepreneur['password']) || !empty($validate_entrepreneur['confirm_password'])) {
                        unset($validate_entrepreneur['password']);
                        unset($validate_entrepreneur['confirm_password']);
                    }

                    if ($validate_entrepreneur['status'] !== 0) {
                        $validate_entrepreneur['status'] = 0;
                    }
                    $validate_entrepreneur['update_time'] = date('Y-m-d H:i:s', time());
                    $entrepreneur = $this->user_model->where('id', $id)->update($validate_entrepreneur);
                }

                if ($entrepreneur) {
                    return $this->return_message(Code::SUCCESS, '创业者数据操作成功');
                } else {
                    return $this->return_message(Code::FAILURE, '创业者数据操作失败');
                }
                break;
            case 2:
                /* 接收参数 */
                $company = request()->param('company');
                $capital_body = request()->param('capital_body');
                $location = request()->param('location');
                $invest_industry = request()->param('invest_industry');
                $invest_address = request()->param('invest_address');
                $invest_amount = request()->param('invest_amount');
                $text_domain = request()->param('text_domain');

                /* 验证参数 */
                $validate_collaborator = [
                    'id'            => $id,
                    'type'          => $type,
                    'status'        => $status,
                    'company'       => $company,
                    'location'      => $location,
                    'capital_body'  => $capital_body,
                    'invest_industry'=> $invest_industry,
                    'invest_address'=> $invest_address,
                    'invest_amount' => $invest_amount,
                    'text_domain'   => $text_domain,
                    'username'      => $username,
                    'password'      => md5($password),
                    'confirm_password'=> md5($confirm_password),
                    'mobile'        => $mobile,
                    'duty'          => $duty,
                    'department'    => $department,
                    'phone'         => $phone,
                    'wechat'        => $wechat,
                    'email'         => $email,
                    'link'          => $link
                ];

                /* 验证规则 */
                $validate_collaborator_rule = [
                    'id'            => 'number',
                    'type'          => 'require|number|in:1,2',
                    'status'        => 'require|number|in:0,1',
                    'company'       => 'require|max:255',
                    'location'      => 'require|max:500',
                    'capital_body'       => 'require|number',
                    'invest_industry'=> 'require|max:300',
                    'invest_address'=> 'require|max:400',
                    'invest_amount' => 'require|number',
                    'text_domain'   => 'require|max:600',
                    'username'      => 'require|max:120',
                    'password'      => 'require|alphaDash',
                    'confirm_password'=> 'confirm:password',
                    'mobile'        => 'require|max:32|unique:tb_user',
                    'duty'          => 'require|max:255',
                    'department'    => 'require|max:300',
                    'phone'         => 'require|max:60',
                    'wechat'        => 'require|max:60',
                    'email'         => 'require|email',
                    'link'          => 'require|max:800',
                ];

                /* 验证结果 */
                $result = $this->user_validate->check($validate_collaborator, $validate_collaborator_rule);

                if (true !== $result) {
                    return $this->return_message(Code::INVALID, $this->user_validate->getError());
                }

                /* 返回结果 */
                if (empty($id)) {
                    if (!empty($validate_collaborator['confirm_password'])) {
                        unset($validate_collaborator['confirm_password']);
                    }
                    if ($validate_collaborator['status'] !== 0) {
                        $validate_collaborator['status'] = 0;
                    }
                    $collaborator = $this->user_model->save($validate_collaborator);
                } else {
                    if (!empty($validate_collaborator['password']) || !empty($validate_collaborator['confirm_password'])) {
                        unset($validate_collaborator['password']);
                        unset($validate_collaborator['confirm_password']);
                    }
                    if ($validate_collaborator['status'] !== 0) {
                        $validate_collaborator['status'] = 0;
                    }
                    $validate_collaborator['update_time'] = date('Y-m-d H:i:s', time());
                    $collaborator = $this->user_model->where('id', $id)->update($validate_collaborator);
                    /*$collaborator = $this->user_model->save($validate_collaborator, ['id' => $id]);*/
                }

                if ($collaborator) {
                    return $this->return_message(Code::SUCCESS, '合作者数据操作成功');
                } else {
                    return $this->return_message(Code::FAILURE, '合作者数据操作失败');
                }
                break;
            default:
                return $this->return_message(Code::INVALID,'传入的用户类型不对，只能是创业者和合作者');
                break;
        }
    }

    /* 用户详情 */
    public function detail() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 返回结果 */
        $user = $this->user_model->where('id', $id)->find();

        if ($user) {
            return $this->return_message(Code::SUCCESS, '获取用户详情成功',$user);
        } else {
            return $this->return_message(Code::FAILURE, '获取用户详情失败');
        }

    }

    /* 用户删除 */
    public function delete() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('delete')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 返回结果 */
        $user = $this->user_model->where('id', $id)->delete();

        if ($user) {
            return $this->return_message(Code::SUCCESS, '删除用户成功');
        } else {
            return $this->return_message(Code::FAILURE, '删除用户失败');
        }
    }

    /* 用户审核 */
    public function auditor() {

        /* 接收参数 */
        $id = request()->param('id');
        $status = request()->param('status');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id,
            'status'    => $status
        ];

        /* 验证结果 */
        $result = $this->user_validate->scene('auditor')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->user_validate->getError());
        }

        /* 返回数据 */
        $user = $this->user_model->where('id', $id)->find();
        if (empty($user)) {
            return $this->return_message(Code::FAILURE, '用户不存在');
        } else {
            /* 此处状态为1,2 */
            if ($status == 0) {
                return $this->return_message(Code::FORBIDDEN, '审核状态错误');
            } else {
                $auditing = $this->user_model->where('id', '=', $id)->update(['status' => $status]);

                if ($auditing) {

                    if ($status == 1) {
                        return $this->return_message(Code::SUCCESS, '审核通过成功');
                    }
                    if ($status == 2) {
                        return $this->return_message(Code::SUCCESS, '审核拒绝成功');
                    }
                } else {
                    return $this->return_message(Code::FAILURE, '已经审核了');
                }
            }
        }
    }

}