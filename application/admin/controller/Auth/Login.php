<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 14:53
 */

namespace app\admin\controller\Auth;

use app\admin\controller\Base;
use app\service\Auth\AuthenticatesUsers;
use think\App;
use think\facade\Session;
use think\Validate;

class Login extends Base
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }

    /**
     * 登录验证器
     *
     * @param array $data
     * @return mixed
     */
    protected function validateLogin(array $data)
    {
        $rule = [
            $this->username() => 'require|min:4',
            $this->password() => 'require|min:6',
        ];

        $msg = [
            $this->username().'.require' => $this->username().'|请填写用户名',
            $this->username().'.min'     => $this->username().'|用户名太短了',
            $this->password().'.require'   => $this->password().'|请填写密码',
            $this->password().'.min'  => $this->password().'|密码不能少于6位',
        ];

        $validate = Validate::make($rule)->message($msg);
        $result = $validate->check($data);

        if(!$result){
            $this->alerts(validate_data($validate->getError()));
        }

        return true;
    }

    /**
     * 用户名字段
     *
     * @return string
     */
    public function username()
    {
        return 'name';
    }

    /**
     * 密码字段
     *
     * @return string
     */
    public function password()
    {
        return 'password';
    }

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
     * 登录后的跳转地址
     *
     * @return string
     */
    public function callbackUri()
    {
        $callback_uri = Session::get('_login_callback_uri');

        Session::delete('_login_callback_uri');

        return $callback_uri;
    }

    /**
     * 处理额外流程
     *
     * @param $user
     * @return mixed
     */
    public function additionalLogin($user)
    {
        return $user;
    }
}