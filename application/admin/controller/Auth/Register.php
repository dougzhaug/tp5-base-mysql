<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 14:54
 */

namespace app\admin\controller\Auth;

use app\admin\controller\Base;
use app\service\Auth\RegistersUsers;
use think\Validate;

class Register extends Base
{
    use RegistersUsers;

    protected $redirectTo = '/';

    /**
     * 验证注册信息
     *
     * @param array $data
     * @return bool
     */
    protected function validateRegister(array $data)
    {
        $rule = [
            $this->username() => 'require|min:4|unique:T_sysuser',
            'Uemail' => 'require|email',
            $this->password() => 'require|min:6|confirm:Rpassword',
            'agree' => 'require',
        ];

        $msg = [
            $this->username().'.require' => $this->username().'|请填写用户名',
            $this->username().'.min'     => $this->username().'|用户名太短了',
            $this->username().'.unique'     => $this->username().'|用户名已存在',
            'Uemail.require' => 'Uemail|请填写邮箱',
            'Uemail.email' => 'Uemail|邮箱格式错误',
            $this->password().'.require'   => $this->password().'|请填写密码',
            $this->password().'.min'  => $this->password().'|密码不能少于6位',
            $this->password().'.confirm'  => $this->password().'|密码不一致',
            'agree.require'  => 'agree|请同意条款',
        ];

        $validate = Validate::make($rule)->message($msg);
        $result = $validate->check($data);

        if(!$result){
            $this->alerts(validate_data($validate->getError()));
        }

        return true;
    }

    /**
     * 保存数据
     *
     * @param array $data
     * @return mixed
     */
    protected function create(array $data)
    {

        $slice = config('auth.slice.'.$this->slice());

        $model = '\\'.$slice['model'];

        return $model::create([
            $this->username() => $data[$this->username()],
            'Uemail' => $data['Uemail'],
            $this->password() => mk_pwd($data[$this->password()]),
        ]);
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

    /**
     * 登录组
     *
     * @return string
     */
    public function slice()
    {
        return 'admin';
    }
}