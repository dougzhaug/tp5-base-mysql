<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/7
 * Time: 15:10
 */

namespace app\admin\controller;


use app\admin\model\DevInfoModel;
use app\admin\model\StoreModel;
use app\admin\validate\DeviceValidate;
use think\Request;

class Device extends Auth
{
    /**
     * 设备列表
     *
     * @param Request $request
     * @return array|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        if($request->isPost()){
            $columns = $request->columns;

            $builder = DevInfoModel::alias('d')
                            ->join('T_Store s','s.ID = d.Store_Id')
                            ->where('d.IsDel',0);

            /* where 条件 */
            if($request->keyword){
                $field = 'd.'.$request->option_field;
                if($request->option_field == 'Store_Id'){
                    $field = 's.StoreName';
                }
                $builder->where($field,'like','%'.$request->keyword.'%');
            }
            if($request->IsState){
                $builder->where('d.IsState',$request->IsState == 1 ? : 0);
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('d.CreateTime','between',[$this->start_time,$this->end_time]);
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

            $builder->field('d.Id,d.Store_Id,d.DevName,d.DeviceSn,d.CashierId,d.Is_Refund,d.IsState,d.CreateTime,s.StoreName');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['IsState'] = $val['IsState']?'<button class="btn btn-info btn-xs">正常</button>':'<button class="btn btn-danger btn-xs">禁用</button>';
                $data[$key]['Is_Refund'] = $val['Is_Refund']?'<button class="btn btn-info btn-xs">是</button>':'<button class="btn btn-danger btn-xs">否</button>';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['DevName'=>'名称','DeviceSn'=>'设备编号','Store_Id'=>'商户名称']";
        return view('',['inputField'=>$inputField]);
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
        $store = $this->getStoreToSelect();
        return view('',['store'=>$store]);
    }

    /**
     * 添加功能
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $create = $request->post();
        $create['IsState'] = isset($create['IsState']) && $create['IsState'] == 'on' ? 1 : 0;
        $validate = new DeviceValidate();
        if (!$validate->check($create)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $create['CreateTime'] = date('Y-m-d H:i:s');
        $result = DevInfoModel::create($create);
        if($result){
            $this->notice('添加成功',url('/device'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑
     *
     * @param $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        $device = DevInfoModel::get($id);
        $store = $this->getStoreToSelect($device['Store_Id']);
        return view('',['device'=>$device,'store'=>$store]);
    }

    /**
     * 编辑功能
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $update = $request->post();
        $update['IsState'] = isset($update['IsState']) && $update['IsState'] == 'on' ? 1 : 0;
        $validate = new DeviceValidate();
        if (!$validate->scene('edit')->check($update)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $result = DevInfoModel::update($update);
        if($result){
            $this->notice('编辑成功',url('/device'));
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
        $result = DevInfoModel::update(['Id'=>$id,'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'网络异常'];
        }
    }

    /**
     * 获取商户下拉菜单
     *
     * @param bool $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStoreToSelect($id=false)
    {
        $storeModel = StoreModel::where(['IsDel'=>0,'IsState'=>1])->field('id,ParentId as pid,StoreName as name,id as value')->select();
        $tree = make_tree_with_namepre($storeModel);
        $tree = json_decode(json_encode($tree),true);
        $store = split_tree($tree);
        if($id){
            foreach ($store as $key=>$val){
                if($val['value'] == $id){
                    $store[$key]['selected'] = 1;
                }
            }
        }
        return array_to_string($store);
    }
}