<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:54
 */

namespace app\admin\behavior;


use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\service\Auth\AuthService;
use think\facade\View;

class BuildMenu
{
    public function run()
    {
        $menu_html = '';
        $auth = new AuthService('admin');
        if($user = $auth->user()){

            $role = RoleModel::where('id','in',trim($user['role'],','))->select();
            $role_ids = '';
            foreach ($role as $key=>$val){
                $role_ids .= ','.$val['menu_id'];
            }
            $menu = MenuModel::where('status',1)
                ->where('is_menu',1)
                ->whereIn('id',trim($role_ids,','))
                ->field('id,pid,name,url,icon')
                ->select();
            //生成菜单html
            $menu_html = make_left_menu($menu);
        }

        View::share('leftMenu',$menu_html);
    }
}