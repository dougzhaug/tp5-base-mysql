<?php
/**
 * 验证用户
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 15:56
 */

namespace app\service\Auth;

use think\Db;
use think\facade\Cookie;
use think\facade\Session;
use think\Request;
use think\Validate;

trait AuthenticatesUsers
{

    use BaseUsers,RedirectsUsers,LoginRateLimiter;

//    protected $failException = true;
    public function showLoginForm()
    {
        return view('auth/login');
    }

    /**
     * 登录
     *
     * @param Request $request
     */
    public function login(Request $request)
    {
        //验证数据
        $this->validateLogin($request->post());

        //验证登录失败次数
        if($this->hasLimited($request)){
            //跳转通知已经被锁定
            $this->alerts('尝试次数已达上线，请稍后再试');
        }

        //尝试登录
        if($this->tryLogin($request->post(),$request->post('remember'))){
            //清除错误次数
            $this->deleteLimiter($request);

            //跳转到成功页面
            $this->sendLoginResponse();
        }

        //如果失败添加失败次数
        $this->addLimiter($request);

        //跳转通知错误次数
        $this->sendFailedLoginError($request);
    }

    /**
     * 发送登录失败的信息
     *
     * @param Request $request
     */
    public function sendFailedLoginError(Request $request)
    {
        $times = $this->residualTimes($request);
        $error_msg = '账号或密码错误！';
        if($times == 0){
            $error_msg = '尝试次数已达上线，请稍后再试';
        }else if($times <= 2){
            $error_msg = '账号或密码错误！（还有' . $times . '次机会）';
        }
        $this->alerts($error_msg);
    }

    /**
     * 处理登录成功后的跳转
     */
    public function sendLoginResponse()
    {
        $this->redirect($this->callbackUri() ? : $this->redirectPath(),302);
    }

    /**
     * 登录后的跳转地址
     *
     * @return string
     */
    public function callbackUri()
    {
        return '';
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
            $this->username()  => 'require|min:4',
            $this->password()   => 'require|min:6',
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
     * 登出
     */
    public function logout()
    {
        Session::delete('user_'.$this->slice());

        $this->deleteLoginCookie();

        $this->redirect('/');
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

    /**
     * 尝试登录
     *
     * @param $data
     * @param bool $remember
     * @return bool
     */
    public function tryLogin($data,$remember=false)
    {
        $slice = config('auth.slice.'.$this->slice());

        $model = '\\'.$slice['model'];

        $user = $model::where($this->username(),$data[$this->username()])->find();

        //验证密码
        if(!verify_pwd($data[$this->password()],$user[$this->password()])){
            return false;
        }

        //额外的验证条件
        $user = $this->additionalLogin($user->toArray());

        //记录session
        return $this->setLoginSession($user,$remember);
    }

    /**
     * session记录
     *
     * @param $user
     * @param $remember
     * @return bool
     */
    public function setLoginSession($user,$remember=false)
    {

        if(isset($user[$this->password()])){
            unset($user[$this->password()]);    //清除密码
        }

        Session::set('user_' . $this->slice(),$user);
        if($remember){
            //生成remember_token并保存到数据库
            $remember_token = $this->mkRememberToken();
            $this->saveRememberToken($user,$remember_token);
            Cookie::set('remember_token',$remember_token);
        }else{
            $this->deleteLoginCookie();
        }

        return $user;
    }

    /**
     * 清除登录cookie
     */
    public function deleteLoginCookie()
    {
        if(Cookie::has('remember_token')){
            Cookie::delete('remember_token');
        }
    }

    /**
     * 生成remember_token
     *
     * @return string
     */
    public function mkRememberToken()
    {
        return md5(md5(time() . rand(10000,99999)) . mt_rand(100000,999999));
    }

    /**
     * 存储RememberToken
     *
     * @param $user
     * @param $remember_token
     * @return mixed
     */
    public function saveRememberToken($user,$remember_token)
    {
        $slice = config('auth.slice.'.$this->slice());

        $model = '\\'.$slice['model'];

        $pk = (new $model)->getPk();

        return $model::where($pk,$user[$pk])->update(['RememberToken'=>$remember_token]);
    }

    /**
     * 额外处理
     *
     * @param $user
     * @return bool
     */
    public function additionalLogin($user)
    {
        return $user;
    }

}