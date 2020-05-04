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
        \PhpOffice\PhpWord\Settings::setPdfRenderer(
            \PhpOffice\PhpWord\Settings::PDF_RENDERER_MPDF, ROOT_PATH . '/vendor/mpdf/mpdf');
        $phpWord = \PhpOffice\PhpWord\IOFactory::createReader("Word2007")->load(ROOT_PATH . '/public' . $row['file']);

        $uploadDir = "/pdfs/";
        $destFileDir =ROOT_PATH . '/public' . $uploadDir;
        if (!file_exists($destFileDir))
            mkdir($destFileDir);
        $filename = \fast\Random::build("unique").".pdf";
        $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord , 'PDF');
        $pdfWriter->save($destFileDir.$filename);
        $this->redirect($uploadDir.$filename);
    }
}
