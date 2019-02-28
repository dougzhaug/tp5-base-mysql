<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 17:12
 */

namespace app\service\Statistics;

class StatisticsService extends StatisticsAbstract
{
    public static function make($scene=false,$time=false)
    {
        if(!$scene){
            return [];
        }
        $data = [];
        if(is_array($scene)){
            foreach ($scene as $statistics){
                $data[$statistics] = self::getData($statistics,$time);
            }
        }
        if(is_string($scene)){
            $data[$scene] = self::getData($scene,$time);
        }
        return $data;
    }

    public static function getData($name,$time){
        switch ($name){
            case 'QuarterAmountLink':
                return QuarterAmountLink::make($time);
                break;
            case 'CountryAmountDonut':
                return CountryAmountDonut::make($time);
                break;
            case 'UserTrendLink':
                return UserTrendLink::make($time);
                break;
            case 'CountryDeviceDonut2':
                return CountryDeviceDonut2::make($time);
                break;
        }
    }

}