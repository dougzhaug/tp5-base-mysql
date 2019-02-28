<?php
/**
 * 重定向
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 17:17
 */

namespace app\service\Auth;


trait RedirectsUsers
{
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }
}