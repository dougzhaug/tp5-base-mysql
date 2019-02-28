<?php
/**
 * 注册用户
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 15:57
 */

namespace app\service\Auth;


use think\Request;
use think\Validate;

trait RegistersUsers
{
    use AuthenticatesUsers;

    /**
     * 注册展示页
     *
     * @return \think\response\View
     */
    public function showRegistrationForm()
    {
        return view('auth/register');
    }

    /**
     * 注册
     *
     * @param Request $request
     */
    public function register(Request $request)
    {
        //验证数据
        $this->validateRegister($request->post());

        //保存数据
        if(!$user = $this->create($request->post())){
            $this->alerts('网络异常，请重试！');
        }

        //登录
        $this->tryLogin($request->post());

        $this->sendLoginResponse();
    }

    /**
     * 验证注册信息
     *
     * @param array $data
     * @return bool
     */
    protected function validateRegister(array $data)
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
            $this->password() => mk_pwd($data[$this->password()]),
        ]);
    }

    /**
     * 账号验证字段
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * 密码验证字段
     *
     * @return string
     */
    public function password()
    {
        return 'password';
    }
}