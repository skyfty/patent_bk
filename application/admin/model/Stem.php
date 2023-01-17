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
            $row['owners_model_id'] =  $row['creator_model_id'] = $auth->isLogin() ? $auth->id : 1;
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

    private function insertWealth($title, $href) {
        $uri = \GuzzleHttp\Psr7\UriResolver::resolve(\GuzzleHttp\Psr7\uri_for($this['link']), \GuzzleHttp\Psr7\uri_for($href))."";
        $snapshot_file = $this->snapshot($uri);
        $wealth = model("wealth")->where("url", $uri)->find();
        if (!$wealth) {
            model("wealth")->create([
                "name"=>$title,
                "url"=>$uri,
                "stem_model_id"=>$this['id'],
                "type"=>"pdf",
                "content"=>$snapshot_file
            ]);
        }
    }

    private function parseXingtaiGovCn($content) {
        \phpQuery::newDocument($content);
        foreach(pq('.rgtlistconerji .titlename a') as $hui) {
            $link = pq($hui);
            $href = $link->attr('href');
            $title = $link->text();
            $title = substr($title, 0,  strpos($title, "\n"));
            $this->insertWealth($title, $href);
        }
    }

    private function parseChengdeGovCn($content) {
        $pos = strpos($content, "<recordset>");
        $pos2 = strpos($content, "</recordset>");
        $content = substr($content, $pos, $pos2 - $pos + 12);
        $xml=simplexml_load_string($content);
        foreach($xml->children() as $child) {
            if ($child->getName() == "div") {
                continue;
            }
            $child_content = $child."";
            $pos = strpos($child_content, "href='");
            $pos2 = strpos($child_content, "'title='");
            $pos3 = strpos($child_content, "'target");
            $href = substr($child_content, $pos + 6, $pos2 - $pos - 6);
            $title = substr($child_content, $pos2 + 7, $pos3 - $pos2 - 7);
            $this->insertWealth($title, $href);
        }
    }

    public function parseDocument($content) {
        $pqselect = null;
        switch($this['link']) {
            case "http://fzgg.tj.gov.cn/zwgk/zcfg/wnwj/qt/": {
                $pqselect = '.gg_list ul.hui14 li a';
                break;
            }
            case "http://www.beijing.gov.cn/zhengce/zhengcefagui/": {
                $pqselect = '.listBox ul.list li a';
                break;
            }
            case "http://hdskjj.hd.gov.cn/web/xxgklist.aspx?newstag=hdszcfg": {
                $pqselect = '.STYLE25 a';
                break;
            }
            case "http://xingtai.gov.cn/xxgk/zfwj/xzz/": {
                return $this->parseXingtaiGovCn($content);
            }
            case "http://www.tangshan.gov.cn/zhuzhan/zfwj/": {
                $pqselect = '.open_list li a';
                break;
            }
            case "http://www.chengde.gov.cn/col/col3263/index.html?number=C10006C00006": {
                return $this->parseChengdeGovCn($content);
            }
            case "http://new.sjz.gov.cn/col/1490941083922/index.html": {
                $pqselect = '.news_box ul li a';
                break;
            }
        }
        if ($pqselect) {
            \phpQuery::newDocument($content);
            foreach(pq($pqselect) as $hui) {
                $link = pq($hui);
                $href = $link->attr('href');
                if ($link->attr("title")) {
                    $title = $link->attr('title');
                } else {
                    $title = $link->text();
                }
                $this->insertWealth($title, $href);
            }
        }
    }

    public function dispatch() {
        $client = new \GuzzleHttp\Client();
        echo  $this['link'] ."\r\n";
        $response = $client->request('GET', $this['link']);
        if ($response->getStatusCode() == 200) {
            try{
                $this->parseDocument($response->getBody());

            }catch(\Exception $e) {
                echo  $e->getMessage() ."\r\n";

            }
        }
    }
}
