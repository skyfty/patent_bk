<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Response;

/**
 * 步骤文档
 *
 * @icon fa fa-circle-o
 */
class Procshutter extends Cosmetic
{
    protected $noNeedLogin =["topdf"];
    public function _initialize()
    {

        parent::_initialize();
        $this->model = new \app\admin\model\Procshutter;
    }


    public function topdf() {
        $row = $this->model->where("id", $this->request->param("id"))->find();
        if (!$row)
            $this->error(__('No Results were found'));

        \PhpOffice\PhpWord\Settings::setDefaultFontName('simsun');
        \PhpOffice\PhpWord\Settings::setPdfRendererPath(ROOT_PATH . '/vendor/dompdf/dompdf/src');
        \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
        $phpWord = \PhpOffice\PhpWord\IOFactory::load(ROOT_PATH . '/public' . $row['file']);
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
        $xmlWriter->save('php://output');
        $response = Response::create();
        $response->contentType("application/pdf");
        $response->header("Cache-Control","max-age=0");
        $response->header("Content-Transfer-Encoding","binary");
        $response->header("Accept-Ranges","bytes");
        return $response;
    }
}
