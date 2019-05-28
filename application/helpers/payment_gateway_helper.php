<?php defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('paymentType')) {
    function paymentType($paymentType = 'ATMNCVS'){
        $payment = array(
            'ATM'=> 'ATM',
            'CREDIT'=> '信用卡',
            'CVS'=> '超商代碼',
            'FAMI'=> '全家',
            'IBON'=> '7-11',
            'ATMNCVS'=> 'CVS & ATM'
        );
        $str = $payment[strtoupper($paymentType)];
        return $str;
    }
}
//會員層級
if(!function_exists('memberGroup')) {
    function memberGroup($i = null){
        $str = '';
        $group = array('一般會員','黃金會員','白金會員','藍鑽會員');
        if(is_numeric($i)){
            if($i < count($group) && $i >= 0){
                $str= $group[$i];
            }else{
                $str= $group[0];
            }
            return $str;
        }else{
            return $group;
        }
    }
}
//會員標籤顏色
if(!function_exists('memberLabel')) {
    function memberLabel($i){
        $label = array('label-default','label-warning','label-danger','label-primary');
        if(is_numeric($i)){
            if($i < count($label) && $i >= 0){
                $str= $label[$i];
            }else{
                $str= $label[0];
            }
        }else{
            $str= $label[0];
        }
        return $str;
    }
}
