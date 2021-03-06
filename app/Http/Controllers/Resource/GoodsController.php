<?php

namespace App\Http\Controllers\Resource;

use Auth;
use App\Models\Goods;
use App\Models\Image;
use App\Models\Browse;
use App\Models\Comment;

use App\Events\GoodsBrowsedEvent as browseEvent;

use App\Http\Controllers\Controller;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GoodsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = 0)
    {
        if($id == Auth::id())
            return redirect('/goods');
        else if($id == 0)
            return view('goods.home');
        else
            return view('goods.index', [
                'id' => $id,
            ]);
    }

    public function category(Request $request, $cat = 'all')
    {
        switch($cat) {
            case 'all':
                $data = [
                    'title'     =>      '全部商品',
                    'cat'      =>      'all',
                ];
                break;
            case '食品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'sp',
                ];
                break;
            case '服饰':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'fs',
                ];
                break;
            case '生活用品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'shyp',
                ];
                break;
            case '学习用品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'xxyp',
                ];
                break;
            case '电子产品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'dzcp',
                ];
                break;
            case '体育用品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'tyyp',
                ];
                break;
            case '音乐器材':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'yyqc',
                ];
                break;
                case '非实体商品':
                $data = [
                    'title'     =>      $cat,
                    'cat'      =>      'fstsp',
                ];
                break;
            default:
                return redirect('/goods/category/all');
        }

        $data['keyword'] = $request->input('keyword', '');

        return view('goods.index', $data);
    }

    /**
     * Show a list of the goods of the same type of a user
     * 
     * @param 
     * @return \Illuminate\Http\Response
     */
    public function list($type, $uid)
    {
        if($type == 'sell'){
            $goods = Goods::where('type', 0);
        }else if($type == 'rent') {
            $goods = Goods::where('type', 1);
        }else abort(404);

        $goods = $goods->where('owner', $uid)->orderBy('remain', 'desc')->get();
        
        foreach($goods as $g) {
            $g->thumbnail;
        }

        return $goods;
    }

    public function listComments($id) {
        return Comment::where('gid', $id)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('goods.form', [
            'id'    =>      0,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $contact = \App\User\Contact::find(Auth::id());
        if($contact == [] || $contact->phone == null)
            return "<h2>请先填写联系方式！</h2><script>setTimeout('window.location.href = '/profile'', 5000)</script>";
        $raw = $request->all();
        $message = isValid($raw);
        if($message === true) {
            $goods = [
                'title' => $raw['title'],
                'owner' => Auth::id(),
                'type' => $raw['type'],
                'cost' => $raw['cost'],
                'remain' => $raw['remain'],
                'description' => $raw['description'],
                'cat1' => $raw['category'][0],
                'cat2' => $raw['category'][1],
            ];
            $goodsModel = new Goods($goods);
            $goodsModel->save();
            $id = $goodsModel->id;
            Browse::create(['gid' => $id]);
            
            if($id) {
                $dir = 'public/'.date('Y/M');
                foreach($raw['uploadList'] as $name) {
                    $content = Cache::pull($name);
                    $ext = $content[1];

                    Storage::put("public/tmp/$name.$ext", $content[0]);
                    $path = Storage::putFile($dir, new File("./storage/tmp/$name.$ext"));
                    Storage::delete("public/tmp/$name.$ext");

                    $res = Image::insert([
                        'gid'       =>      $id,
                        'path'      =>      $path,
                        'src'       =>      Storage::url($path),
                    ]);
                    if(!$res) return [
                        'status'    =>      false,
                        'error'     =>      'db error',
                    ];
                }

                return [
                    'status'    =>      true,
                    'id'        =>      $id,
                ];
            }else
                $message = 'db error';
        }
        
        return [
            'status'    =>      false,
            'error'     =>      $message,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  integer      $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $goods = Goods::find($id);
        
        event(new browseEvent(Browse::firstOrNew(['gid' => $id])));
        
        $goods->cost = number_format($goods->cost, 2);
        $goods->images;
        //return $goods;
        return view('goods.home',$goods);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param       integer      $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('goods.form', [
            'id'    =>      $id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Goods  $goods
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $goodsModel = Goods::find($id);
        $images = $goodsModel->images;
        foreach($images as $img) {
            $imgModel = Image::find($img->id);
            $imgModel->delete();
        }

        $raw = $request->all();
        $message = isValid($raw);
        if($message === true) {
            $goodsModel->title = $raw['title'];
            $goodsModel->owner = Auth::id();
            $goodsModel->type = $raw['type'];
            $goodsModel->cost = $raw['cost'];
            $goodsModel->remain = $raw['remain'];
            $goodsModel->description = $raw['description'];
            $goodsModel->cat1 = $raw['category'][0];
            $goodsModel->cat2 = $raw['category'][1];
            
            $goodsModel->save();

            $dir = 'public/'.date('Y/M');
            foreach($raw['uploadList'] as $name) {
                $content = Cache::pull($name);
                Storage::put("public/tmp/$name", $content);
                $path = Storage::putFile($dir, new File("./storage/tmp/$name"));

                $res = Image::create([
                    'gid'       =>      $id,
                    'path'      =>      $path,
                    'src'       =>      Storage::url($path),
                ]);
                if(!$res) return [
                    'status'    =>      false,
                    'error'     =>      'db error',
                ];
            }

            return [
                'status'    =>      true,
                'id'        =>      $id,
            ];
        }
        
        return [
            'status'    =>      false,
            'error'     =>      $message,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Goods  $goods
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $uid = Auth::id();
        $goods = Goods::find($id);
        if($uid != $goods->owner)
            return [
                'status'    =>      false,
                'error'     =>      'no access',
            ];
        
        return [
            'status'        =>      $goods->delete(),
        ];
    }
}

/**
 * function to check whether a valid good form
 * 
 * @param array
 * @return string | boolean
 */
function isValid($goods) {
    if($goods['uploadList'] == [])
        return "please upload at list one image!";

    if(!is_string($goods["title"]))
        return "title is not a string";
    else if(strlen($goods["title"]) > 20)
        return "title is too long";
    
    if(!is_string($goods["description"]))
        return "description is not a string";
    else if(strlen($goods["description"]) < 20
        || strlen($goods["description"]) > 256)
        return "description is no longer than 256 letters and no shorter than 20 letters";

    $arr_cat = [
        "shyp" => [
            "csyp","xxyp","rcyp","qtshyp",
        ],
        "xxyp" => [
            "xjjc","xbbj","fxzl","sjzz","cgyy","qtxxyp",
        ],
        "sp" => [
            "ls","tc","yp","qtsp",
        ],
        "fs" => [
            "fsxm","dzfz","gj","qtfs",
        ],
        "dzcp" => [
            "sj","dn","sjpj","dnpj","qtdzcp",
        ],
        "tyyp" => [
            "qlxg","jsqc","qttyyp",
        ],
        "yyqc" => [
            "xyyq","mzyq","yqpj","qtyyqc",
        ],
        "fstsp" => [
            "hpjh","bjzby","sy","pmsj","rjsj","spjj","pxkc","qtfstsp",
        ],
    ];

    $cat1 = $goods['category'][0];
    $cat2 = $goods['category'][1];

    if(!isset($arr_cat[$cat1]) || !in_array($cat2, $arr_cat[$cat1]))
        return "category does not exist";

    if(!is_numeric($goods["remain"]))
        return "remain is not a number";

    if(!is_numeric($goods["cost"]))
        return "cost is not a number";

    if($goods["cost"] > 99999)
        return "number is no larger than 100 000!";

    if(!is_numeric($goods["type"]))
        return "type is not in format";

    if($goods["type"] > 99999)
    return "number is no larger than 100 000!";

    //todo: tags
    return true;
}