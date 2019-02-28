<?php
/**
 * 结算管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/6
 * Time: 10:17
 */

namespace app\admin\controller;


use app\admin\model\AgentModel;
use app\admin\model\OrderModel;
use app\admin\model\OrderView;
use app\admin\model\SettlesAgentModel;
use app\admin\model\SettlesStoreModel;
use app\admin\model\StoreModel;
use think\Request;

class Settles extends Auth
{
    /**
     * 结算单
     *
     * @param Request $request
     * @return array|\think\response\View
     */
    public function index(Request $request)
    {
        if($request->isPost()){
            //数据
            $data = [
                [
                    'id'=>1,
                    'agent_name' => '河北代理',
                    'create_time' => '2018-11-01',
                    'amount_payable'=> 595524.81,
                    'settlement_amount'=> 55524.11,
                    'poundage' => 4000,
                    'trading_volume' => '225',
                    'settlement_type' => '现金',
                    'status'=>'结算成功'
                ],
                [
                    'id'=>2,
                    'agent_name' => '北京',
                    'create_time' => '2018-10-31',
                    'amount_payable'=> 460024.32,
                    'settlement_amount'=> 42524.12,
                    'poundage' => 3440,
                    'trading_volume' => '304',
                    'settlement_type' => '转账',
                    'status'=>'结算成功'
                ],
                [
                    'id'=>3,
                    'agent_name' => '唐山代理',
                    'create_time' => '2018-10-29',
                    'amount_payable'=> 105524.10,
                    'settlement_amount'=> 85524.65,
                    'poundage' => 2100,
                    'trading_volume' => '105',
                    'settlement_type' => '转账',
                    'status'=>'结算成功'
                ],
                [
                    'id'=>4,
                    'agent_name' => '石家庄代理',
                    'create_time' => '2018-11-02',
                    'amount_payable'=> 4524.84,
                    'settlement_amount'=> 4024.01,
                    'poundage' => 500,
                    'trading_volume' => '1140',
                    'settlement_type' => '转账',
                    'status'=>'结算成功'
                ],
                [
                    'id'=>5,
                    'agent_name' => '泰国代理',
                    'create_time' => '2018-11-01',
                    'amount_payable'=> 6001024.25,
                    'settlement_amount'=> 5500524.18,
                    'poundage' => 51500,
                    'trading_volume' => '8104',
                    'settlement_type' => '汇款',
                    'status'=>'结算成功'
                ],
            ];
            //总条数
            $total = 20;
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['name'=>'名称','phone'=>'联系电话','email'=>'联系人']";
        return view('',['inputField'=>$inputField]);
    }

    public function read($id)
    {
        return view();
    }

    /**
     * 代理商结算列表
     *
     * @param Request $request
     * @return array|\think\response\View
     */
    public function agent(Request $request)
    {
        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $builder = SettlesAgentModel::alias('s')
                ->join('T_Agent a','a.Id = s.AgentId');

            /* where 条件 */
            if($request->keyword){
                $field = 's.'.$request->option_field;
                if($request->option_field == 'AgentName'){
                    $field = 'a.AgentName';
                }
                $builder->where($field,'like','%'.$request->keyword.'%');
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('s.SettlesTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->type){
                $builder->where('s.Type',$request->type);
            }
            if($request->state){
                $builder->where('s.IsState',$request->state);
            }

            if($request->second_agent){
                $builder->where('s.AgentId',$request->second_agent);
            }else{
                if($request->top_agent){
                    $builder->where('s.AgentId',$request->top_agent);
                }
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

            $builder->field('s.Id,s.OrderId,s.Type,s.TradeNo,s.Amount*0.01 as Amount,s.FeeType,s.Rate,s.StartTime,s.EndTime,s.SettlesTime,s.CreateTime,a.AgentName');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['Type'] = SettlesAgentModel::getType($val['Type']);
                $data[$key]['Scope'] = $val['StartTime'] .'<br/>~'. $val['EndTime'];
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['OrderId'=>'订单号','AgentName'=>'代理名称']";
        return view('',[
            'inputField'=>$inputField,
            'top_agent' => AgentModel::getSelectArray(false,['ParentId'=>0]),
            ]);
    }

    public function agent_read(Request $request,$id)
    {
        $settles = SettlesAgentModel::get($id);

        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $second_agent = AgentModel::where('ParentId',$settles['AgentId'])->column('ID');

            $ids = array_merge($second_agent,[$settles['AgentId']]);

            $builder = OrderView::where(['IsClear'=>1])
                        ->whereIn('AgentId',$ids)
                        ->whereTime('PayTime','between',[$settles['StartTime'],$settles['EndTime']]);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('PayTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->store){
                $builder->where('ShopId',$request->store);
            }else{
                if($request->seller){
                    $builder->where('StoreId',$request->seller);
                }else{
                    if($request->second_agent){
                        $builder->where('AgentId',$request->second_agent);
                    }
                }
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

            $builder->field('Id,OrderId,AgentName,StoreName,ShopName,PayTime,PayFee*0.01 as PayFee');
            $data = $builder->select();

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }

        $inputGroupField = "['AgentName'=>'代理商','StoreName'=>'商户','ShopName'=>'门店']";
        return view('',[
            'inputGroupField'=>$inputGroupField,
            'second_agent'=>AgentModel::getSelectArray(false,['ParentId'=>$settles['AgentId']]),
            'seller'=>StoreModel::getSelectArray(false,['AgentId'=>$settles['AgentId']]),
            ]);

    }

    /**
     * 商户结算
     *
     * @param Request $request
     * @return array|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function seller(Request $request)
    {
        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $builder = SettlesStoreModel::alias('se')
                        ->join('T_Store st','st.Id = se.StoreId');

            /* where 条件 */
            if($request->keyword){
                $field = 'se.'.$request->option_field;
                if($request->option_field == 'StoreName'){
                    $field = 'st.StoreName';
                }
                $builder->where($field,'like','%'.$request->keyword.'%');
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('se.SettlesTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->type){
                //dump($request->type);die;
                $builder->where('se.Type',$request->type);
            }
            if($request->state){
                $builder->where('se.IsState',$request->state);
            }
            if($request->store){
                $builder->where('se.StoreId',$request->store);
            }else{
                if(($request->seller)){
                    $store_ids = StoreModel::where('ParentId',$request->seller)->column('ID');
                    $builder->where('se.StoreId','in',$store_ids);
                }else{
                    if($request->second_agent){
                        $builder->where('se.AgentId',$request->second_agent);
                    }else{
                        if($request->top_agent){
                            $ids = AgentModel::where('ParentId',$request->top_agent)->column('ID');
                            $builder->where('se.AgentId','in',$ids);
                        }
                    }
                }
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

            $builder->field('se.Id,se.OrderId,se.Type,se.TradeNo,se.Amount*0.01 as Amount,se.FeeType,se.Rate,se.StartTime,se.EndTime,se.SettlesTime,se.CreateTime,st.StoreName');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['Type'] = SettlesStoreModel::getType($val['Type']);
                $data[$key]['Scope'] = $val['StartTime'] .'<br/>~'. $val['EndTime'];
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }

        $inputField = "['OrderId'=>'订单号','StoreName'=>'商户名称']";
        return view('',[
            'inputField'=>$inputField,
            'top_agent' => AgentModel::getSelectArray(false,['ParentId'=>0]),
            ]);
    }

    public function seller_read(Request $request,$id)
    {
        $settles = SettlesStoreModel::get($id);

        if($request->isPost()){
            //数据
            $columns = $request->columns;

            $builder = OrderView::where(['IsClear'=>1])
                ->whereIn('StoreId',$settles['StoreId'])
                ->whereTime('PayTime','between',[$settles['StartTime'],$settles['EndTime']]);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('PayTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->store){
                $builder->where('ShopId',$request->store);
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

            $builder->field('Id,OrderId,AgentName,StoreName,ShopName,PayTime,PayFee*0.01 as PayFee');
            $data = $builder->select();

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $seller = StoreModel::get($settles['StoreId']);
        $inputGroupField = "['OrderId'=>'订单号']";
        return view('',[
            'inputGroupField'=>$inputGroupField,
            'settles'=>$settles,
            'seller'=>$seller,
            'store'=>StoreModel::getSelectArray(false,['ParentId'=>$settles['StoreId']]),
        ]);
    }
}