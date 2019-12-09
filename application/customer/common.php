<?php
if (!function_exists('rand_span_lable')) {

    /**
     * 生成下拉列表
     * @param string $name
     * @param mixed $options
     * @param mixed $selected
     * @param mixed $attr
     * @return string
     */
    function rand_span_lable()
    {
        $l = ['default','primary','success','info','warning','danger',];
        return $l[rand(0,5)];
    }
}

function subtraction($v1, $v2) {
    return $v1 - $v2;
}

function residue_time($endtime) {
    $intDiff = $endtime - time();
    $day = 00;$hour = 00;$minute = 00;$second = 00;
    if ($intDiff > 0) {
        $day = floor($intDiff / (60 * 60 * 24));
        $hour = floor($intDiff / (60 * 60)) - ($day * 24);
        $minute = floor($intDiff / 60) - ($day * 24 * 60) - ($hour * 60);
        $second = floor($intDiff) - ($day * 24 * 60 * 60) - ($hour * 60 * 60) - ($minute * 60);
    }
    if ($minute <= 9) $minute = '0'.$minute;
    if ($second <= 9) $second = '0'.$second;
    return [$day,$hour, $minute, $second];
}