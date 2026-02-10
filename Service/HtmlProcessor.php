<?php

namespace React\React\Service;

use Psr\Log\LoggerInterface;

/**
 * Process HTML content using proper DOM parser instead of regex
 */
class HtmlProcessor
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Remove Magento init scripts from HTML using DOM parser
     * 
     * @param string $html
     * @return string
     */
    public function removeMagentoInitScripts(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        try {
            // Use DOMDocument for proper HTML parsing
            $dom = new \DOMDocument();
            
            // Suppress warnings for malformed HTML
            libxml_use_internal_errors(true);
            
            // Load HTML with UTF-8 encoding
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            // Clear errors
            libxml_clear_errors();
            
            // Find all script tags
            $xpath = new \DOMXPath($dom);
            $scripts = $xpath->query('//script[@type="text/x-magento-init"]');
            
            // Remove matching scripts
            foreach ($scripts as $script) {
                $script->parentNode->removeChild($script);
            }
            
            // Return modified HTML
            return $dom->saveHTML();
            
        } catch (\Exception $e) {
            $this->logger->warning('Failed to parse HTML with DOM parser, falling back to regex', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to regex if DOM parsing fails
            return preg_replace('/<script[^>]+type=["\']text\/x-magento-init["\'][^>]*>.*?<\/script>/is', '', $html);
        }
    }

    /**
     * Move scripts to bottom of HTML, preserving no-defer scripts
     * 
     * @param string $html
     * @return string
     */
    public function moveScriptsToBottom(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            $scripts = $xpath->query('//script[not(@no-defer)]');
            
            $scriptsToMove = [];
            foreach ($scripts as $script) {
                // Clone the node before removing
                $scriptsToMove[] = $script->cloneNode(true);
                $script->parentNode->removeChild($script);
            }
            
            // Find body tag and append scripts
            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body) {
                foreach ($scriptsToMove as $script) {
                    $body->appendChild($script);
                }
            }
            
            return $dom->saveHTML();
            
        } catch (\Exception $e) {
            $this->logger->warning('Failed to move scripts with DOM parser, falling back to regex', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to regex
            $pattern = '@(?:<script type="text/javascript"|<script)(?![^>]*no-defer)(.*)</script>@msU';
            preg_match_all($pattern, $html, $matches);
            $jsHtml = implode('', $matches[0]);
            $html = preg_replace($pattern, '', $html);
            return $html . $jsHtml;
        }
    }

    /**
     * Validate HTML structure
     * 
     * @param string $html
     * @return bool
     */
    public function isValidHtml(string $html): bool
    {
        if (empty($html)) {
            return false;
        }

        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $result = $dom->loadHTML($html);
            libxml_clear_errors();
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
