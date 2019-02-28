<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 18:17
 */

// +----------------------------------------------------------------------
// | 登录设置
// +----------------------------------------------------------------------

return [
    'defaults' => [
        'slice' => 'web',
    ],

    'slice' => [
        'web' => [
            'model' => app\index\model\UserModel::class,
        ],

        'admin' => [
            'model' => app\admin\model\AdminModel::class,
        ],

        'api' => [
            'model' => app\index\model\UserModel::class,
        ],
    ],

    //登录限制
    'passwords' => [
        'failure_limit'=>5,
        'expire' => 60,
    ],
];