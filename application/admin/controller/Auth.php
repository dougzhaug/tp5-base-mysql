<?php
/**
 * 登陆管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 12:25
 */

namespace app\admin\controller;


use think\App;
use think\Collection;
use think\facade\Session;
use think\Request;
use app\service\Auth\AuthService;


class Auth extends Base
{
    protected $admin;

    public function __construct(App $app = null,Request $request)
    {
        parent::__construct($app);

        $this->checkLogin($request);
    }

    public function checkLogin(Request $request)
    {
        $auth = new AuthService('admin');

        if($auth->check()){
            $this->admin = $auth->user();
        }else{

            Session::set('_login_callback_uri',$request->url(true));

            $this->redirect('/login');
        }
    }

}