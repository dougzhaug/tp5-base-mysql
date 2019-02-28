<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 13:44
 */

namespace app\admin\controller\Auth;


use app\admin\controller\Base;
use think\App;
use think\Controller;
use think\facade\Session;

class BaseAuth extends Base
{

    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }



}