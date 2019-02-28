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

class AgentRole extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $builder = RoleModel::where('RType',2);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($request->IsState){
                $builder->where('IsState',$request->IsState == 1 ? : 0);
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

            $builder->field('Rid,RName,MenuID,RDescription,IsOrder,IsState,Remark,CreateTime');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['IsState'] = $val['IsState'] ? '启用' : '停用';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['RName'=>'名称']";
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

        $create = array_merge($request->post(),['RType'=>1]);
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

        $result = RoleModel::update($request->param());
        if($result){
            $this->notice('修改成功',url('/role'));
        }else{
            $this->alerts('网络异常');
        }
    }

    public function get_permissions(Request $request)
    {
        $data = MenuModel::where(['Type'=>1,'IsMenu'=>1])->field('Id as id,Name as text,ParentID as pid,Url as url,rel as icon')->select();

        $roleTree = [];
        if($request->id){   //编辑时使用
            $role = RoleModel::get($request->id);
            $roleTree = explode(',',$role['TreeID']);
        }

        foreach ($data as $key=>$val){
            if(in_array($val['id'],$roleTree)){

                $children_ids = MenuModel::where('ParentID',$val['id'])->column('id');
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