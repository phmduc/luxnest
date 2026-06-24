<?php

if (! function_exists('asset_v')) {
    /**
     * Like asset(), but appends a filemtime-based version query string so
     * browsers fetch a fresh copy whenever the file's contents change,
     * instead of serving a stale cached copy after deploys.
     */
    function asset_v(string $path): string
    {
        $fullPath = public_path($path);
        $version  = file_exists($fullPath) ? filemtime($fullPath) : time();

        return asset($path).'?v='.$version;
    }
}
