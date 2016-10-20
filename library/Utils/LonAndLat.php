<?php
/**
 * 工具类 - 关于 经纬度的计算
 *
 * @package Utils
 */
final class Utils_LonAndLat
{
    /**
     * 获取两个点之间的距离 - 根据经纬度
     *
     * $point1 = array('lat' => 40.770623, 'long' => -73.964367);
     * $point2 = array('lat' => 40.758224, 'long' => -73.917404);
     * $distance = Fz_Utils_LonAndLat::getDistanceBetweenPointsNew($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
     * foreach ($distance as $unit => $value) {
     *     echo $unit.': '.number_format($value,4).'<br />';
     * }
     *
     * @param float $latitude1 经度
     * @param float $longitude1 维度
     * @param float $latitude2 经度
     * @param float $longitude2 维度
     * @return array
     */
    public static function getDistanceBetweenPoints($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $theta = $longitude1 - $longitude2;
        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet  = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters     = $kilometers * 1000;
        return compact('miles','feet','yards','kilometers','meters');
    }

}
