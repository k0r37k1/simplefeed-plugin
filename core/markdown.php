<?php
defined('INC_ROOT') || die;

/**
 * Markdown Parser Integration for SimpleFeed
 */
global $Wcms;

/**
 * Load Parsedown library if needed
 */
function sf_loadParsedown() {
    global $Wcms;
    
    // Get plugin directory path (defined in functions.php)
    if (function_exists('sf_getPluginPath')) {
        $pluginPath = sf_getPluginPath();
    } else {
        $pluginPath = __DIR__ . '/..';
    }

    if (!class_exists('Parsedown')) {
        $parsedownPath = $pluginPath . '/lib/Parsedown.php';

        if (file_exists($parsedownPath)) {
            require_once $parsedownPath;
        } else {
            if (method_exists($Wcms, 'log')) {
                $Wcms->log('SimpleFeed: Parsedown library not found', 'danger');
            }
            return false;
        }
    }

    return true;
}

/**
 * Convert Markdown to HTML using Parsedown
 *
 * @param string $markdown The Markdown text
 * @return string The generated HTML
 */
function sf_parseMarkdown(string $markdown): string {
    global $Wcms;

    if (!sf_loadParsedown()) {
        if (method_exists($Wcms, 'log')) {
            $Wcms->log('SimpleFeed: Failed to parse Markdown - Parsedown library not available', 'warning');
        }
        return $Wcms->purify($markdown); // Fallback to just purifying the input
    }

    static $parser = null;

    // Singleton pattern for Parsedown instance
    if ($parser === null) {
        $parser = new Parsedown();
        // Safe mode to prevent XSS
        if (method_exists($parser, 'setSafeMode')) {
            $parser->setSafeMode(true);
        }
    }

    $html = $parser->text($markdown);

    // Additional purification using WonderCMS if needed
    if (method_exists($Wcms, 'purify')) {
        $html = $Wcms->purify($html);
    }

    return $html;
}

/**
 * Check if text contains Markdown
 *
 * @param string $text The text to check
 * @return bool True if the text contains Markdown structures
 */
function sf_isMarkdown(string $text): bool {
    // Check for typical Markdown structures
    return (
        // Headers
        preg_match('/^#{1,6}\s/m', $text) ||
        // Lists
        preg_match('/^-\s/m', $text) ||
        preg_match('/^\*\s/m', $text) ||
        preg_match('/^\d+\.\s/m', $text) ||
        // Blockquotes
        preg_match('/^>\s/m', $text) ||
        // Code blocks
        preg_match('/^```/m', $text) ||
        // Links
        preg_match('/\[.+?\]\(.+?\)/', $text) ||
        // Emphasis
        preg_match('/(\*\*|__).+?(\*\*|__)/', $text) ||
        preg_match('/(\*|_).+?(\*|_)/', $text)
    );
}

/**
 * Auto-detect whether content is Markdown and convert accordingly
 *
 * @param string $content The text content
 * @param bool $isMarkdown Optional: Explicitly treat as Markdown
 * @return string Converted content
 */
function sf_processContent(string $content, bool $isMarkdown = null): string {
    global $Wcms;

    // Auto-detect if not specified
    if ($isMarkdown === null) {
        $isMarkdown = sf_isMarkdown($content);
    }

    if ($isMarkdown) {
        return sf_parseMarkdown($content);
    } else {
        // If not Markdown, treat as HTML but still sanitize
        if (method_exists($Wcms, 'purify')) {
            return $Wcms->purify($content);
        } else {
            // Fallback to basic sanitization
            $allowed_tags = '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><a><strong><em><blockquote><pre><code><img><table><tr><td><th>';
            return strip_tags($content, $allowed_tags);
        }
    }
}
