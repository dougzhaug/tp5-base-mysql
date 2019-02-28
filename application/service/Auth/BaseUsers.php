<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 18:29
 */

namespace app\service\Auth;

trait BaseUsers
{
    public function slice()
    {
        return config('auth.defaults.slice');
    }
}