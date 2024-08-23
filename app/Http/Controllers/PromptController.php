<?php

namespace App\Http\Controllers;

use App\Models\KeywordIndex;
use App\Models\Keywords;

class PromptController extends Controller
{
    public function showPrompt($prompt_id, $keyword_id)
    {
        $keyword = Keywords::find($keyword_id);

        $crawl = collect($keyword->website->getCrawledPagesData()->unique('url'));

        // 1 Select page from embeddings
        if($prompt_id == 1) {
            $systemPrompt = config('openai.embedding_page_selection_prompt');
            $pages = [];
            foreach ($keyword->embedding_results as $page) {
                $pages[] = $crawl->where('path', $page['url'])->first();
            }

            $string = 'Keyword: '.$keyword->keyword."\n\n";

            foreach ($pages as $page) {
                $string .= 'Page URL: '.$page->url."\nPage Title: ".$page->title."\nMeta Description: ".$page->meta_desc."\nHeadings: ".implode(', ', str_replace('\n', '', $page->h1_headings)).', '.implode(', ', str_replace('\n', '', $page->h2_headings))."\n\n";
            }

            $userPrompt = $string;
        } elseif ($prompt_id == 2) {
            $systemPrompt = config('openai.keyword_selection_prompt');

            $page = $crawl->where('path', $keyword->assigned_page)->first();

            $string = 'Keywords: ';

            $keywords = Keywords::where('website_id', $keyword->website_id)->where('assigned_page', $keyword->assigned_page)->pluck('keyword')->toArray();

            foreach ($keywords as $keyword) {
                $data =  KeywordIndex::where('keyword', $keyword)
                    ->where('language', 'en')
                    ->where('country', 'GB')
                    ->first();

                $difficulty = (!$data || $data->difficulty == 999) ? 'Unknown' : $data->difficulty;
                $search_volume = (!$data) ? 0 : $data->search_volume;

                $string .= $keyword.' (Search Volume: ' . $search_volume . ', Difficulty: '.$difficulty.'), ';
            }

            $string .= "\n\nPage URL: ".$page->url."\nPage Title: ".$page->title."\nMeta Description: ".$page->meta_desc."\nHeadings: ".implode(', ', str_replace('\n', '', $page->h1_headings)).', '.implode(', ', str_replace('\n', '', $page->h2_headings));

            $userPrompt = $string;
        }


        // 2 Select keyword for existing page

//        selectPageFromEmbeddings


//        selectKeywordForExistingPage


//        $systemPrompt
//        $userPrompt

        return view('prompts.show', compact('systemPrompt', 'userPrompt'));
    }
}
