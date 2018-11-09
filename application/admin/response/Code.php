<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 17:16
 * Comment: 状态码
 */

namespace app\admin\response;

class Code {

    /* 成功状态码 */
    const SUCCESS = 200;

    /* 失败状态码 */
    const FAILURE = 404;

    /* 无效状态码 */
    const INVALID = 401;

    /* 过期状态码 */
    const EXPIRED = 402;

    /* 禁止状态码 */
    const FORBIDDEN = 403;
}