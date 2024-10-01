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
            $systemPrompt = 'Role: You are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices. \n\nTask Overview: You will receive key details for up to five pages on the website most relevant to the supplied keyword. This information includes the page URL, title, meta description, and headings. Your task is to determine whether the keyword is best optimised on an existing page or if a new page is necessary. If a primary location is provided, ensure it is prioritised in your decision. You will also be provided a list of the top 10 websites currently showing in Google for that query, use this to help determine the type of page which Google prioritises for the keyword.\n\nEvaluate the suitability of each page using the following criteria: \n\nContent Relevance: Ensure the page content closely matches the keyword. \n\nLocation Relevance: \n- If the keyword contains the supplied primary location then it should be reviewed without the location being taken into consideration. (e.g. primary location = Leicester, the keyword \"Kitchen Fitting Leicester\" would be assigned to /kitchen-fitting) \n- Optimise keywords containing the primary location on national pages (e.g., /, /service, /product). Ignore location when deciding, focusing only on the keyword. \n- Optimise keywords with other locations on location-specific pages (e.g., /{location} or /{product}-{location}). \n- For multiple locations, treat them as related if geographically close and under 100,000 population. Otherwise, recommend separate pages. \n\nKeyword Intent: Match the intent (informational, transactional, or mixed) with the type of page. Use broad transactional keywords on the homepage; suggest blog posts for informational keywords. You can also use the supplied list of ranking websites to just the correct intent based on how Google ranks websites.\n\n“Near Me” Keywords: Always recommend creating a new page for “near me” or similar variations. \n\nGeneric Pages: Never optimise keywords on generic pages like “About Us”, \"Terms & Conditions\" or “Contact Us”. \n\nResponse: Please respond in a JSON object format containing: \n ["assigned_page" => "Page URL or \'new page\' if none of the supplied pages are suitable.", "reason" => "Provide a brief explanation as to the reason the page has been chosen"] \n\nExample Response: ["assigned_page" => "https://website.co.uk", "reason" => "This page is chosen because the content is highly relevant, and the keyword intent aligns with the page type."]';
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
            $systemPrompt = 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10). You will also receive information about the page, including its URL, page title, meta description, and headings.\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n3. Transactional Relevancy: While important, this factor should be considered after search volume and difficulty. Evaluate how relevant each keyword is to the transactional intent of the page, based on the provided page information.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array in the format: ["keyword 1", "keyword 2", "keyword 3"].';

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
