<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/21
 * Time: 10:49
 */

namespace app\admin\controller;


use app\admin\model\AdminModel;
use app\admin\model\AgentModel;
use app\admin\model\RoleModel;

use app\admin\validate\AgentUserValidate;
use think\Request;

class AgentUser extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){

            $columns = $request->columns;

            $builder = AdminModel::where(['IsDel'=>0,'Utype'=>2]);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($request->IsState){
                $builder->where('IsState',$request->IsState == 1 ? : 0);
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('CreateTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->Role){
                $builder->where('Role',$request->Role);
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

            $builder->field('Uid,Uname,Role,AgentId,Uemail,IsState,IsDel,CreateTime');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['IsState'] = $val['IsState']?'<button class="btn btn-info btn-xs">正常</button>':'<button class="btn btn-danger btn-xs">禁用</button>';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }

        $role = RoleModel::where(['IsDel'=>0,'RType'=>2])->field('Rid,RName as name,Rid as value')->select()->toArray();
        array_unshift($role,['name'=>'请选择','value'=>0]);
        $roleString = array_to_string($role);

        $inputGroupField = "['AgentName'=>'名称','ContactName'=>'联系人','ContactPhone'=>'手机']";
        return view('',['inputGroupField'=>$inputGroupField,'role'=>$roleString]);
    }

    /**
     * 添加
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create()
    {
        $role = RoleModel::where(['IsDel'=>0,'RType'=>2])->field('Rid,RName as name,Rid as value')->select()->toArray();
        array_unshift($role,['name'=>'请选择','value'=>0]);
        $roleString = array_to_string($role);
        return view('',[
            'role'=>$roleString,
            ]);
    }

    /**
     * 添加功能
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $create = $request->post();
        $validate = new AgentUserValidate();
        if (!$validate->check($create)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $create['Upassword'] = substr(md5($create['Upassword']),8,16);
        $create['IsState'] = isset($create['IsState']) && $create['IsState'] == 'on' ? 1 : 0;
        $create['Utype'] = 2;

        $result = AdminModel::create($create);
        if($result){
            $this->notice('添加成功',url('/agent_user'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(Request $request)
    {
        $agent = AdminModel::find($request->id);
        $role = RoleModel::where(['IsDel'=>0,'RType'=>2])->field('Rid,RName as name,Rid as value')->select()->toArray();

        foreach ($role as $key=>$val){
            if($val['value'] == $agent['Role']){
                $role[$key]['selected'] = 1;
            }
        }

        array_unshift($role,['name'=>'请选择','value'=>0]);
        $roleString = array_to_string($role);

        $viewData = [
            'agent' => $agent,
            'role' => $roleString,
        ];
        return view('',$viewData);
    }

    /**
     * 编辑功能
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $update = $request->post();
        $validate = new AgentUserValidate();
        if (!$validate->scene('edit')->check($update)) {
            $this->alerts(validate_data($validate->getError()));
        }

        if(isset($update['Upassword']) && $update['Upassword']){
            $update['Upassword'] = substr(md5($update['Upassword']),8,16);
        }
        $update['IsState'] = isset($update['IsState']) && $update['IsState'] == 'on' ? 1 : 0;

        $result = AdminModel::update($update);
        if($result){
            $this->notice('编辑成功',url('/agent_user'));
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
        $result = AdminModel::update(['Uid'=>$id,'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'网络异常'];
        }
    }
}