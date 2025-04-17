<?php
/**
 * SimpleFeed Plugin - Default Configuration
 * 
 * These default settings are used when no custom settings are found.
 * Custom settings are stored in data/settings.json and override these defaults.
 * 
 * @return array Default configuration values
 */
return [
    // Date format for displaying post dates (PHP date format)
    'date_format' => 'd.m.Y',
    
    // Number of posts to show before "Show more" button
    'show_more_limit' => 4,
    
    // Whether to display image thumbnails in post lists
    'use_thumbnails' => true,
    
    // Set Markdown as the default format for new posts
    'default_markdown' => true,
];
