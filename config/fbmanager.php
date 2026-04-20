<?php

return [
    'default_admin_username' => env('DEFAULT_ADMIN_USERNAME', 'admin'),
    'spreadsheet_id' => env('SPREADSHEET_ID'),
    'sheet_id' => env('SHEET_ID'),
    'google_credentials_path' => env('GOOGLE_CREDENTIALS_PATH'),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'groq_api_key' => env('GROQ_API_KEY'),
    'cache_ttl' => 300,

    // Facebook Graph API
    'facebook_page_id' => env('FACEBOOK_PAGE_ID'),
    'facebook_page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    'facebook_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v25.0'),
];
