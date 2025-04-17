<?php
defined('INC_ROOT') || die;

/**
 * Markdown-Parser-Integration für SimpleFeed
 */

// Parsedown-Bibliothek laden, falls noch nicht geschehen
if (!class_exists('Parsedown')) {
    require_once __DIR__ . '/../lib/Parsedown.php';
}

/**
 * Konvertiert Markdown zu HTML mit Parsedown
 * 
 * @param string $markdown Der Markdown-Text
 * @return string Das generierte HTML
 */
function sf_parseMarkdown(string $markdown): string {
    static $parser = null;
    
    // Singleton-Pattern für Parsedown-Instanz
    if ($parser === null) {
        $parser = new Parsedown();
        // Sicherer Modus, um XSS zu verhindern
        if (method_exists($parser, 'setSafeMode')) {
            $parser->setSafeMode(true);
        }
    }
    
    return $parser->text($markdown);
}

/**
 * Prüft, ob der Text Markdown enthält
 * 
 * @param string $text Der zu prüfende Text
 * @return bool True, wenn der Text Markdown-Strukturen enthält
 */
function sf_isMarkdown(string $text): bool {
    // Prüfen auf typische Markdown-Strukturen
    return (
        // Headers
        preg_match('/^#{1,6}\s/m', $text) ||
        // Listen
        preg_match('/^-\s/m', $text) ||
        preg_match('/^\*\s/m', $text) ||
        preg_match('/^\d+\.\s/m', $text) ||
        // Blockquotes
        preg_match('/^>\s/m', $text) ||
        // Code-Blöcke
        preg_match('/^```/m', $text) ||
        // Links
        preg_match('/\[.+?\]\(.+?\)/', $text) ||
        // Emphasis
        preg_match('/(\*\*|__).+?(\*\*|__)/', $text) ||
        preg_match('/(\*|_).+?(\*|_)/', $text)
    );
}

/**
 * Detektiert automatisch, ob der Inhalt Markdown ist und konvertiert entsprechend
 * 
 * @param string $content Der Textinhalt
 * @param bool $isMarkdown Optional: Explizit als Markdown behandeln
 * @return string Konvertierter Inhalt
 */
function sf_processContent(string $content, bool $isMarkdown = null): string {
    // Wenn $isMarkdown nicht angegeben wurde, automatisch erkennen
    if ($isMarkdown === null) {
        $isMarkdown = sf_isMarkdown($content);
    }
    
    if ($isMarkdown) {
        return sf_parseMarkdown($content);
    } else {
        // Wenn kein Markdown, dann als HTML behandeln - trotzdem sanitizen
        return sf_sanitizeHTML($content);
    }
}
