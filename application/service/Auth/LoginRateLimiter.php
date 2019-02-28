<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 10:11
 */

namespace app\service\Auth;


use think\facade\Cache;
use think\facade\Config;
use think\Request;

trait LoginRateLimiter
{
    protected $defaultFailureLimit = 5;     //默认错误次数限制
    protected $defaultExpire = 60;          //默认锁定时间

    /**
     * 添加错误次数
     *
     * @param Request $request
     * @return mixed
     */
    public function addLimiter(Request $request)
    {

        $key = $this->loginKey($request);
        $old = Cache::get($key) ? : 0;

        return Cache::set($key,$old+1,$this->getExpire());
    }

    /**
     * 是否被锁定
     *
     * @param Request $request
     * @return bool
     */
    public function hasLimited(Request $request)
    {
        if(!$this->residualTimes($request)){
            return true;
        }
        return false;
    }

    /**
     * 清除错误次数
     *
     * @param Request $request
     * @return bool
     */
    public function deleteLimiter(Request $request)
    {
        return Cache::rm($this->loginKey($request));
    }

    /**
     * 获取失败次数
     *
     * @param Request $request
     * @return int|mixed
     */
    public function getFailures(Request $request)
    {
        $failures = 0;
        if(Cache::has($this->loginKey($request))){
            $failures = Cache::get($this->loginKey($request));
        }

        return $failures;
    }

    /**
     * 获取剩余尝试次数
     *
     * @param Request $request
     * @return int|mixed
     */
    public function residualTimes(Request $request)
    {
        $times = $this->getFailureLimit() - $this->getFailures($request);
        return $times > 0 ? $times : 0;
    }

    /**
     * 生成登录限制key
     *
     * @param Request $request
     * @return string
     */
    public function loginKey(Request $request)
    {
        $key = $this->slice().'-'.$request->post($this->username()).'-'.$request->ip();

        return mb_strtolower($key, 'UTF-8');
    }

    public function getFailureLimit()
    {
        return Config::has('auth.passwords.failure_limit') ? Config::get('auth.passwords.failure_limit') : $this->defaultFailureLimit;
    }

    public function getExpire()
    {
        return Config::has('auth.passwords.expire') ? Config::get('auth.passwords.expire') : $this->defaultExpire;
    }
}