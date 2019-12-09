<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\common\library\traits;

use app\common\model\Account;

trait Gatherer
{
    public function amount() {
        //支出
        $where = [
            "reckon_type"=>$this->name,
            "reckon_model_id"=>$this->id
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
        ]);
    }
}