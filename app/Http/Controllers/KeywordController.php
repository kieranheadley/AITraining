<?php

namespace App\Http\Controllers;

use App\Models\KeywordIndex;
use App\Models\Keywords;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function flagAssignment(Request $request)
    {
        $keyword = Keywords::find($request->keyword);

        $keyword->assignment_flagged = 1;
        $keyword->assignment_flag_reason = $request->reason;
        $keyword->assignment_flag_notes = $request->notes;
        $keyword->save();

        return redirect('/website/'.$keyword->website_id);
    }

    public function unFlagAssignment($id)
    {
        $keyword = Keywords::find($id);
        $keyword->assignment_flagged = 0;
        $keyword->assignment_flag_reason = null;
        $keyword->assignment_flag_notes = null;
        $keyword->save();

        return redirect('/website/'.$keyword->website_id);
    }

    public function getFlaggedKeywords()
    {
        $keywords = Keywords::where('assignment_flagged', 1)->get();

        return view('keywords.flagged', compact('keywords'));
    }
}
