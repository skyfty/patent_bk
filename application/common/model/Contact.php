<?php

namespace app\common\model;

use fast\Random;
use think\Model;
use traits\model\SoftDelete;

class Contact extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deletetime';

    // 表名
    protected $name = 'contact';
    // 追加属性
    protected $append = [
    ];
    use \app\common\library\traits\Addressfield;
}

