<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UrlShortens;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UrlShortensController extends Controller
{
    public function getList(Request $request)
    {
        $query = $request->input('q');
        $sort_fields = explode(",", $request->input('order-by'));
        $page = empty($request->input('page')) ? 1 : $request->input('page');
        $page_size = empty($request->input('page-size')) ? 5 : $request->input('page-size');
        $offset =  ($page - 1) * $page_size;

        $url_shortens = DB::table('url_shortens');
        if (!empty($query)) {
            $url_shortens = $url_shortens
                ->where('url', 'like', "%$query%")
                ->orWhere('short_code', 'like', "%$query%");
        }
        if (in_array('hits', $sort_fields)) {
            $url_shortens = $url_shortens->orderByDesc('hits');
        }
        if (in_array('expiration_date', $sort_fields)) {
            $url_shortens = $url_shortens->orderBy('expiration_date');
        }

        $count = $url_shortens->count();

        $url_shortens = $url_shortens
            ->offset($offset)
            ->limit($page_size)
            ->get();

        $result = [
            "pages" => ceil($count / $page_size),
            "list" => $url_shortens
        ];

        return response()->json($result, 201);
    }

    public function getById(UrlShortens $url_shorten)
    {
        return $url_shorten;
    }

    public function create(Request $request)
    {
        $params = $request->all();
        $params['expiration_date'] = $request['expiration_date'] ? Carbon::parse($request['expiration_date']) : null;
        $url =  $params['url'];
        $url_shorten = UrlShortens::where('url', $url)->first();

        $url_pattern = '/https?:\/\/(www\.)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/'; // $url_pattern = '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/';

        if (empty($url_shorten)) {
            // validate url pattern
            if (!preg_match($url_pattern, $url, $m)) {
                throw ValidationException::withMessages(['URL has invalid pattern']);
            }

            // validate blacklist
            $blacklist = array('/example.com/', '/microsoft.com/');
            foreach ($blacklist as $stop) {
                if (preg_match($stop, $url)) {
                    throw ValidationException::withMessages(['URL is blacklisted']);
                }
            }

            $params['short_code'] = substr(md5(uniqid(rand(), true)), 0, 6);
            $params['hits'] = 0;
            $params['is_deleted'] = false;
            $url_shorten = UrlShortens::create($params);
        } else {
            $url_shorten->expiration_date = $params['expiration_date'];
            $url_shorten->save();
        }

        Redis::set($url_shorten->short_code, $url_shorten);

        return response()->json($url_shorten, 201);
    }

    public function visit($short_code)
    {
        $cache_url_shorten = Redis::get($short_code);

        if (is_null($cache_url_shorten)) {
            $url_shorten = UrlShortens::where('short_code', $short_code)->first();
        } else {
            $url_shorten = json_decode($cache_url_shorten, true);
        }


        $is_deleted = $url_shorten['is_deleted'];
        $expiration_date = isset($url_shorten['expiration_date']) ? $url_shorten['expiration_date'] : null;
        $is_expired = Carbon::now()->gt(Carbon::parse($expiration_date));

        if (empty($url_shorten)) {
            throw new ModelNotFoundException();
        } else if ($is_deleted) {
            return response()->json([
                'error' => 'deleted'
            ], 410);
        } else if ($is_expired) {
            return response()->json([
                'error' => 'expired'
            ], 410);
        } else {
            UrlShortens::where('short_code', $short_code)
                ->update(['hits' => $url_shorten['hits'] + 1]);
        }

        return Redirect::to($url_shorten['url'], 302);
    }

    public function toggleDelete(Request $request)
    {
        $params = $request->all();
        $url_shorten = UrlShortens::find($params['id']);
        if (empty($url_shorten)) {
            throw new ModelNotFoundException();
        } else {
            $url_shorten->is_deleted = $params['is_deleted'];
            $url_shorten->save();

            Redis::del($url_shorten['short_code']);
        }

        return response()->json($url_shorten, 200);
    }
}


// public function delete(Request $request)
// {
//     $params = $request->all();
//     UrlShortens::destroy($params['id']);

//     return response()->json(null, 204);
// }