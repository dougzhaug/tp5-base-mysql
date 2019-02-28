<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/7
 * Time: 9:25
 */

namespace app\admin\model;

use think\Db;

class OrderModel extends Model
{
    protected $table = 'T_Order';
    protected $pk = 'Id';

    //获取订单列表
    public function getOrderList($params){
        $fields = ('Id,AgentName,StoreName,ShopName,PayMode,OrderId,TotalFee,PayFee,PayStatus,PayTime');
        $where  = array_merge($params['where'],[]);
        $order  = empty($params['order']) ? $this->pk  : $params['order'];
        $limit_start  = $params['limit_s'];
        $limit_length = $params['limit_l'] >50 ? 50 : $params['limit_l'];
        $counts = Db::table('view_order')->field($fields)->where($where)->count();
        $lists  = Db::table('view_order')->field($fields)->where($where)->order($order)->limit($limit_start,$limit_length)->select();
        foreach ($lists as $key=>$value){
            $lists[$key]['PayStatus'] = $this->payStatusToString($value['PayStatus']);
            $lists[$key]['PayMode']   = $this->payTypeToString($value['PayMode']);
        }
        return ['total'=>$counts , 'list'=>$lists];
    }
    //获取订单列表
    public function getStatisticsList($params){
        $tmp_where = [];
        $lists = [];
        $limit_start  = $params['limit_s'];
        $limit_length = $params['limit_l'] >50 ? 50 : $params['limit_l'];
        foreach ($params['where'] as $value) {
            $tmp_where[$value[0]] = $value;
        }
        if (!isset($tmp_where['PayTime'])){
            $params['where'][] = ['PayTime','<= time',date('Y-m-d H:i:s')];
            $params['where'][] = ['PayTime','>= time',date('Y-m-d ')];
        }
        $counts =  Db::table('view_order')->where($params['where'])->group('ShopId')->count();
        $field_count_str ="SUM( case PayMode when 2 then 1 end) as ZfbOrderCount,
                         SUM( case PayMode when 2 then TotalFee end) as ZfbMoneyCount,
                            SUM( case PayMode when 1 then 1 end) as WxOrderCount,
                                SUM( case PayMode when 1 then TotalFee end) as WxMoneyCount";
        $lists_sums =  Db::table('view_order')
            ->field('ShopId,'.$field_count_str)
            ->where($params['where'])
            ->group('ShopId')
            ->order('ShopId asc')
            ->limit($limit_start,$limit_length)
            ->select();
        if (count($lists_sums)>0){
            $ShopIds = array_column($lists_sums,'ShopId');
            $sql = "select shop.Id,shop.StoreName as ShopName,store.StoreName,agent.AgentName from t_store as shop 
                        left join t_store as store on store.Id = shop.ParentId 
                        left join t_agent as agent on shop.agentId = agent.ID 
                        where shop.id in (". join(',',$ShopIds).") 
                        order by shop.Id asc ;";
            $lists_names = Db::query($sql);
        }else{
            $lists_names = [];
        }
        for ($i = 0;$i<count($lists_names);$i++){
            $lists[$i] = array_merge($lists_names[$i],$lists_sums[$i]);
        }
        return ['total'=>$counts , 'list'=>$lists];
    }

    protected function payStatusToString($value){
        $stateArr = [1=>'成功',2=>'失败'];
        return $stateArr[$value];
    }
    protected function payTypeToString($value){
        $stateArr = [1=>'微信',2=>'支付宝'];
        return $stateArr[$value];
    }
}