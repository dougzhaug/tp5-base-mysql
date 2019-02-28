<?php
/**
 * 收银员管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 13:55
 */

namespace app\admin\controller;


use app\admin\model\AdminModel;
use app\admin\model\StoreModel;
use app\admin\validate\CashierValidate;
use think\Request;

class Cashier extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){

            $columns = $request->columns;

            $builder = AdminModel::alias('a')
//                        ->join('T_Agent ag','ag.ID=a.AgentId')
                        ->join('T_Store s','s.ID=a.StoreId')
                        ->where(['a.IsDel'=>0,'a.Utype'=>3]);

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

            $builder->field('Uid,Uname,a.IsState,s.StoreName,Uemail,a.CreateTime');
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

        $inputField = "['name'=>'姓名','phone'=>'联系电话']";
        return view('',['inputField'=>$inputField]);
    }

    /**
     * 添加页面
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create()
    {
        $store = StoreModel::getSelectTreeArray();
        return view('',['store'=>$store]);
    }

    /**
     * 添加
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $create = $request->post();
        $create['IsState'] = isset($create['IsState']) && $create['IsState'] == 'on' ? 1 : 0;
        $validate = new CashierValidate();
        if (!$validate->check($create)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $create['Utype'] = 3;
        $create['Upassword'] = md5($create['Upassword']);   //暂时md5处理
        $create['CreateTime'] = date('Y-m-d H:i:s');
        $result = AdminModel::create($create);
        if($result){
            $this->notice('添加成功',url('/cashier'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑页面
     *
     * @param $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $cashier = AdminModel::where('Utype',3)->get($id);
        $store = StoreModel::getSelectTreeArray($cashier['StoreId']);
        return view('',['cashier'=>$cashier,'store'=>$store]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $update = $request->post();
        $update['IsState'] = isset($update['IsState']) && $update['IsState'] == 'on' ? 1 : 0;
        $validate = new CashierValidate();
        if (!$validate->scene('edit')->check($update)) {
            $this->alerts(validate_data($validate->getError()));
        }

        if(isset($update['Upassword']) && $update['Upassword']){
            $update['Upassword'] = md5($update['Upassword']);
        }else{
            unset($update['Upassword']);
        }
        $result = AdminModel::update($update);
        if($result){
            $this->notice('编辑成功',url('/cashier'));
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