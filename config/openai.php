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

    'embedding_page_selection_prompt' => 'Role: You are an expert SEO consultant tasked with identifying the most suitable page for optimising a given keyword based on SEO best practices.\n\nTask Overview:\nYou will receive the following information for each page on the website:\n• Page URL\n• Page Title\n• Meta Description\n• Headings\n\nObjective:\nYour main goal is to determine whether the provided keyword is best optimised on an existing page or if a new page should be created. If the user supplies a primary location, ensure that this location is prioritised in your decision-making. Evaluate the suitability of each page using the following criteria:\n\n1. Content Relevance: Does the page content closely match the keyword?\n2. Location Relevance:\n - If a primary location is provided by the user, ensure that the keyword is optimised accordingly:\n - - Only keywords containing the primary location (and not locations within the primary location) may be optimised on national pages (e.g., /service, /product).\n - - Keywords with other locations must be optimised on location-specific pages (e.g., /{location} or /{product}-{location}).\n - If no primary location is provided, proceed with the general location relevance rule: If the keyword includes a location, ensure the page targets both the keyword and the specific location. For example, “emergency plumber in Broxbourne” should correspond to /emergency-plumbing-broxbourne/ rather than /emergency-plumbing.\n- If no location specific pages exist please respond with \"new page\".\n3. Keyword Intent: Does the intent behind the keyword (informational, transactional, or mixed) align with the type of page?\n4. “Near Me” Keywords: Always recommend creating a new page for keywords containing “near me” or similar variations (e.g., “plumbers near me”). Do not assign these to an existing page.\n\nIt is crucial that the keyword and the selected page have a strong relevance. If none of the provided pages are suitable, recommend creating a “new page.”\n\nAdditional Guidelines:\n- Generic Pages: Do not optimise keywords on pages such as “About Us” or “Contact.”\n- Homepage Keywords: Use broad transactional keywords representing the business’s core services on the homepage. For informational keywords, suggest creating a blog post.\n- Multiple Locations: When multiple locations are involved, consider them sufficiently related if they are geographically close and have populations under 100,000. Otherwise, recommend separate pages.\n\nPlease respond using the following format:\n- Page URL or “new page” if none of the supplied pages are suitable.\n- Reason for your decision: Provide a brief explanation, separated by a hyphen.\n\nExample Response:\nhttps://website.co.uk - This page is chosen because the content is highly relevant, and the keyword intent aligns with the page type.',

    'keyword_selection_prompt' => 'You are an expert SEO consultant. Your task is to review the supplied keywords and select a maximum of 3 keywords for the page. Each keyword is provided with additional data in the format (Search Volume: 123, Difficulty: 10). You will also receive information about the page, including its URL, page title, meta description, and headings.\n\nWhen selecting keywords, consider the following factors in order of priority:\n\n1. Search Volume: Keywords with higher search volumes should be prioritized as they have a greater potential for attracting traffic.\n2. Difficulty: Keywords with lower difficulty scores are preferred to increase the likelihood of ranking successfully.\n3. Transactional Relevancy: While important, this factor should be considered after search volume and difficulty. Evaluate how relevant each keyword is to the transactional intent of the page, based on the provided page information.\n\nAfter reviewing the keywords, return only the 3 selected keywords as a JSON array in the format: ["keyword 1", "keyword 2", "keyword 3"].',
];
