<?php

namespace app\admin\model;


use app\admin\library\Auth;

class Equipment extends \app\common\model\Equipment
{
    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::afterInsert(function($row){
            $warehouse = Warehouse::create([
                'pid'=>12,
                'name'=>$row['name'],
                'status'=>'locked',
                'model_type'=>'equipment',
                'model_id'=>$row['id'],
            ]);
            if ($warehouse) {
                \app\admin\model\Assembly::create([
                    'name'=>'主体',
                    'status'=>'locked',
                    'creator_model_id'=>$warehouse['creator_model_id'],
                    'warehouse_model_id'=>$warehouse['id'],
                    'body'=>1,
                ]);
                $row->save(['warehouse_id'=>$warehouse['id']]);
            }
        });
        self::afterDelete(function($row){
            model("warehouse")->destroy($row['warehouse_id']);
        });

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("MA%06d", $maxid);
        });
    }


    public function warehouse()
    {
        return $this->morphMany('warehouse', 'model');
    }


}
