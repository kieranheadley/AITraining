<?php

namespace App\Http\Controllers;

use App\Models\KeywordIndex;

class LocalKeywordController extends Controller
{
    public function index()
    {
        $keywords = KeywordIndex::where('location_in_keyword', 1)
            ->where('location_reviewed', 0)
            ->get();

        return view('local-keywords.index', compact('keywords'));
    }

    public function reviewKeyword($keyword, $decision)
    {
        $keyword = KeywordIndex::find($keyword);

        if ($decision == 'incorrect') {
            $keyword->location_in_keyword = 0;
        } else {
            $keyword->location_in_keyword = 1;
        }

        $keyword->location_reviewed = 1;
        $keyword->save();

        return redirect('/local-keywords');
    }
}
