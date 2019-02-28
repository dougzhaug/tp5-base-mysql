<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 13:56
 */

namespace app\service\Auth;


use app\service\Service;
use think\facade\Cookie;
use think\facade\Session;

class AuthService extends Service
{
    use AuthenticatesUsers;

    protected $slice;

    public function __construct($slice=false)
    {
//        $this->slice = isset($param['slice']) ? $param['slice'] : config('auth.defaults.slice');
        $this->slice = $slice ? $slice : config('auth.defaults.slice');
    }

    /**
     * 设置组信息
     *
     * @return bool|mixed
     */
    public function slice()
    {
        return $this->slice;
    }

    /**
     * 检查登录状态
     *
     * @return bool
     */
    public function check()
    {
        return $this->user() ? true : false;
    }

    /**
     * 获取用户信息
     *
     * @return mixed|null
     */
    public function user()
    {
        $user = Session::get('user_'.$this->slice);

        if($user) return $user;

        $remember_token = Cookie::get('remember_token');
        if($remember_token){

            $user = $this->getUserModel($remember_token);

            if($user){
                return $this->setLoginSession($user,true); //重新设置session
            }
        }
        return null;
    }

    public function getUserModel($remember_token)
    {
        $slice = config('auth.slice.'.$this->slice());

        $model = '\\'.$slice['model'];

        return $model::where('RememberToken',$remember_token)->find();
    }
}