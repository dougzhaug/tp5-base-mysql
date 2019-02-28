<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 17:28
 */

namespace app\admin\controller;


use app\admin\model\MenuModel;
use app\admin\validate\MenuValidate;
use think\Request;

class Menu extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){

            $columns = $request->columns;

            $builder = MenuModel::where('status',1);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($request->IsState){
                $builder->where('IsState',$request->IsState == 1 ? : 0);
            }
            if($request->IsMenu){
                $builder->where('IsMenu',$request->IsMenu == 1 ? : 0);
            }
            /* where end */

            /* get count */
//            $total = $builder->count();

            /* order start */
            if($request->order){
                $order = $request->order[0];
                $order_column = $columns[$order['column']]['data'];
                $order_dir = $order['dir'];
                $builder->order($order_column,$order_dir);
            }
            /* order end */

            $builder->field('id,pid,name,url,icon,sort,status,is_menu,remark,create_time');
            $data = $builder->select();

            $treeMenu = make_tree_with_namepre($data,'pid');
            $tree = json_decode(json_encode($treeMenu),true);
            $data = split_tree($tree);

            foreach ($data as $key=>$val){
                $data[$key]['icon'] = "<i class='fa " . $val['icon'] . "'> " . $val['icon'] . "</i>";
                $data[$key]['status'] = $val['status'] ? '启用' : '停用';
                $data[$key]['is_menu'] = $val['is_menu'] ? '是' : '否';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => $data,
            ];
        }

        $inputField = "['name'=>'名称']";
        return view('',['inputField'=>$inputField]);
    }

    /**
     * 添加
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create(Request $request)
    {
        $menuModel = MenuModel::where('status',1)->field('id,name,pid,id as value')->select();

        foreach($menuModel as $key=>$val){
            if($val['value'] == $request->id){
                $menuModel[$key]['selected'] = 1;
            }
        }

        /* 获取下拉所需的数据 */
        $treeMenu = make_tree_with_namepre($menuModel);
        $tree = json_decode(json_encode($treeMenu),true);
        $menu = split_tree($tree);
        array_unshift($menu,['name'=>'请选择','value'=>0]);
        $menuString = array_to_string($menu);
        /* 获取下拉所需的数据（完） */

        $viewData = [
            'menu'=> $menuString,
            'menu_id' => $request->id,
        ];

        return view('',$viewData);
    }

    /**
     * 添加功能
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $post = $request->post();
        $validate = new MenuValidate();
        if (!$validate->check($post)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $post['create_time'] = date('Y-m-d H:i:s');

        $result = MenuModel::create($post);
        if($result){
            $this->notice('添加成功',url('/menu'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑页面
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(Request $request)
    {
        $menuModel = MenuModel::where('status',1)->field('id,name,pid,id as value')->select();

        /* 获取下拉所需的数据 */
        $treeMenu = make_tree_with_namepre($menuModel);
        $tree = json_decode(json_encode($treeMenu),true);
        $menu = split_tree($tree);
        array_unshift($menu,['name'=>'请选择','value'=>0]);
        $menuString = array_to_string($menu);
        /* 获取下拉所需的数据（完） */
        $menu = MenuModel::find($request->id);
        $viewData = [
            'allMenu'=> $menuString,
            'pid' => $menu['pid'],
            'menu' => MenuModel::find($request->id),
        ];

        return view('',$viewData);
    }

    /**
     * 编辑
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $validate = new MenuValidate();
        if (!$validate->scene('edit')->check($request->post())) {
            $this->alerts(validate_data($validate->getError()));
        }

        $result = MenuModel::update($request->param());
        if($result){
            $this->notice('编辑成功',url('/menu'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 删除
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $result = MenuModel::update(['Id'=>$id,'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'网络异常'];
        }
    }
}