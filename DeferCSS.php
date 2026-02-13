<?php

namespace React\React;

use Magento\Framework\App\Config\ScopeConfigInterface as Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class DeferCSS implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    
    public function __construct(
        protected Config $config,
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Check if defer CSS is enabled (config or GET parameter)
        if (!$this->shouldDeferCSS()) {
            return;
        }

        $response = $observer->getEvent()->getData('response');
        if (!$response) {
            return;
        }
        $html = $response->getBody();
        if ($html == '') {
            return;
        }
        
        $html = $this->deferCSS($html);
        $response->setBody($html);
    }

    /**
     * Check if CSS deferral should be applied
     * 
     * @return bool
     */
    private function shouldDeferCSS(): bool
    {
        // Check GET parameter first (use request object)
        $getParam = $this->request->getParam('defer-css');
        if ($getParam === "false") {
            return false;
        }
        if ($getParam === "true") {
            return true;
        }
        
        // Fall back to config (default to true if not set)
        $configValue = $this->config->getValue('react_vue_config/css/defer_css');
        return $configValue === null || $configValue === '' ? true : boolval($configValue);
    }

    private function deferCSS(string $html): string
    {
        // Match link tags with styles-l.css (handles both /> and > closing, with or without whitespace)
        $stylesLPattern = '@<link[^>]*href=["\'][^"\']*styles-l[^"\']*\.css[^"\']*["\'][^>]*\s*/?>@i';
        
        $scriptsToInsert = [];
        
        if (preg_match_all($stylesLPattern, $html, $allMatches, PREG_OFFSET_CAPTURE)) {
            // Process matches in reverse order to maintain correct offsets when replacing
            $matches = array_reverse($allMatches[0]);
            
            foreach ($matches as $match) {
                $stylesLTag = $match[0];
                
                // Check if stylesheet has desktop-only media query (safe to defer on mobile)
                if (!$this->hasDesktopMediaQuery($stylesLTag)) {
                    continue;
                }
                
                $href = $this->extractHref($stylesLTag);
                $media = $this->extractMedia($stylesLTag);
                
                // Build preload link for desktop (in HTML source)
                $preloadLink = '<link rel="preload" as="style" fetchpriority="high" href="' . htmlspecialchars($href, ENT_QUOTES) . '"' . ($media ? ' media="' . htmlspecialchars($media, ENT_QUOTES) . '"' : '') . '>';
                
                // Build the deferred script
                // Desktop: uses document.write() for blocking synchronous load before FCP
                // Mobile: uses setTimeout with createElement for async delayed load
                $deferredScript = '<script no-defer>
  (function () {
    var isDesktop = window.matchMedia("(min-width: 768px)").matches;

    if (isDesktop) {
      // Acts as if the <link> was in original HTML → BLOCKS before FCP
      document.write(
        \'<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '"' . ($media ? ' media="' . htmlspecialchars($media, ENT_QUOTES) . '"' : '') . '>\'
      );
    } else {
      // Mobile: delayed, non-blocking
      setTimeout(function () {
        var l = document.createElement("link");
        l.rel = "stylesheet";
        l.href = "' . htmlspecialchars($href, ENT_QUOTES) . '";
        ' . ($media ? 'l.media = "' . htmlspecialchars($media, ENT_QUOTES) . '";' : '') . '
        document.head.appendChild(l);
      }, 0);
    }
  })();
</script>';
                
                // Remove original link tag
                $html = str_replace($stylesLTag, '', $html);
                
                // Collect preload link and script to insert after mobile styles
                $scriptsToInsert[] = $preloadLink . $deferredScript;
            }
        }
        
        // Insert scripts after mobile styles (styles-m.css, category-styles-m.min.css, etc.)
        // but before desktop styles
        if (!empty($scriptsToInsert) && preg_match('/<head[^>]*>/i', $html, $headMatch, PREG_OFFSET_CAPTURE)) {
            $headPos = $headMatch[0][1] + strlen($headMatch[0][0]);
            $headContent = substr($html, $headPos);
            
            // Find the last mobile stylesheet (styles-m.css or category-styles-m.min.css, etc.)
            // Pattern: link tags with styles-m or category-styles-m or product-styles-m, etc.
            $mobileStylesPattern = '@<link[^>]*href=["\'][^"\']*(?:styles-m|category-styles-m|product-styles-m|home-styles-m)[^"\']*\.css[^"\']*["\'][^>]*\s*/?>@i';
            
            $insertPosition = 0;
            if (preg_match_all($mobileStylesPattern, $headContent, $mobileMatches, PREG_OFFSET_CAPTURE)) {
                // Find the position after the last mobile stylesheet
                $lastMobileMatch = end($mobileMatches[0]);
                $insertPosition = $lastMobileMatch[1] + strlen($lastMobileMatch[0]);
            }
            
            // Insert scripts after mobile styles (or at beginning of head if no mobile styles found)
            $beforeScripts = substr($headContent, 0, $insertPosition);
            $afterScripts = substr($headContent, $insertPosition);
            
            $html = substr($html, 0, $headPos) . $beforeScripts . implode('', $scriptsToInsert) . $afterScripts;
        }
        
        return $html;
    }

    private function hasDesktopMediaQuery(string $linkTag): bool
    {
        if (preg_match('/media=["\']([^"\']+)["\']/i', $linkTag, $matches)) {
            $media = $matches[1];
            // Check if media query contains min-width (desktop-only)
            return preg_match('/min-width\s*:\s*(\d+)/i', $media) === 1;
        }
        return false;
    }

    private function extractMedia(string $linkTag): string
    {
        if (preg_match('/media=["\']([^"\']+)["\']/i', $linkTag, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function extractHref(string $linkTag): string
    {
        if (preg_match('/href=["\']([^"\']+)["\']/i', $linkTag, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
