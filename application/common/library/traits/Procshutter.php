<?php
namespace app\common\library\traits;

trait Procshutter
{
    public function download($ids) {
        $row = $this->model->where("id",$ids)->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $tempDirName = \fast\Random::build("unique");
        $tempPath = TEMP_PATH .$tempDirName;
        mkdir(iconv('utf-8','gb2312',$tempPath));

        $catalogs = model("catalog")->where("model", strtolower($this->model->raw_name))->select();
        foreach($catalogs as $catalog) {
            $outpath = $tempPath;
            $outpath.="/".$catalog->full_name;
            if (!file_exists($outpath)) {
                @mkdir(iconv('utf-8','gb2312',$outpath), 0755, true);
            }
        }
        $procshutters = model("procshutter")->where([
            "relevance_model_type"=>strtolower($this->model->raw_name),
            "relevance_model_id"=> $row['id'],
            "status"=> "normal",
        ])->select();

        foreach($procshutters as $procshutter) {
            $file = $procshutter['file'];
            if ($file == null)
                continue;
            $srcfile = ROOT_PATH . 'public' .$file;
            if (!file_exists($srcfile)) {
                continue;
            }
            $outpath = $tempPath;
            if ($procshutter->shuttering_model_id) {
                $shuttering = $procshutter->shuttering;
                if ($shuttering && $shuttering->catalog_model_id) {
                    $outpath.="/".$shuttering->catalog->full_name;
                }
            }

            if (!file_exists($outpath)) {
                @mkdir(iconv('utf-8','gb2312',$outpath), 0755, true);
            }
            $pi = pathinfo($file);
            $newfile = $outpath."//".$procshutter['name'].".".$pi['extension'];
            $newfile = iconv('utf-8','gb2312',$newfile);
            copy($srcfile,$newfile);
        }

        $procshutterdir = '/procshutter/'.$tempDirName.".zip";
        $destFileDir =ROOT_PATH . '/public' . $procshutterdir;
        system(sprintf("cd %s && zip -q -r %s .",$tempPath, $destFileDir), $status);
        rmdirs($tempPath, true);
        $this->redirect($procshutterdir);
    }
}