<?php
/**
 * 国家交易金额统计--甜甜圈
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 17:24
 */

namespace app\service\Statistics;

class CountryAmountDonut extends StatisticsAbstract
{
    public static function make($scene = false,$time=false)
    {
        $data = [
            [
                'label' => '中国',
                'value'=> 12052759
            ],
            [
                'label' => '泰国',
                'value'=> 2500131
            ],
            [
                'label' => '新加坡',
                'value'=> 5074024
            ],
            [
                'label' => '印度尼西亚',
                'value'=> 1960380
            ]
        ];

        return $data;
    }
}