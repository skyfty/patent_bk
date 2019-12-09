<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use app\admin\model\Account;

trait Gatherer
{
    public function amount($nac) {
        if (!isset($nac['reckon_model_id']))
            return;
        //支出
        $where = [
            "reckon_type"=>$this->name,
            "reckon_model_id"=>$nac->reckon_model_id
        ];
        $payacount = Account::hasWhere('cheque',['mold'=>-1])->where($where)->sum("money");

        //收入
        $incomeamount = Account::hasWhere('cheque',['mold'=>1])->where($where)->sum("money");
        $balance = $incomeamount - $payacount;
        $cash = $balance;

        $this->isUpdate(true)->allowField(true)->save([
            'balance'  => $balance,
            'cash'  => $cash,
            'payamount' => $payacount,
            'incomeamount' => $incomeamount
        ],['id' => $nac->reckon_model_id]);
    }
}