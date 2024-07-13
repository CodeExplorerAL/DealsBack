<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPost;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserPostController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => '請登入會員'], 401);
        }

        $user = Auth::user();

        // 自訂驗證錯誤訊息
        $messages = [
            'title.not_regex' => '「標題」不能包含 HTML 標籤。',
            'title.required' => '請填寫「標題」。',
            'title.min' => '「標題」至少需要 :min 個字。',
            'title.max' => '「標題」不能超過 :max 個字。',
            'Article.not_regex' => '「內容」不能包含 HTML 標籤。',
            'Article.required' => '請填寫「內容」。',
            'Article.min' => '「內容」至少需要 :min 個字。',
            'product_tag.required' => '請選擇「產品類別」。',
            'location_tag.required' => '請選擇「地區」。',
            'concessionEnd.required' => '請填寫「優惠結束日」。',
            'concessionEnd.after_or_equal' => '「優惠結束日」 不得早於 「優惠開始日」。',
            'itemImg.required' => '請上傳「圖片」。',
            'itemImg.mimes' => '「圖片」格式必須是 jpeg, png, jpg, 或 gif。',
            'itemImg.max' => '「圖片」大小不能超過 2048 KB。',
        ];
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|min:5|max:255|not_regex:/<\/?[a-z][\s\S]*>/i',
                'Article' => 'required|string|min:10|not_regex:/<\/?[a-z][\s\S]*>/i',
                'ItemLink' => 'nullable|string',
                'product_tag' => 'required|string',
                'location_tag' => 'required|string',
                'itemImg' => 'required|mimes:jpeg,png,jpg,gif|max:2048',
                'concessionStart' => 'nullable|date',
                'concessionEnd' => 'nullable|date|after_or_equal:concessionStart',
            ], $messages);

            $validatedData['title'] = strip_tags($validatedData['title']);
            $validatedData['Article'] = strip_tags($validatedData['Article']);


            $article = new UserPost;
            $article->title = $validatedData['title'];
            $article->Article = $validatedData['Article'];
            $article->ConcessionStart = array_key_exists('concessionStart', $validatedData) ? $validatedData['concessionStart'] : null;
            $article->ConcessionEnd = array_key_exists('concessionEnd', $validatedData) ? $validatedData['concessionEnd'] : null;
            $article->product_tag = $validatedData['product_tag'];
            $article->location_tag = $validatedData['location_tag'];
            $article->UID = $user->UID;
            $article->ItemLink = $validatedData['ItemLink'] ?? null;

            // 自行處理 itemImg 的驗證
            if ($request->hasFile('itemImg')) {
                $file = $request->file('itemImg');
                // 檢查檔案的 MIME 類型
                if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
                    return response()->json(['error' => '圖片格式不支援'], 400);
                }
                $itemImgBase64 = base64_encode(file_get_contents($file->getPathname()));
                $article->ItemIMG = $itemImgBase64;
            } else {
                return response()->json(['error' => '請上傳圖片'], 400);
            }


            $article->user()->associate($user);
            $article->save();

            return response()->json([
                'message' => '文章建立成功',
                'title' => $article->title,
                'Article' => $article->Article,
                'user_name' => $user->name,
                'created_at' => Carbon::parse($article->PostTime)->tz('Asia/Taipei')->format('Y年m月d日 H:i'),
                'updated_at' => $article->ChangeTime ? Carbon::parse($article->ChangeTime)->tz('Asia/Taipei')->format('Y年m月d日 H:i') : null,
                // 圖片(版本一)
                // 'itemImg' => $itemImgBase64,
                // 圖片(版本二和三)
                'itemImg' => $article->ItemIMG,
                'ItemLink' => $article->ItemLink,
                'product_tag' => $article->product_tag,
                'location_tag' => $article->location_tag,
                'concessionStart' => $article->ConcessionStart,
                'concessionEnd' => $article->ConcessionEnd,
                "InProgress" => $article->InProgress,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 確保錯誤訊息格式與前端預期一致
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // 捕獲其他異常,並返回一般的錯誤訊息
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function index()
    {
        $perPage = 150;
        $articles = UserPost::with('user:id,name')
            ->orderBy('PostTime', 'desc')
            ->paginate($perPage);

        $articlesTransformed = $articles->map(function ($article) {
            $itemImgBase64 = base64_encode($article->ItemIMG);

            $title = $article->InProgress === '已過期' ? '<span class="expired-title">[已過期]</span>  ' . $article->Title . '</span>' : $article->Title;

            return [
                'wid' => $article->WID,
                'title' => $title,
                "InProgress" => $article->InProgress,
                'Article' => $article->Article,
                'user_name' => $article->user ? $article->user->name : null,
                'created_at' => Carbon::parse($article->PostTime)->format('Y年m月d日 H:i'),
                'updated_at' => $article->ChangeTime ? Carbon::parse($article->ChangeTime)->format('Y年m月d日 H:i') : null,
                'itemImg' => $itemImgBase64,
                'ItemLink' => $article->ItemLink,
                'product_tag' => $article->product_tag,
                'location_tag' => $article->location_tag,
                'concessionStart' => $article->ConcessionStart,
                'concessionEnd' => $article->ConcessionEnd,
            ];
        });

        return response()->json([
            'data' => $articlesTransformed,
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $perPage,
                'total' => $articles->total(),
            ],
        ]);
    }

    //編輯文章
    public function UpdatePost(Request $request)
    {

        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $id = $decoded_token->id;

        $user = Auth::user();

        $article = new UserPost;
        $article->Title = $request->title;
        $article->Article = $request->Article;
        $article->ConcessionStart = $request->concessionStart;
        $article->ConcessionEnd = $request->concessionEnd;
        $article->product_tag = $request->product_tag;
        $article->location_tag = $request->location_tag;
        $article->ItemLink = $request->ItemLink;

        if ($request->hasFile('itemImg')) {
            $file = $request->file('itemImg');
            $binaryData = file_get_contents($file->getPathname());
            $article->ItemIMG = $binaryData;
        }

        $itemImgBase64 = base64_encode($article->ItemIMG);


        $email = DB::table("users")
            ->join("UserPost", "users.id", "=", "UserPost.UID")
            ->where("users.id", $id)
            ->select("email")
            ->get();


        $original_image = DB::table("UserPost")->select("itemIMG")->where("email", "=", $email);
        $original_Title = DB::table("UserPost")->select("Title")->where("email", "=", $email);
        $original_Article = DB::table("UserPost")->select("Article")->where("email", "=", $email);
        $original_ConcessionStart = DB::table("UserPost")->select("ConcessionStart")->where("email", "=", $email);
        $original_ConcessionEnd = DB::table("UserPost")->select("ConcessionEnd")->where("email", "=", $email);

        $updateData = [];
        if ($article->ItemIMG != "" && ($article->ItemIMG != $original_image)) {
            $updateData["image"] = $article->ItemIMG;
            $src = $article->ItemIMG;
        } else if ($article->ItemIMG = "") {
            $src = $original_image;
        }

        if ($article->Title != "" && ($article->Title != $original_Title)) {
            $updateData["Title"] = $article->Title;
        }

        if ($article->Article != "" && ($article->Article != $original_Article)) {
            $updateData["Article"] = $article->Article;
        }
        if ($article->ConcessionStart != "" && ($article->ConcessionStart != $original_ConcessionStart)) {
            $updateData["ConcessionStart"] = $article->ConcessionStart;
        }
        if ($article->ConcessionEnd != "" && ($article->ConcessionEnd != $original_ConcessionEnd)) {
            $updateData["ConcessionEnd"] = $article->ConcessionEnd;
        }

        DB::table("UserPost")->where("UID", "=", $id)->update($updateData);

        return response()->json([
            'message' => 'Item updated successfully',
        ]);
    }

    //刪除文章
    public function destroy($wid)
    {
        // 查找要刪除的文章
        $article = UserPost::find($wid);

        // 確保文章存在
        if (!$article) {
            return response()->json(['message' => '文章不存在'], 404);
        }

        // 刪除文章
        $article->delete();

        return response()->json(['message' => '文章已刪除'], 200);
    }

    public function show($id)
    {
        $article = UserPost::with('user:id,name')->find($id);

        if (!$article) {
            return response()->json(['error' => '文章不存在'], 404);
        }

        $itemImgBase64 = $article->ItemIMG ? base64_encode($article->ItemIMG) : "";

        $articleTransformed = [
            'wid' => $article->WID,
            'title' => $article->Title,
            'Article' => $article->Article,
            'user_name' => $article->user ? $article->user->name : null,
            'user_id' => $article->user ? $article->user->id : null, // 添加用户ID
            'created_at' => Carbon::parse($article->PostTime)->format('Y年m月d日 H:i'),
            'updated_at' => $article->ChangeTime ? Carbon::parse($article->ChangeTime)->format('Y年m月d日 H:i') : null,
            'itemImg' => $itemImgBase64,
            'ItemLink' => $article->ItemLink,
            'product_tag' => $article->product_tag,
            'location_tag' => $article->location_tag,
            'concessionStart' => $article->ConcessionStart,
            'concessionEnd' => $article->ConcessionEnd,
        ];

        return response()->json($articleTransformed);
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'product_tag' => 'nullable|string',
            'location_tag' => 'nullable|string',
        ]);

        $query = UserPost::query();

        if ($request->has('keyword')) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('Article', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->has('product_tag')) {
            $query->where('product_tag', $request->product_tag);
        }

        if ($request->has('location_tag')) {
            $query->where('location_tag', $request->location_tag);
        }

        $articles = $query->with('user:id,name')->orderBy('PostTime', 'desc')->paginate(10);

        $articlesTransformed = $articles->map(function ($article) {
            $itemImgBase64 = base64_encode($article->ItemIMG);

            return [
                'title' => $article->Title,
                'Article' => $article->Article,
                'user_name' => $article->user ? $article->user->name : null,
                'created_at' => Carbon::parse($article->PostTime)->format('Y年m月d日 H:i'),
                'updated_at' => $article->ChangeTime ? Carbon::parse($article->ChangeTime)->format('Y年m月d日 H:i') : null,
                'itemImg' => $itemImgBase64,
                'ItemLink' => $article->ItemLink,
                'product_tag' => $article->product_tag,
                'location_tag' => $article->location_tag,
                'concessionStart' => $article->ConcessionStart,
                'concessionEnd' => $article->ConcessionEnd,
            ];
        });

        return response()->json([
            'data' => $articlesTransformed,
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}
