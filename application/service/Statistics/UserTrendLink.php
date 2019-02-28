<?php
/**
 * 季度交易金额统计--线型
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 17:24
 */

namespace app\service\Statistics;


use app\admin\model\StatisticsDateModel;

class UserTrendLink extends StatisticsAbstract
{
    public static function make($scene = false,$time=false)
    {
        $amount = StatisticsDateModel::where('Years','between',[date('Y', strtotime('-3 year')),date('Y')])
            ->where('Title','user_trend')
            ->group('Quarter')
            ->column('sum(Value) as Value','Quarter');

        $data = [];
        foreach ($amount as $key=>$val){
            $data[] = [
                'y'=> substr_replace($key,' Q',4,0),
                'user_trend' => $val,
            ];
        }

        return $data;
    }
}