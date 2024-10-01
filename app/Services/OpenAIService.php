<?php

namespace App\Services;

use App\Models\KeywordIndex;
use App\Models\Logs;
use Illuminate\Support\Facades\Http;
use OpenAI;

class OpenAIService
{

    public function generateEmbeddings($text)
    {
        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->embeddings()->create([
            'model' => 'text-embedding-3-large',
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding ?? [];
    }

    public function selectPageFromEmbeddings($keyword, $pages, $website): object|string
    {
        $string = 'Primary Location: ' . ($website->primary_location ?? '') . "\n\n";
        $string .= 'Keyword: ' . $keyword->keyword . "\n\n";

        foreach ($pages as $page) {
            $string .= "Page URL: ".$page->url."\n";
            $string .= "Page Title: ".$page->title."\n";
            $string .= "Meta Description: ".$page->meta_desc."\n";
            $string .= "Headings: ".implode(",", $page->h1_headings).",".implode(",", $page->h2_headings)."\n\n";
        }

        $string .= "Ranking Websites\n\n";
        foreach ($keyword->ranking_sites as $ranking_site) {
            $string .= "Url: ".$ranking_site['url']."\n";
            $string .= "Title: ".$ranking_site['title']."\n\n";
        }

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-2024-08-06',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Role: You are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices. \n\nTask Overview: You will receive key details for up to five pages on the website most relevant to the supplied keyword. This information includes the page URL, title, meta description, and headings. Your task is to determine whether the keyword is best optimised on an existing page or if a new page is necessary. If a primary location is provided, ensure it is prioritised in your decision. You will also be provided a list of the top 10 websites currently showing in Google for that query, use this to help determine the type of page which Google prioritises for the keyword.\n\nEvaluate the suitability of each page using the following criteria: \n\nContent Relevance: Ensure the page content closely matches the keyword. \n\nLocation Relevance: \n- If the keyword contains the supplied primary location then it should be reviewed without the location being taken into consideration. (e.g. primary location = Leicester, the keyword \"Kitchen Fitting Leicester\" would be assigned to /kitchen-fitting) \n- Optimise keywords containing the primary location on national pages (e.g., /, /service, /product). Ignore location when deciding, focusing only on the keyword. \n- Optimise keywords with other locations on location-specific pages (e.g., /{location} or /{product}-{location}). \n- For multiple locations, treat them as related if geographically close and under 100,000 population. Otherwise, recommend separate pages. \n\nKeyword Intent: Match the intent (informational, transactional, or mixed) with the type of page. Use broad transactional keywords on the homepage; suggest blog posts for informational keywords. You can also use the supplied list of ranking websites to just the correct intent based on how Google ranks websites.\n\n“Near Me” Keywords: Always recommend creating a new page for “near me” or similar variations. \n\nGeneric Pages: Never optimise keywords on generic pages like “About Us”, \"Terms & Conditions\" or “Contact Us”. \n\nResponse: Please respond in a JSON object format containing: \n ["assigned_page" => "Page URL or \'new page\' if none of the supplied pages are suitable.", "reason" => "Provide a brief explanation as to the reason the page has been chosen"] \n\nExample Response: ["assigned_page" => "https://website.co.uk", "reason" => "This page is chosen because the content is highly relevant, and the keyword intent aligns with the page type."]',
                ],
                [
                    'role' => 'user',
                    'content' => $string,
                ],
            ],
            'temperature' => 1,
            'max_tokens' => 2048,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'assignment',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'assignment' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'assigned_page' => [
                                            'type' => 'string',
                                        ],
                                        'reason' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => ["assigned_page", "reason"],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['assignment'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        if (!empty($response->choices)) {
            Logs::create(['log' => $response->choices[0]->message->content]);
            return json_decode($response->choices[0]->message->content);
        } else {
            return 'error';
        }
    }

    public function selectKeywordForExistingPage($keywords, $page): string
    {
        // TODO: Ensure that near me keywords are not selected
        $string = 'Keywords: ';

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

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10). You will also receive information about the page, including its URL, page title, meta description, and headings.\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n3. Transactional Relevancy: While important, this factor should be considered after search volume and difficulty. Evaluate how relevant each keyword is to the transactional intent of the page, based on the provided page information.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array in the format: ["keyword 1", "keyword 2", "keyword 3"].',
                ],
                [
                    'role' => 'user',
                    'content' => $string,
                ],
            ],
            'temperature' => 0.75,
            'max_tokens' => 500,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_object',
            ],
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }
    }

    public function groupKeywordsForNewPage($keywords)
    {
        $string = 'Keywords: '.implode(', ', $keywords);

        dd($string);

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Role: \nYou are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will be provided with a list of comma-separated keywords. Your task is to:\n\n1. Group Keywords by Surface-Level Similarity:\n - Identify and group keywords based on surface-level similarities to enhance SEO effectiveness. For example, keywords like \"builders\" and \"building\" should be grouped together, while \"plastering\" would be in a separate group. Ensure that you return all keywords as part of the response.\n- Only group very similar keywords, there should only be up to 3 keywords per page unless the similarity of keywords is very high.\n- You should not group local keywords and non-local keywords on the same page. For example, \"builders leicester\" should not be on the same page as \"builders\".\n2. Prioritise Location-Based Grouping (When Applicable):\n - If any of the keywords include a location, prioritize grouping them by location.\n - Further, if there are distinctly different services within the same location, create separate groups for each service and location. e.g. /builders-leicester and /plumbers-leicester\n3. Generate SEO-Optimised Page URLs:\n - For each group of keywords, generate a corresponding SEO-optimized page URL that accurately reflects the grouped keywords.\n - Ensure the URL is structured in a way that supports search engine optimisation.\n\nResponse:\nYour response must be a JSON array of keywords keyed by your generated page path. For example;\n\n<input>\nbuilder leicester, builder rugby, plastering leicester, builders leicester, house builder leicester.\n</input>\n\n<response>\n[ \"/builders-leicester\" => [ \"builder leicester\", \"builders leicester\", \"house builder leicester\" ], \"/builders-rugby\" => [ \"builder rugby\" ], \"/plastering-leicester\" => [ \"plastering leicester\" ]]\n</response>\n\nImportant! Ensure that every keyword supplied is returned in your response.",
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $string,
                        ],
                    ],
                ],
            ],
            'temperature' => 1,
            'max_tokens' => 2500,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_object',
            ],
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }
    }

    public function selectKeywordForNewPage($keywords): string
    {
        $string = 'Keywords: ';

        foreach ($keywords as $keyword) {

            $data =  KeywordIndex::where('keyword', $keyword)
                ->where('language', 'en')
                ->where('country', 'GB')
                ->first();

            $difficulty = (!$data || $data->difficulty == 999) ? 'Unknown' : $data->difficulty;
            $search_volume = (!$data) ? 0 : $data->search_volume;

            $string .= $keyword.' (Search Volume: '.$search_volume.', Difficulty: '.$difficulty.'), ';
        }

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10).\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n\nIf all supplied keywords have no search volume or difficulty try and make an educated guess which one will have the highest number of searches a month.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array (with no key) in the format: [\"keyword 1\", \"keyword 2\", \"keyword 3\"].',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $string,
                        ],
                    ],
                ],
            ],
            'temperature' => 0.75,
            'max_tokens' => 2048,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_object',
            ],
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }
    }

    public function getSearchIntent($keywords)
    {
        $string = implode(', ', $keywords);

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini-2024-07-18',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are a content specialist with vast experience in classifying keywords. When classifying a list of keywords, it is important to identify the intent behind each keyword. There are four primary types of keyword intent: informational, navigational, commercial, and transactional. Here are the definitions and examples of each type to help you accurately classify the keywords: \n\n1. Informational Intent Definition: Keywords that indicate the user is looking to learn more about a topic or find an answer to a specific question. These users are seeking information and are typically at the early stages of their search. \nExamples: \n- \"Coffee calories\" \n- \"National coffee day\" \n- \"What is the difference between cold brew and iced coffee\" \n- \"How to make cold brew coffee\" \n- \"History of coffee\" \n\n2. Navigational Intent Definition: Keywords that indicate the user is trying to find a specific brand, website or webpage. These users know exactly where they want to go and are using the search engine to navigate to that location. \nExamples: \n- \"YouTube\" \n- \"Hike blog\" \n- \"Where is Angelino\'s coffee located\" \n- \"Facebook login\" - \"BBC news\" \n\n3. Commercial Intent Definition: Keywords that indicate the user is looking to research brands, products, or services. These users are in the consideration stage, comparing options, reading reviews, or looking for specific features. \nExamples: \n- \"Free coffee samples\" \n- \"Dunkin iced coffee flavors\" \n- \"Peet\'s cold brew vs Starbucks cold brew\" \n- \"Best home coffee makers\" \n- \"Top rated coffee grinders\" \n\n4. Transactional Intent Definition: Keywords that indicate the user is ready to make a purchase or take a specific action. These users have a clear intent to buy, subscribe, or perform another transaction. \nExamples: \n- \"Buy crypto online\" \n- \"Sandwich places near me that deliver\" \n- \"Pickup truck for sale\" \n- \"Subscribe to coffee club\" \n- \"Buy neon blue unisex watch\" \n\nTask: For each keyword in your list, determine which of the four intents it represents: informational, navigational, commercial, or transactional. Use the definitions and examples above as a guide to accurately classify each keyword.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $string,
                        ],
                    ],
                ],
            ],
            'temperature' => 1,
            'max_tokens' => 16383,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'intents',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword_intent' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'keyword' => [
                                            'type' => 'string',
                                        ],
                                        'intent' => [
                                            'type' => 'string',
                                            'enum' => ['Transactional', 'Informational', 'Commercial', 'Navigational'],
                                        ],
                                    ],
                                    'required' => ['keyword', 'intent'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'required' => ['keyword_intent'],
                        'additionalProperties' => false,
                    ],
                ],
            ]
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }
    }

    public function getLocationInKeyword($keywords)
    {
        $string = implode(', ', $keywords);

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'ft:gpt-4o-mini-2024-07-18:hikeseo:location-identification:9wrkxrtL',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are a local SEO specialist experienced in selecting the best keywords for local SEO campaigns. \n\nTask \nYour task is to review a list of comma separated keywords and select any keywords that are targeting specific geographic locations like \"Plumber Coventry\", \"Web Design UK\", \"Liverpool schools\" or \"Events in London yesterday\". \n\nExample: \n• Input: Plumber Rugby, Builder in Leicester, Leicester Builders, Plastering, Window Fitters, Drain Unblocking, Rugby Boots, Web Design India, Local businesses, local seo campaigns, local market optimization \n\n• Response: [\"Plumber Rugby\", \"Builder in Leicester\", \"Leicester Builders\", \"Web Design India\"] \n\nImportant Notes: \n1. Important! If you find that 0 keywords contain a location please return an empty array, do not create new keywords! \n2. Exclude general or vague terms that do not clearly reference a specific physical location, like \"local\", \"near me\", \"nearby\", \"neighbourhood\". Return the identified keywords as a JSON array. Do not create, modify, or infer keywords; only return the keywords as supplied.',
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $string,
                        ],
                    ],
                ],
            ],
            'temperature' => 0.2,
            'max_tokens' => 16000,
            'top_p' => 0.75,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'contains-location',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'keywords' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'required' => ['keywords'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }

    }
}
