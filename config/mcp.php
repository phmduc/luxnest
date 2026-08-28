<?php

return [
    // Shared secret required by the MCP endpoint. Empty = endpoint disabled.
    'token' => env('MCP_TOKEN', ''),

    // Server identity reported to MCP clients (ChatGPT, Claude, ...).
    'name'    => env('MCP_SERVER_NAME', 'LuxNest Blog'),
    'version' => '1.0.0',
];
