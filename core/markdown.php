<?php
defined('INC_ROOT') || die;

/**
 * Markdown Parser Integration for SimpleFeed
 */
global $Wcms;

/**
 * Load Parsedown library if needed
 * 
 * @return bool True if Parsedown is available, false otherwise
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
                $Wcms->log('SimpleFeed: Parsedown library not found', 'warning');
            }
            return false;
        }
    }

    return true;
}

/**
 * Convert Markdown to HTML using Parsedown
 * Provides basic fallback conversion if Parsedown is not available
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
        
        // Basic fallback conversion if Parsedown is not available
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $markdown); // Bold
        $html = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $html); // Italic
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/s', '<a href="$2">$1</a>', $html); // Links
        $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html); // H1
        $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html); // H2
        $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $html); // H3
        $html = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $html); // List items
        $html = preg_replace('/\n\n/s', '</p><p>', $html); // Paragraphs
        $html = '<p>' . $html . '</p>'; // Wrap in paragraphs
        
        // Final sanitization
        return $Wcms->purify($html);
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
 * Process content based on format (Markdown or HTML)
 * 
 * This function detects or uses the specified format to process content.
 * For Markdown content, it uses Parsedown to convert to HTML.
 * For HTML content, it ensures proper sanitization.
 *
 * @param string $content The text content to process
 * @param bool|null $isMarkdown Optional: Explicitly treat as Markdown
 * @return string Processed and sanitized HTML content
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
