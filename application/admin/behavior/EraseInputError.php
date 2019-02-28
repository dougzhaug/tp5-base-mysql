<?php
/**
 * 钩子函数-重置post请求的旧数据，防止污染其它页面
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 15:29
 */

namespace app\admin\behavior;

use think\facade\Request;
use think\facade\Session;

class EraseInputError
{

    public function run()
    {
        //跳转完页面后清除所有旧数据的信息
        if(Request::isGet() || Request::isAjax()){
            Session::delete('_old_input','old');
            Session::delete('_old_input_errors','old');
        }
    }
}