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

        $uploadDir = "/pdfs/";
        $destFileDir =ROOT_PATH . '/public' . $uploadDir;
        if (!file_exists($destFileDir))
            mkdir($destFileDir);
        $inputfile = ROOT_PATH . '/public' . $row['file'];
        $pdfcmd = "libreoffice --headless --convert-to pdf:writer_pdf_Export ".$inputfile." --outdir ".$destFileDir;
        system($pdfcmd);
        $filename = substr($row['file'], strrpos($row['file'], "/") + 1);
        $filename = substr($filename, 0, strpos($filename, ".")).".pdf";
        echo($uploadDir.$filename);
        $this->redirect($uploadDir.$filename);
    }
}
