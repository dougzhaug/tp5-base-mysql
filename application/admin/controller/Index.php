<?php
/**
 * 后台首页
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 13:29
 */

namespace app\admin\controller;


class Index extends Auth
{
    public function index()
    {
        return view();
    }
}