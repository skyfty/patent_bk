<?php

namespace app\common\model;

use think\Model;

class Payconfirm extends Account
{
    public function amount() {

    }

    public function add($data = []) {
        if ($this->validate) {
            $validate = $this->validate;
            if (!$this->validateData($data, $validate)) {
                return false;
            }
        }

        $db     = $this->getQuery();
        $db->startTrans();
        try {
            $result = $this->isUpdate(false)->allowField(true)->save($data);
            if ($result) {
                $db->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}
