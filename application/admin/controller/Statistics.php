<?php
/**
 *
 * 统计管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 15:03
 */

namespace app\admin\controller;

use app\admin\model\AgentModel;
use app\admin\model\OrderModel;
use app\service\Statistics\StatisticsService;
use think\Request;

class Statistics extends Auth
{
    public function index()
    {
        return view();
    }

    /**
     * 趋势统计
     */
    public function trend()
    {
        return view();
    }

    /**
     * 订单统计
     */
    public function order()
    {
        return view();
    }

    public function getStatisticsData(Request $request)
    {
        if(!$request->isAjax()){
            return ['errorCode'=>1,'errorMessage'=>'请求失败'];
        }
        if(!$request->scene || !$request->time){
            return ['errorCode'=>1,'errorMessage'=>'数据异常'];
        }
        $scene = is_array($request->scene) ? $request->scene : explode(',',$request->scene);
        $time = is_array($request->time) ? $request->time : explode(',',$request->time);

        $data = StatisticsService::make($scene,$time);

        return ['errorCode'=>0,'data'=>$data];
    }

    /**
     * 订单统计图表
     *
     * @return \think\response\View
     */
    public function order_chart()
    {
        return view('',[
            'top_agent' => AgentModel::getSelectArray(false,['ParentId'=>0]),
        ]);
    }

    public function getStatisticsOrderData(Request $request)
    {
        $builder = OrderModel::alias('o')->where(['o.PayStatus'=>1]);

        if($request->store){
            $builder->where('o.ShopId',$request->store);
        }else{
            if(($request->seller)){
                $store_ids = StoreModel::where('ParentId',$request->seller)->column('ID');
                $builder->where('o.StoreId','in',$store_ids);
            }else{
                if($request->second_agent){
                    $builder->where('o.AgentId',$request->second_agent);
                }else{
                    if($request->top_agent){
                        $ids = AgentModel::where('ParentId',$request->top_agent)->column('ID');
                        $builder->where('o.AgentId','in',$ids);
                    }
                }
            }
        }

        if($request->area_code){
            $builder->join('T_Store s','s.ID=o.ShopId');
            $builder->where('s.Area',$request->area_code);
        }else{
            if($request->city_code){
                $builder->join('T_Store s','s.ID=o.ShopId');
                $builder->where('s.City',$request->city_code);
            }else{
                if($request->province_code){
                    $builder->join('T_Store s','s.ID=o.ShopId');
                    $builder->where('s.Province',$request->province_code);
                }
            }
        }

        if($request->PayMentTypeId){
            $builder->where('o.PayMentTypeId',$request->PayMentTypeId);
        }

        if($this->start_time && $this->end_time){
            $builder->whereTime('o.PayTime','between',[$this->start_time,$this->end_time]);
        }else{
            //默认查昨天的
            $builder->whereTime('o.PayTime','between',[date("Y-m-d",strtotime("-1 day")).' 00:00:00',date("Y-m-d",strtotime("-1 day")).' 23:59:59']);
        }
        $builder->field('CAST(o.PayTime as date) as PayTime,sum(o.PayFee) as PayFee,count(*) as count');
        $order = $builder->group('CAST(o.PayTime as date)')->select();

        $data = [];
        foreach ($order as $key => $val) {
            $data[] = [
                'key'=>$val['PayTime'],
                'amount' => $val['PayFee'],
                'volume' => $val['count']
            ];
        }

        return ['errorCode'=>0,'data'=>$data];
    }
}