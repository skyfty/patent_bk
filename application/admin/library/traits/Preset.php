<?php
/**
 * Created by PhpStorm.
 * User: feitianyu
 * Date: 8/16/2018
 * Time: 17:31
 */

namespace app\admin\library\traits;

use fast\Tree;

trait Preset
{
    public function update($ids = null) {
        $params = $this->request->post("row/a");
        if (!$params || !isset($params['primary'])) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = formatPresetParams($params);

        try {
            $row = $this->model->get($ids);
            if ($row) {
                $result = $row->allowField(true)->save($params);
            } else {
                $result = $this->model->allowField(true)->save($params);
                if ($result !== false) {
                    $list = $this->model->where([
                        'promotion_model_id'=>$params['promotion_model_id'],
                        'exlecture_model_id'=>$params['exlecture_model_id']])->order("weigh desc")->select();
                    $cnt = count($list);
                    foreach($list as $k=>$v) {
                        $weigh = $cnt--;
                        $v->save(['weigh'=>$weigh]);
                    }
                }
            }
            if ($result !== false) {
                $this->success("", null, $this->model->toArray());
            } else {
                $this->error($this->model->getError());
            }
        } catch (\think\exception\PDOException $e) {
            $this->error($e->getMessage());
        } catch (\think\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}