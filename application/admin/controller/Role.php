<?php
/**
 * 角色管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 16:32
 */

namespace app\admin\controller;


use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\admin\validate\RoleValidate;
use think\Request;

class Role extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $builder = RoleModel::where('status',1);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($request->status){
                $builder->where('status',$request->status == 1 ? : 0);
            }
            /* where end */

            /* get count */
            $total = $builder->count();

            /* order start */
            if($request->order){
                $order = $request->order[0];
                $order_column = $columns[$order['column']]['data'];
                $order_dir = $order['dir'];
                $builder->order($order_column,$order_dir);
            }
            /* order end */

            /* limit */
            if($request->start){
                $builder->limit($request->start,$request->length);
            }
            /* limit end */

            $builder->field('id,name,menu_id,sort,status,remark,create_time');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['status'] = $val['status'] ? '启用' : '停用';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['name'=>'名称']";
        return view('',['inputField'=>$inputField]);
    }

    /**
     * 添加页面
     *
     * @return \think\response\View
     */
    public function create()
    {
        return view();
    }

    /**
     * 添加
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $validate = new RoleValidate();
        if (!$validate->check($request->post())) {
            $this->alerts(validate_data($validate->getError()));
        }

        $create = array_merge($request->post(),['status'=>1]);
        $result = RoleModel::create($create);
        if($result){
            $this->notice('添加成功',url('/role'));
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
        $viewData = [
            'role' => RoleModel::find($request->id),
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
        $validate = new RoleValidate();
        if (!$validate->scene('edit')->check($request->post())) {
            $this->alerts(validate_data($validate->getError()));
        }

        $result = RoleModel::update($request->post());
        if($result){
            $this->notice('修改成功',url('/role'));
        }else{
            $this->alerts('网络异常');
        }
    }

    public function get_permissions(Request $request)
    {
        $data = MenuModel::where(['status'=>1,'is_menu'=>1])->field('id,name as text,pid,url,icon')->select();

        $roleTree = [];
        if($request->id){   //编辑时使用
            $role = RoleModel::get($request->id);
            $roleTree = explode(',',$role['tree_id']);
        }

        foreach ($data as $key=>$val){
            if(in_array($val['id'],$roleTree)){

                $children_ids = MenuModel::where('pid',$val['id'])->column('id');
                if($children_ids){
                    if(is_contain_array($roleTree,$children_ids)){
                        $data[$key]['state'] = array_merge(isset($val['state']) ? $val['state'] : [],['selected'=>true]);
                    }
                }else{
                    $data[$key]['state'] = array_merge(isset($val['state']) ? $val['state'] : [],['selected'=>true]);
                }
            }
            $data[$key]['icon'] = 'fa ' . $val['icon'];
        }
        $permissions = make_tree(json_decode(json_encode($data)));
        return $permissions;
    }
}