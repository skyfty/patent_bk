<?php

namespace app\admin\model;

use app\admin\library\Auth;
use Composer\Util\Url;
use Symfony\Component\HttpFoundation\Request;
use think\Loader;
use think\Model;

class Stem extends   \app\common\model\Stem
{
// 追加属性

    protected static function init()
    {
        self::beforeInsert(function($row){
            $auth = Auth::instance();
            $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
        });
        parent::init();
    }

    private function snapshot($uri) {
        $suffix = "pdf";
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
        ];
        $savekey = '/snapshots/{year}{mon}{day}/{filemd5}{.suffix}';
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);
        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);

        $destFileDir =ROOT_PATH . '/public' . $uploadDir;
        if (!file_exists($destFileDir))
            mkdir($destFileDir);
        $filename = \fast\Random::build("unique").".".$suffix;

        $pdfexec = "wkhtmltopdf " . $uri . " " . $destFileDir.$filename;
        exec($pdfexec);
        return $uploadDir.$filename;
    }

    public function dispatch() {
        $cutime = time();

        Loader::import('phpQuery.phpQuery');

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $this['link']);
        if ($response->getStatusCode() == 200) {
            \phpQuery::newDocument($response->getBody());
            foreach(pq('.gg_list ul.hui14 li a') as $hui) {
                $link = pq($hui);
                $href = $link->attr('href');
                $title = $link->html();
                $uri = \GuzzleHttp\Psr7\UriResolver::resolve(\GuzzleHttp\Psr7\uri_for($this['link']), \GuzzleHttp\Psr7\uri_for($href))."";
                $snapshot_file = $this->snapshot($uri);
                model("wealth")->create([
                    "name"=>$title,
                    "url"=>$uri,
                    "stem_model_id"=>$this['id'],
                    "type"=>"pdf",
                    "content"=>$snapshot_file
                ]);
            }
        }
    }
}
