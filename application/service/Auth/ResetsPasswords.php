<?php
/**
 * 重置密码
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 17:27
 */

namespace app\service\Auth;


use think\Request;
use think\Validate;

trait ResetsPasswords
{
    use AuthenticatesUsers;

    /**
     * 修改密码展示页
     *
     * @return \think\response\View
     */
    public function showResetForm()
    {
        return view('auth/reset');
    }

    /**
     * 执行修改
     *
     * @param Request $request
     */
    public function reset(Request $request)
    {
        $this->validateReset($request->post());

        //判断新旧密码是否相同
        if($request->post($this->password()) == $request->post($this->newPassword())){
            $this->alerts('您输入的新密码于旧密码相同');
        }

        //获取用户信息
        $user = $this->getUser();

        //验证旧密码
        if(!$this->verifyPassword($request->post(),$user)){
            $this->alerts('密码错误');
        }

        //额外验证
        $this->additionalReset($user);

        //修改数据库
        $this->editPassword($user,$request->post());

        //重新登录
        $this->tryLogin($user->toArray());

        //跳转页面
        $this->notice('修改密码成功',$this->redirectPath());
//        $this->sendLoginResponse();
    }

    protected function validateReset(array $data)
    {
        $rule = [
            $this->password() => 'require|min:6',
            $this->newPassword() => 'require|min:6|confirm:'.$this->ConfirmPassword(),
        ];

        $msg = [
            $this->password().'.require' => $this->password().'|请填写旧密码',
            $this->password().'.min'     => $this->password().'|旧密码不能少于6位',
            $this->newPassword().'.require'   => $this->newPassword().'|请填写新密码',
            $this->newPassword().'.min'  => $this->newPassword().'|新密码不能少于6位',
            $this->newPassword().'.confirm'  => $this->newPassword().'|密码不一致',
        ];

        $validate = Validate::make($rule)->message($msg);
        $result = $validate->check($data);

        if(!$result){
            $this->alerts(validate_data($validate->getError()));
        }

        return true;
    }

    public function getUser()
    {
        $sessionUser = (new AuthService($this->slice()))->user();

        $slice = config('auth.slice.'.$this->slice());

        $model = '\\'.$slice['model'];

        $pk = (new $model)->getPk();

        $user = $model::get($sessionUser[$pk]);
        if(!$user){
            $this->alerts('网络异常，请重试');
        }

        return $user;
    }

    /**
     * 验证密码
     *
     * @param $data
     * @param $user
     * @return bool
     */
    public function verifyPassword($data,$user)
    {
        return verify_pwd($data[$this->password()],$user[$this->password()]);
    }

    /**
     * 重置密码-额外验证条件
     */
    public function additionalReset($user)
    {
        return true;
    }

    /**
     * 修改密码
     *
     * @param $user
     * @param $request
     * @return mixed
     */
    public function editPassword(&$user,$request)
    {
        $password = $this->password();

        $user->$password = mk_pwd($request[$this->newPassword()]);

        $result = $user->save();
        if(!$result){
            $this->alerts('网络异常，请重试');
        }

        return $result;
    }

    /**
     * 旧密码
     *
     * @return string
     */
    public function password()
    {
        return 'password';
    }

    /**
     * 新密码
     *
     * @return string
     */
    public function newPassword()
    {
        return 'new_password';
    }

    /**
     * 确认密码
     *
     * @return string
     */
    public function ConfirmPassword()
    {
        return 'confirm_password';
    }
}