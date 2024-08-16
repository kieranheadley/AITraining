<?php

namespace App\Http\Controllers;

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

        return view('websites.show', compact('website'));
    }

    public function processAssignment($id)
    {
        $website = Websites::find($id);
        $website->process_stage = 1;
        $website->save();

        return Redirect::to('/website/'.$website->id);
    }
}
