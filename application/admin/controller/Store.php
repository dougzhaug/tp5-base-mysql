<?php
/**
 * 店铺/门店管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 13:53
 */

namespace app\admin\controller;


use think\Request;
use app\admin\model\StoreModel;
use app\admin\validate\StoreValidate;

class Store extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){
            $params = $this->dealParams($request->param());
            $store = new StoreModel();
            $dataArr = $store->getStoreList($params);
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $dataArr['total'],
                'recordsFiltered' => $dataArr['total'],
                'data' => $dataArr['list'],
            ];
        }
        $agentList = $this->getAgentList();
        $agentList = $this->arrayToTemplateString($agentList,'StoreName','ID');
        $inputField = "['StoreName'=>'名称','ContactPhone'=>'联系电话','ContactName'=>'联系人']";
        return view('',['inputField'=>$inputField,'agentList'=>$agentList]);
    }

    /**
     * 添加
     *
     * @return \think\response\View
     */
   /* public function create()
    {
        return view();
    }*/

    /**
     * 编辑
     *
     * @return \think\response\View
     */
    public function edit(Request $request)
    {
        $store = new StoreModel();
        if ($request->isPost()){
            $datas = $request->post();
            if(!isset($datas['ID']) || intval($datas['ID'])<1 ){
                $this->alerts('无效操作');
            }
            $id = intval($datas['ID']);
            unset($datas['ID']);
            $storeV = new StoreValidate();
            if ($storeV->check($datas)){
                $this->alerts($storeV->getError());
            }
            if($store->save($datas,['ID'=>$id])){
                $this->notice('更新成功');
            }else{
                $this->alerts('更新失败');
            }

        }
        $id = $request->param('id',0,'intval');
        if ($id < 1){
            $this->alerts('无效操作');
        }
        $store_info = $store->getStoreInfoById($id);
        return view('',['storeInfo'=>$store_info]);
    }
    /**
     * 删除商户
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $result = StoreModel::update(['ID'=>intval($id),'IsDel'=>1]);
        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'删除失败'];
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

        $adminTableFields = (new StoreModel())->getTableFields();

        if (isset($requestData['columns'])){
            foreach ($requestData['columns'] as $key=>$value){
                if (!empty($value['data']) && in_array($value['data'],$adminTableFields)){
                    $fields[] =$value['data'];
                }else{
                    unset($requestData['columns'][$key]);
                }
            }
        }
        //搜索栏1
        if (isset($requestData['option_field']) && $requestData['keyword']){
            $whereArr[] = [$requestData['option_field'],'=',$requestData['keyword']];
        }
        //搜索栏2（所属商户）
        if (isset($requestData['agent']) && intval($requestData['agent']) >0){
            $whereArr[] = ['ParentId','=',intval($requestData['agent'])];
        }
        //搜索栏3 （注册时间）
        if(!empty($requestData['date_range'])){
            list($start_time,$end_time) = explode('~',$requestData['date_range']);
            $whereArr[] = ['CreateTime','<= time',trim($end_time)];
            $whereArr[] = ['CreateTime','>= time',trim($start_time)];
        }
        //搜索栏4 （地区搜索）
        //省
        /*if(isset($requestData['province_code']) && intval($requestData['province_code']) > 0){
            $whereArr[] = ['Province','=',intval($requestData['province_code'])];
        }*/
        //市
        /*if(isset($requestData['city_code']) && intval($requestData['city_code']) > 0){
            $whereArr[] = ['City','=',intval($requestData['city_code'])];
        }*/
        //区
        /*if(isset($requestData['district_code']) && intval($requestData['district_code']) > 0){
            $whereArr[] = ['Area','=',intval($requestData['district_code'])];
        }*/
        //分页条件
        if (!empty($requestData['start']))  $pageStart = intval($requestData['start']);
        if (!empty($requestData['length'])) $pageLimit = intval($requestData['length']);
        //表头排序
        if (isset($requestData['order'])){
            foreach ($requestData['order'] as $items) {
                if (isset($requestData['columns'][$items['column']])){
                    $tmpfield = $requestData['columns'][$items['column']]['data'];
                    if (isset($items['dir'])) $tmporder = $items['dir']== 'asc'? 'asc':'desc';
                    $order[$tmpfield] = $tmporder;
                }
            }
        }

        return [
            'fields'=>$fields,
            'where' =>$whereArr,
            'limit_s' =>$pageStart,
            'limit_l' =>$pageLimit,
            'order'=>$order
        ];
    }
    protected function getAgentList(){
        $store = new StoreModel();
        $agent_list = $store->where('ParentId',0)->field('ID,StoreName')->select();
        return $agent_list->toArray();
    }
    /**
     * 数组转换成前台使用的字符串
     *
     * @return string
     */
    protected function arrayToTemplateString($arr,$name,$value){
        $roleStr = "[['name'=>'请选择','value'=>0]";
        foreach ($arr as $item) {
            $roleStr .= ",['name'=>'{$item[$name]}','value'=>{$item[$value]}]";
        }
        $roleStr .= ']';
        return $roleStr;
    }

    /**
     * 获取门店信息
     *
     * @param $id
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_store($id)
    {
        return StoreModel::getSelectArray(false,['ParentId'=>$id],true);
    }
}