<?php

namespace App\Services;

use App\Models\KeywordIndex;
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

    public function selectPageFromEmbeddings($keyword, $pages): string
    {
        $string = 'Keyword: '.$keyword->keyword."\n\n";
        foreach ($pages as $page) {
            $string .= 'Page URL: '.$page->url."\nPage Title: ".$page->title."\nMeta Description: ".$page->meta_desc."\nHeadings: ".implode(', ', str_replace('\n', '', $page->h1_headings)).', '.implode(', ', str_replace('\n', '', $page->h2_headings))."\n\n";
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
                            'text' => 'Role: You are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will receive the following information for each page on the website:\n• Page URL\n• Page Title\n• Meta Description\n• Headings\n\nObjective:\nYour main goal is to determine whether the provided keyword is best optimised on an existing page or if a new page should be created. If the user supplies a primary location, ensure that this location is prioritised in your decision-making. Evaluate the suitability of each page using the following criteria:\n\n1. Content Relevance: Does the page content closely match the keyword?\n2. Location Relevance:\n - If a primary location is provided by the user, ensure that the keyword is optimised accordingly:\n - - Only keywords containing the primary location (and not locations within the primary location) may be optimised on national pages (e.g., /service, /product).\n - - Keywords with other locations must be optimised on location-specific pages (e.g., /{location} or /{product}-{location}).\n - If no primary location is provided, proceed with the general location relevance rule: If the keyword includes a location, ensure the page targets both the keyword and the specific location. For example, “emergency plumber in Broxbourne” should correspond to /emergency-plumbing-broxbourne/ rather than /emergency-plumbing.\n- If no location specific pages exist please respond with \"new page\".\n3. Keyword Intent: Does the intent behind the keyword (informational, transactional, or mixed) align with the type of page?\n4. “Near Me” Keywords: Always recommend creating a new page for keywords containing “near me” or similar variations (e.g., “plumbers near me”). Do not assign these to an existing page.\n\nIt is crucial that the keyword and the selected page have a strong relevance. If none of the provided pages are suitable, recommend creating a “new page.”\n\nAdditional Guidelines:\n- Generic Pages: Do not optimise keywords on pages such as “About Us” or “Contact.”\n- Homepage Keywords: Use broad transactional keywords representing the business’s core services on the homepage. For informational keywords, suggest creating a blog post.\n- Multiple Locations: When multiple locations are involved, consider them sufficiently related if they are geographically close and have populations under 100,000. Otherwise, recommend separate pages.\n\nPlease respond using the following format:\n- Page URL or “new page” if none of the supplied pages are suitable.\n- Reason for your decision: Provide a brief explanation, separated by a hyphen.\n\nExample Response:\nhttps://website.co.uk - This page is chosen because the content is highly relevant, and the keyword intent aligns with the page type.',
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
            'temperature' => 0.7,
            'max_tokens' => 2048,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        if ($response->choices) {
            return $response->choices[0]->message->content;
        } else {
            return 'error';
        }
    }

    public function selectKeywordForExistingPage($keywords, $page): string
    {
        $string = 'Keywords: ';

        foreach ($keywords as $keyword) {
            $data =  KeywordIndex::where('keyword', $keyword)
                ->where('language', 'en')
                ->where('country', 'GB')
                ->first();

            $difficulty = ($data->difficulty == 999) ? 'Unknown' : $data->difficulty;

            $string .= $keyword.' (Search Volume: '.$data->search_volume.', Difficulty: '.$difficulty.'), ';
        }

        $string .= "\n\nPage URL: ".$page->url."\nPage Title: ".$page->title."\nMeta Description: ".$page->meta_desc."\nHeadings: ".implode(', ', str_replace('\n', '', $page->h1_headings)).', '.implode(', ', str_replace('\n', '', $page->h2_headings));

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10). You will also receive information about the page, including its URL, page title, meta description, and headings.\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n3. Transactional Relevancy: While important, this factor should be considered after search volume and difficulty. Evaluate how relevant each keyword is to the transactional intent of the page, based on the provided page information.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array in the format: [\"keyword 1\", \"keyword 2\", \"keyword 3\"].',
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
        $string = 'Keyword: '.implode(', ', $keywords);

        $client = OpenAI::client(config('services.openai.key'));
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Role: \nYou are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will be provided with a list of comma-separated keywords. Your task is to:\n\n1. Group Keywords by Surface-Level Similarity:\n - Identify and group keywords based on surface-level similarities to enhance SEO effectiveness.\n - For example, keywords like “builders” and “building” should be grouped together, while “plastering” would be in a separate group.\n2. Prioritise Location-Based Grouping (When Applicable):\n - If any of the keywords include location information, prioritize grouping them by location.\n - Further, if there are distinctly different services within the same location, create separate groups for each service and location.\n3. Generate SEO-Optimised Page URLs:\n - For each group of keywords, generate a corresponding SEO-optimized page URL that accurately reflects the grouped keywords.\n - Ensure the URL is structured in a way that supports search engine optimisation, considering factors such as keyword relevance and clarity.\n\nResponse:\nYour response must be a JSON array of keywords keyed by your generated page path. For example;\n\nInput: builder leicester, builder rugby, plastering leicester, builders leicester, house builder leicester.\n\nOutput: [ \"/builders-leicester\" => [ \"builder leicester\", \"builders leicester\", \"house builder leicester\" ], \"/builders-rugby\" => [ \"builder rugby\" ], \"/plastering-leicester\" => [ \"plastering leicester\" ]]',
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

            $difficulty = ($data->difficulty == 999) ? 'Unknown' : $data->difficulty;

            $string .= $keyword.' (Search Volume: '.$data->search_volume.', Difficulty: '.$difficulty.'), ';
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
                            'text' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10).\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array (with no key) in the format: [\"keyword 1\", \"keyword 2\", \"keyword 3\"].',
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
}
