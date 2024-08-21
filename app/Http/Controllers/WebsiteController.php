<?php

namespace App\Http\Controllers;

use App\Models\KeywordIndex;
use App\Models\Websites;
use Illuminate\Support\Facades\Redirect;

class WebsiteController extends Controller
{
    public function index()
    {
        $websites = Websites::get();

        return view('websites.index', compact('websites'));
    }

    public function getWebsite($id)
    {
        $website = Websites::find($id);

        $keywordData = KeywordIndex::whereIn('keyword', $website->keywords->pluck('keyword')->toArray())->get();

        $crawl = collect($website->getCrawledPagesData()->unique('url'));

        return view('websites.show', compact('website', 'keywordData', 'crawl'));
    }

    public function processAssignment($id)
    {
        $website = Websites::find($id);
        $website->process_stage = 1;
        $website->save();

        return Redirect::to('/website/'.$website->id);
    }
}
