<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 17:28
 */

namespace app\admin\controller;

use app\admin\model\IndustryModel;
use app\admin\validate\IndustryValidate;
use think\Request;

class Industry extends Auth
{
    public function index(Request $request)
    {
        if ($request->isPost()){
            $industry =  new IndustryModel();
            $params = $this->dealParams($request->post());
            $list = $industry->getIndustryList($params);

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $list['total'],
                'recordsFiltered' => $list['total'],
                'data' => $list['list'],
            ];
        }
        $inputField = "['IndustryName'=>'名称']";
        return view('',['inputField'=>$inputField]);
    }

    /*处理参数
    */
    protected function dealParams($requestData){
        $fields    = []; //表格显示字段
        $whereArr  = []; //筛选搜索条件
        $pageLimit = 10; //每页条数
        $pageStart = 0;  //起始页
        $order     = []; //排序

        $IndustryTableFields = (new IndustryModel())->getTableFields();

        if (isset($requestData['columns'])){
            foreach ($requestData['columns'] as $key=>$value){
                if (!empty($value['data']) && in_array($value['data'],$IndustryTableFields)){
                    $fields[] =$value['data'];
                }else{
                    unset($requestData['columns'][$key]);
                }
            }
        }
        //搜索栏1
        if (isset($requestData['option_field']) && $requestData['keyword']){
            $requestData['keyword'] = '%'.$requestData['keyword'].'%';
            $whereArr[] = [$requestData['option_field'],'like',$requestData['keyword']];
        }
        //搜索栏2（状态）
        if (isset($requestData['IsState']) && intval($requestData['IsState']) < 2){
            $whereArr[] = ['IsState','=',intval($requestData['IsState'])];
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
     * 添加
     *
     */
    public function create(Request $request)
    {
        $industry = new IndustryModel();
        if ($request->isPost()){
            $validate = new IndustryValidate();
            if (!$validate->check($request->post())) {
                 $this->alerts($validate->getError());
             }
            $result = $industry->save($request->post());
            if($result){
                $this->notice('添加成功',url('Industry'));
            }else{
                $this->alerts('添加失败');
            }
        }

        $parent_id  = intval($request->id);
        $frist_industry = $industry->where('ParentId',0)->select();
        $frist_industry = $this->arrayToTemplateString($frist_industry,'IndustryName','Id');

        return view('',['industryStr'=>$frist_industry,'parent_id'=>$parent_id]);
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
     * 编辑页面
     *
     */
    public function edit(Request $request)
    {
        if ($request->isPost()){
            $validate = new IndustryValidate();
            if (!$validate->check($request->post())) {
                $this->alerts($validate->getError());
            }
            $industry = new IndustryModel();
            $result = $industry->save($request->post(),['Id'=>$request->post('id/d')]);
            if($result){
                $this->notice('修改成功',url('Industry'));
            }else{
                $this->alerts('修改失败'.$industry->getError());
            }
        }
        if(intval($request->id)< 1){
            $this->alerts('行业不存在');
        }
        $frist_industry = IndustryModel::all(['ParentId'=>0]);
        $frist_industry = $this->arrayToTemplateString($frist_industry,'IndustryName','Id');
        $industry_info = IndustryModel::get(intval($request->id));
        return view('',['industry_info'=>$industry_info,'industryStr'=>$frist_industry]);
    }

    /**
     * 删除
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $res = IndustryModel::get(['ParentId'=>intval($id)]);
        if ($res){
            return ['errorCode'=>1,'errorMessage'=>'先删除子行业'];
        }
        $result = IndustryModel::update(['Id'=>intval($id),'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'删除失败'];
        }
    }
}