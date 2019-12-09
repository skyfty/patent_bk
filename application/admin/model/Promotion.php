<?php

namespace app\admin\model;

use app\admin\library\Auth;

class Promotion extends  \app\common\model\Promotion
{
    // 追加属性
    protected $append = [
    ];

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();

        self::beforeInsert(function($row){
            $maxid = self::withTrashed()->max("id") + 1;
            $row['idcode'] = sprintf("PN%06d", $maxid);
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        });

        self::beforeUpdate(function($row){
            if (isset($row['name'])) {
                $row['slug'] = \fast\Pinyin::get($row['name']);
            }
        });

        self::afterDelete(function($row){
            Procedure::destroy(['promotion_id'=>$row['id']]);
            Expound::destroy(['promotion_model_id'=>$row['id']]);
            Middleware::destroy(['promotion_model_id'=>$row['id']]);
            Provider::destroy(['appoint_promotion_model_id'=>$row['id']]);
            Exlecture::destroy(['promotion_model_id'=>$row['id']]);
            Repertory::destroy(['promotion_model_id'=>$row['id']]);
            Rope::where(['promotion_model_id'=>$row['id']])->delete();
            Lore::where(['promotion_model_id'=>$row['id']])->delete();
            Course::where(['appoint_promotion_model_id'=>$row['id']])->delete();
            Datum::where(['promotion_model_id'=>$row['id']])->delete();
            Warrant::where(['promotion_model_id'=>$row['id']])->delete();
            Merchandise::where(['promotion_model_id'=>$row['id']])->delete();
        });

        self::afterUpdate(function($row){
            $changeData = $row->readonly("updatetime")->getChangedData();
            if (isset($changeData['lore_amount'])) {
                $ropes = model("rope")->where("promotion_model_id", $row->id)->select();
                foreach($ropes as $r) {
                    $r->package->lore_amount = model("lore")->where("promotion_model_id",'in', $r->package->promotion_ids)->group("knowledge_model_id")->count();
                    $r->package->save();
                }
            }
            if (isset($changeData['age']) || isset($changeData['genre_cascader_id'])) {
                $lores = model("lore")->where("promotion_model_id", $row->id)->select();
                foreach($lores as $lore) {
                    $lore->updateLoreGenre();
                }
                model("provider")->where("appoint_promotion_model_id", $row->id)->chunk(100,function($providers)use($row,$lores) {
                    foreach ($providers as $v) {
                        foreach($lores as $lore) {
                            $v->countScholarship($lore['lorerange_cascader_id']);
                        }
                    }
                });
            }
            if (isset($changeData['age'])) {
                $ages = explode(",", $row->age);
                $lores = model("lore")->where("promotion_model_id", $row->id)->select();
                foreach($lores as $lore) {
                    model("LoreGradeAge")->where("lore_model_id", $lore['id'])->delete();
                    foreach($ages as $age) {
                        model("LoreGradeAge")->create(['age'=>$age,'lore_model_id'=>$lore['id'],'grade_model_id'=>$lore['grade_model_id'],]);
                    }
                }
            }

            if (isset($row['deletetime']) && $row['deletetime']) {
                Merchandise::where(['promotion_model_id'=>$row['id']])->delete();
            }

            $merchandise_types = Fields::get(['model_table'=>'merchandise','name'=>'type'],[], true)->content_list;
            foreach($merchandise_types as $type) {
                if (isset($changeData[$type.'_material_price'])) {
                    model("merchandise")->where(['promotion_model_id'=>$row['id'],'type'=>$type])->save('price', $changeData[$type.'_material_price']);
                }
            }
        });

        self::afterInsert(function($row){
            if (isset($row['templet_model_id'])) {
                $row->updateTemplet();
            }

            $warrant_group = model("model_group")->where("branch_model_id", $row->branch_model_id)->where("warrant", 1)->find();
            Warrant::create([
                'branch_model_id'=>$row->branch_model_id,
                'group_model_id'=>$warrant_group->id,
                'promotion_model_id'=>$row->id
            ]);
        });
    }

    protected function updateTemplet($prelectureId = 0, $newlectureid = 0) {
        $prelectures = model("prelecture")->where("templet_model_id", $this['templet_model_id'])->where("pid", $prelectureId)->select();
        foreach($prelectures as $prelecture) {
            $exlecture = Exlecture::create([
                'promotion_model_id'=>$this['id'],
                'weigh'=>$prelecture['weigh'],
                'name'=>$prelecture['name'],
                'pid'=>$newlectureid,
                'status'=>$prelecture['status'],
                'lecture_id'=>$prelecture['lecture_id'],
                'type'=>$prelecture['type'],
            ]);
            $presets = model("preset")->where("prelecture_model_id", $prelecture['id'])->select();
            foreach ($presets as $preset) {
                $data = $preset->getData();
                Expound::create([
                    'exlecture_model_id' => $exlecture['id'],
                    'promotion_model_id' => $this['id'],
                    'name' => $data['name'],
                    'primary' => $data['primary'],
                    'second' => $data['second'],
                    'third' => $data['third'],
                    'detail' => $data['detail'],
                    'entire' => $data['entire'],
                    'weigh' => $data['weigh'],
                ]);
            }
            $this->updateTemplet($prelecture['id'], $exlecture['id']);
        }

    }
}
