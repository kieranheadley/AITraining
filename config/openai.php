<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

//    'embedding_page_selection_prompt' => 'Role:\nYou are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will receive key details for up to five pages on the website most relevant to the supplied keyword. This information includes the page URL, title, meta description, and headings.\nYour task is to determine whether the keyword is best optimised on an existing page or if a new page is necessary. If a primary location is provided, ensure it is prioritised in your decision.\n\nEvaluate the suitability of each page using the following criteria:\nContent Relevance: Ensure the page content closely matches the keyword.\nLocation Relevance: \n - If the keyword contains a the supplied primary location then it should be reviewed without the location being taken into consideration. (e.g. primary location = Leicester, the keyword \"Kitchen Fitting Leicester\" would be assigned to /kitchen-fitting)\n - Optimise keywords containing the primary location on national pages (e.g., /, /service, /product). Ignore location when deciding, focusing only on the keyword.\n - Optimise keywords with other locations on location-specific pages (e.g., /{location} or /{product}-{location}).\n - For multiple locations, treat them as related if geographically close and under 100,000 population. Otherwise, recommend separate pages.\nKeyword Intent: Match the intent (informational, transactional, or mixed) with the type of page. Use broad transactional keywords on the homepage; suggest blog posts for informational keywords.\n“Near Me” Keywords: Always recommend creating a new page for “near me” or similar variations.\nGeneric Pages: Never optimise keywords on generic pages like “About Us”, \"Terms & Conditions\" or “Contact Us”.\n\nResponse:\nPlease respond using the following format:\n- Page URL or “new page” if none of the supplied pages are suitable.\n- Reason for your decision: Provide a brief explanation, separated by a hyphen, like the below.\n\n[page_url|new page] - Reason\n\nExample Response:\nhttps://website.co.uk - This page is chosen because the content is highly relevant, and the keyword intent aligns with the page type.',
//
//    'keyword_selection_prompt' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10). You will also receive information about the page, including its URL, page title, meta description, and headings.\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n3. Transactional Relevancy: While important, this factor should be considered after search volume and difficulty. Evaluate how relevant each keyword is to the transactional intent of the page, based on the provided page information.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array in the format: ["keyword 1", "keyword 2", "keyword 3"].',
];
