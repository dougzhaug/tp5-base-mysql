<?php
/**
 * 自动加载数据
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/24
 * Time: 16:45
 */

namespace app\admin\controller\Traits;


use think\facade\Request;
use think\facade\Session;

trait Loading
{
    protected $start_time;
    protected $end_time;

    /**
     * 自动加载-保存上次post提交的数据
     */
    public function _loadOldInput()
    {
        if(Request::isPost() || Request::isPut()){
            Session::set('_old_input',input(),'old');
        }
    }

    /**
     * 获取时间选择器中的信息
     */
    public function _loadDateRange()
    {
        $date_range = Request::param('date_range');
        if($date_range){
            list($this->start_time,$this->end_time) = explode(config('daterange.separator'),$date_range);
        }
    }
}