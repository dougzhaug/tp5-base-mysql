<?php
/**
 * 订单管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29
 * Time: 11:04
 */

namespace app\admin\controller;


use app\admin\model\AgentModel;
use app\admin\model\OrderModel;
use app\admin\model\StoreModel;
use think\Db;
use think\Request;

class Order extends Auth
{
    /**
     * 订单列表
     *
     * @param Request $request
     * @return array|\think\response\View
     */
    public function index(Request $request)
    {
        if($request->isPost()){
            $params = $this->dealParams($request->param());
            $store = new OrderModel();
            $dataArr = $store->getOrderList($params);
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $dataArr['total'],
                'recordsFiltered' => $dataArr['total'],
                'data' => $dataArr['list'],
            ];
        }
        //return view('indexajax');
        return view('');
    }
    /**
     * 订单统计
     *
     * @param Request $request
     * @return array|\think\response\View
     */
    public function statistics(Request $request)
    {
        if($request->isPost()){
            $params = $this->dealParams($request->param());
            $store = new OrderModel();
            $dataArr = $store->getStatisticsList($params);
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $dataArr['total'],
                'recordsFiltered' => $dataArr['total'],
                'data' => $dataArr['list'],
            ];
        }
        return view('statistics/order');
    }
    /**
     * 查询代理商
     *
     */
    public function getAgent(Request $request){
        $agent = new AgentModel();
        if ($request->pid){
            $agent = $agent->where('ParentId',intval($request->pid));
        }
        $items = $agent->field('ID as id,AgentName as text')
                    ->order('ID asc')
                    ->select();
        return ['items' => $items];


    }
    /**
     * 查询商户
     *
     */
    public function getSeller(Request $request){
        $items       = [];
        $total_count = 0;
        if($request->isAjax()){
            if (!$request->page) $request->page = 1;
            $store = new StoreModel();
            $store = $store->where('ParentId',0);
            if ($request->q){
                $store = $store->where('StoreName','like','%'.$request->q.'%');
            }
            //存在代理商
            if ( intval($request->aid) > 0){
                $store = $store->where('AgentId', intval($request->aid));
            }
            $total_count = $store->count();
            if ($total_count >0){
                $items = $store->field('ID as id,StoreName as text')
                                ->order('ID asc')
                               ->limit($request->page-1,5)
                               ->select();
            }
            return ['items' => $items, 'total_count' => $total_count];
        }

    }

    /**
     * 查询门店
     *
     */
    public function getStore(Request $request){
        $items       = [];
        $total_count = 0;
        if($request->isAjax()){
            if (!$request->page){
                $request->page = 1;
            }
            $store = new StoreModel();
            //查询关键字
            if ($request->q){
                $store = $store->where('StoreName','like','%'.$request->q.'%');
            }
            //上级商户id
            if (intval($request->pid)>0){
                $store = $store->where('ParentId',intval($request->pid));
            }
            //代理商
            if (intval($request->aid)>0){
                $store = $store->where('AgentId',intval($request->pid));
            }
            $total_count = $store->count();
            if ($total_count >0){
                $items = $store->field('ID as id,StoreName as text')
                    ->order('ID asc')
                    ->limit($request->page-1,5)
                    ->select();
            }
            return ['items' => $items, 'total_count' => $total_count];
        }

    }

    /*
     * 处理参数
    */
    protected function dealParams($requestData){
        $fields    = []; //表格显示字段
        $whereArr  = []; //筛选搜索条件
        $pageLimit = 10; //每页条数
        $pageStart = 0;  //起始页
        $order     = []; //排序
        //地区删选
        //省
        if (isset($requestData['province']) && intval($requestData['province']) >0){
            $whereArr[] = ['StoreProvince','=',intval($requestData['province'])];
        }
        //市
        if (isset($requestData['city']) && intval($requestData['city']) >0){
            $whereArr[] = ['StoreCity','=',intval($requestData['city'])];
        }
        //区
        if (isset($requestData['district']) && intval($requestData['district']) >0){
            $whereArr[] = ['StoreArea','=',intval($requestData['district'])];
        }

        //（订单号）
        if (isset($requestData['option_field']) && $requestData['keyword']){
            $whereArr[] = [$requestData['option_field'],'=',$requestData['keyword']];
        }
        //（支付方式）
        if (isset($requestData['paytype']) && intval($requestData['paytype']) >0){
            $whereArr[] = ['PayMentTypeId','=',intval($requestData['paytype'])];
        }
        //（支付状态）
        if(!empty($requestData['paystatus'])){
            $whereArr[] = ['PayStatus','=',intval($requestData['paystatus'])];
        }
        //代理商筛选(二级)
        if (isset($requestData['agent2']) && intval($requestData['agent2']) >0){
            $whereArr[] = ['AgentId','=',intval($requestData['agent2'])];
            $agent2 = true;
        }else{
            $agent2 = false;
        }
        //代理商筛选(一级)
        if (isset($requestData['agent1']) && intval($requestData['agent1']) >0){
            if (!$agent2){
                $agentIdArr = Db::table('t_agent')->field('ID')->where('ParentId',intval($requestData['agent1']))->select();
                if (count($agentIdArr)>0){
                    $whereArr[] = ['AgentId','in',join(',',array_column($agentIdArr,'ID'))];
                }
            }
        }
        //商户筛选
        if (isset($requestData['seller']) && intval($requestData['seller']) >0){
            $whereArr[] = ['StoreId','=',intval($requestData['seller'])];
        }
        //门店筛选
        if (isset($requestData['store']) && intval($requestData['store']) >0){
            $whereArr[] = ['ShopId','=',intval($requestData['store'])];
        }
        //搜索栏3 （时间删选）
        if(!empty($requestData['date_range'])){
            list($start_time,$end_time) = explode('~',$requestData['date_range']);
            $whereArr[] = ['PayTime','<= time',trim($end_time)];
            $whereArr[] = ['PayTime','>= time',trim($start_time)];
        }
        //分页条件
        if (!empty($requestData['start']))  $pageStart = intval($requestData['start']);
        if (!empty($requestData['length'])) $pageLimit = intval($requestData['length']);
        //表头排序
        if (isset($requestData['order'])){
            $order_arr = $requestData['order'][0];
            $order_column = $requestData['columns'][$order_arr['column']]['data'];
            $order_dir = $order_arr['dir']=='asc'? 'asc':'desc';
            $order[$order_column] = $order_dir;
        }

        return [
            'fields'=>$fields,
            'where' =>$whereArr,
            'limit_s' =>$pageStart,
            'limit_l' =>$pageLimit,
            'order'=>$order
        ];
    }
}