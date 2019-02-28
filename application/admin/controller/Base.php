<?php
/**
 * 后台基础类
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 12:24
 */

namespace app\admin\controller;

use think\App;
use think\Controller;
use think\facade\Session;
use app\admin\controller\Traits\Loading;

class Base extends Controller
{
    use Loading;

    protected $http_referer;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->loading();
    }

    /**
     * 自动加载（类必须已 _load 开头）
     */
    public function loading()
    {
        $autoLoadFunction = [
            'oldInput',
            'dateRange',
        ];

        foreach ($autoLoadFunction as $key=>$method){

            $methodName = "_load" . ucwords($method);

            if(method_exists($this,$methodName)){
                $this->$methodName();
            }
        }
    }

    /**
     * 错误提示信息
     *
     * @param $msg
     * @param bool $url
     */
    public function alerts($msg,$url=false)
    {
        if(!is_array($msg)){
            $msg = ['errors_message'=>$msg];
        }

        Session::set('_old_input_errors',$msg,'old');
        $this->redirect($url ? : $_SERVER['HTTP_REFERER'],302);
    }

    /**
     * 成功提示信息
     *
     * @param $msg
     * @param bool $url
     */
    public function notice($msg,$url=false)
    {
        if(!is_array($msg)){
            $msg = ['success_message'=>$msg];
        }

        Session::set('_old_input_errors',$msg,'old');
        $this->redirect($url ? : $_SERVER['HTTP_REFERER'],302);
    }
}