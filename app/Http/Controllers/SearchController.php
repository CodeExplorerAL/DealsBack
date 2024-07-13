<?php

namespace App\Http\Controllers;

require_once "C:/xampp/htdocs/Prologin2/vendor/autoload.php";

use App\Http\Controllers\Controller;
use App\Models\Search;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\YuenArticle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Jieba::init();
Finalseg::init();


class SearchController extends Controller
{
    public function search(Request $request)
    {

        // Models版本
        $seg_list = Jieba::cutForSearch($request->search); #搜索引擎模式
        // return $seg_list;
        $searchlist = array();
        foreach ($seg_list as $key => $value) { //第一個迴圈 我去匹配文章
            echo "斷詞: " . $value . "\n";
            $search = YuenArticle::where("Title", "like", "%" . $value . "%")->get();

            foreach ($search as $value) { //第二個迴圈 匹配到的文章 加入到空陣列
                array_push($searchlist, $value);
            }
        }

        $searchlist = array_unique($searchlist);
        echo "下面是回傳";
        echo "\n";

        return response()->json([

            'search' => $searchlist,
        ]);
    }
}
