<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 步骤文档
 *
 * @icon fa fa-circle-o
 */
class Procshutter extends Cosmetic
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Procshutter;
    }


    public function topdf() {
        $row = $this->model->where("id", $this->request->param("id"))->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $tempfile =  ROOT_PATH . '/public' . $row['file'];
        \PhpOffice\PhpWord\Settings::setPdfRendererPath(ROOT_PATH . '/vendor/tcpdf/src');
        \PhpOffice\PhpWord\Settings::setPdfRendererName('MPDF');
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempfile);
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
        $xmlWriter->save('result.pdf');
    }
}
