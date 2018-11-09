<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/8
 * Time: 11:55
 * Comment: Redis控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
use think\Request;
use think\cache\driver\Redis;

class Test extends BasisController {

    protected $redis;

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->redis = new Redis();
    }


    public function index() {

        $this->redis->set('name', 'huigupai');
        return $this->return_message(Code::SUCCESS, '获取数据成功', $this->redis->get('name'));
    }

    public function delete() {
        $this->redis->rm('name');
    }
}