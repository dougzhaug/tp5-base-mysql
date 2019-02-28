<?php
/**
 * 公司/商户管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 13:53
 */

namespace app\admin\controller;


use think\Db;
use think\Request;
use app\admin\model\StoreModel;
use app\admin\validate\StoreValidate;

class Seller extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){
            $params = $this->dealParams($request->param());
            $store = new StoreModel();
            $dataArr = $store->getSellerList($params);
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $dataArr['total'],
                'recordsFiltered' => $dataArr['total'],
                'data' => $dataArr['list'],
            ];
        }
        $inputField = "['StoreName'=>'名称','ContactPhone'=>'联系电话','ContactName'=>'联系人']";
        return view('',['inputField'=>$inputField]);
    }

    /**
     * 添加
     *
     * @return \think\response\View
     */
    public function create(Request $request)
    {
        if ($request->isPost()){
            $store_validata = new StoreValidate();
            $store = new StoreModel();
            if (!$store_validata->check($request->param())){
                $this->alerts($store_validata->getError());
            }
            $datas = $request->param();
            $datas['ParentId'] = 0;
            $res = $store->allowField(true)->save($datas);
            if ($res){
                $this->notice('添加成功');
            }else{
                $this->alerts('添加失败');
            }
        }
       $agent = $this->arrayToTemplateString($this->getAgentList(),'AgentName','ID');
        return view('',['agentList'=>$agent]);
    }
    /**
     * 添加门店
     *
     * @return \think\response\View
     */
    public function addstore(Request $request)
    {
        if ($request->isPost()){
            $store_validata = new StoreValidate();
            $store          = new StoreModel();
            if (!$store_validata->check($request->param())){
                $this->alerts($store_validata->getError());
            }
            $res = $store->allowField(true)->save($request->param());
            if ($res){
                $this->notice('添加成功',url('seller'));
            }else{
                $this->alerts('添加失败');
            }
        }
        $id = $request->param('id/d',0);
        if ($id < 1){
            $this->alerts('无效访问');
        }
        $sellerInfo = Db::table('View_store')->field('ID,StoreName,AgentId,AgentName')->where('id',$id)->find();
        return view('store/create',['seller'=>$sellerInfo]);
    }

    /**
     * 编辑
     *
     * @return \think\response\View
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d',0);
        if ($id < 1){
            $this->alerts('无效操作');
        }
        $sroreInfo = StoreModel::get($id);
        $agent = $this->arrayToTemplateString($this->getAgentList(),'AgentName','ID');
        return view('',['storeInfo'=>$sroreInfo,'agentList'=>$agent]);
    }

    /**
     * 保存修改
     *
     * @return \think\response\View
     */
    public function update(Request $request)
    {
        if ($request->isPost()){

            $store_validata = new StoreValidate();
            $store = new StoreModel();
            $datas = $request->param();
            if (!isset($datas['ID']) || intval($datas['ID']) <1){
                $this->alerts('无效操作');
            }
            $id = intval($datas['ID']);
            unset($datas['ID']);
            if (!$store_validata->check($request->param())){
                $this->alerts($store_validata->getError());
            }
            $res = $store->allowField(true)->save($datas,['ID' => $id]);
            if ($res){
                $this->notice('修改成功');
            }else{
                $this->alerts('修改失败');
            }
        }
    }

    /**
     * 上传资质图片
     *
     */
    public function uploadImg(Request $request)
    {
        $file = $this->request->file('file');
        if(!empty($file)){
            $path = 'uploads/store/'.date('Ymd');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->rule('uniqid')->move($path);
            if($info){
                //$info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
               // $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                $photo = $path.'/'.$info->getSaveName();
                return ['code'=>1,'msg'=>'上传成功','photo'=>$photo];
            }else{
                return ['code'=>154,'msg'=>$file->getError(),'photo'=>''];
            }
        }
            return ['code'=>157,'msg'=>'请选择文件','photo'=>''];
    }

    /**
     * 删除商户
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $res = StoreModel::get(['ParentId'=>intval($id)]);
        if ($res){
            return ['errorCode'=>1,'errorMessage'=>'先删除子商户'];
        }
        $result = StoreModel::update(['ID'=>intval($id),'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'删除失败'];
        }
    }


    /**
     * 获取代理商
     *
     */
    protected function getAgentList(){
        $agentList = Db::table('T_Agent')->field('ID,AgentName')->where('IsState',1)->select();
        return $agentList;
    }
    /**
     * 数组转换成前台使用的字符串
     *
     * @return string
     */
    protected function arrayToTemplateString($arr,$name,$value){
        //$roleStr = "[['name'=>'请选择','value'=>0]";
        $roleStr = "[";
        foreach ($arr as $item) {
            $roleStr .= "['name'=>'{$item[$name]}','value'=>{$item[$value]}],";
        }
        if (strlen($roleStr) > 1){
            substr($roleStr,0,-1);
        }
        $roleStr .= ']';
        return $roleStr;
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
        //搜索栏2（公司性质）
        if (isset($requestData['Nature']) && intval($requestData['Nature']) >0){
            $whereArr[] = ['Nature','=',intval($requestData['Nature'])];
        }
        //搜索栏3 （注册时间）
        if(!empty($requestData['date_range'])){
            list($start_time,$end_time) = explode('~',$requestData['date_range']);
            $whereArr[] = ['CreateTime','<= time',trim($end_time)];
            $whereArr[] = ['CreateTime','>= time',trim($start_time)];
        }
        //搜索栏4 （地区搜索）
            //省
        if(isset($requestData['province_code']) && intval($requestData['province_code']) > 0){
            $whereArr[] = ['Province','=',intval($requestData['province_code'])];
        }
            //市
        if(isset($requestData['city_code']) && intval($requestData['city_code']) > 0){
            $whereArr[] = ['City','=',intval($requestData['city_code'])];
        }
            //区
        if(isset($requestData['district_code']) && intval($requestData['district_code']) > 0){
            $whereArr[] = ['Area','=',intval($requestData['district_code'])];
        }
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


    /**
     * 获取商户信息
     *
     * @param $id
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_seller($id)
    {
        return StoreModel::getSelectArray(false,['AgentId'=>$id],true);
    }
}