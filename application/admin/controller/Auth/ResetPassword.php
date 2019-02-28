<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 14:55
 */

namespace app\admin\controller\Auth;


use app\admin\controller\Auth;
use app\service\Auth\ResetsPasswords;

class ResetPassword extends Auth
{
    use ResetsPasswords;

    protected $redirectTo = '/';

    /**
     * 登录组
     *
     * @return string
     */
    public function slice()
    {
        return 'admin';
    }

    /**
     * 用户名字段
     *
     * @return string
     */
    public function username()
    {
        return 'Uname';
    }

    /**
     * 密码字段
     *
     * @return string
     */
    public function password()
    {
        return 'Upassword';
    }
}