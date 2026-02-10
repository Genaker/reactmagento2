<?php

namespace React\React;

use Magento\Framework\App\Config\ScopeConfigInterface as Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class DeferJS implements ObserverInterface
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
        $removeAdobeJSJunk = boolval($this->config->getValue('react_vue_config/junk/remove'));

        $response = $observer->getEvent()->getData('response');
        if (!$response) {
            return;
        }
        $html = $response->getBody();
        if ($html == '') {
            return;
        }
        
        if ($removeAdobeJSJunk) {
            $response->setBody($html);
            return;
        }
        
        // Check if defer JS is enabled (config or GET parameter)
        $deferJS = $this->shouldDeferJS();
        if ($deferJS) {
            // Move scripts to bottom, but preserve scripts with no-defer attribute
            $conditionalJsPattern = '@(?:<script type="text/javascript"|<script)(?![^>]*no-defer)(.*)</script>@msU';
            preg_match_all($conditionalJsPattern, $html, $_matches);
            $jsHtml = implode('', $_matches[0]);
            $html = preg_replace($conditionalJsPattern, '', $html);
            $html .= $jsHtml;
        }
        
        $response->setBody($html);
    }

    /**
     * Check if JS deferral should be applied
     * 
     * @return bool
     */
    private function shouldDeferJS(): bool
    {
        // Check GET parameter first (use request object)
        $getParam = $this->request->getParam('defer-js');
        if ($getParam === "false") {
            return false;
        }
        if ($getParam === "true") {
            return true;
        }
        
        // Fall back to config (default to true if not set)
        $configValue = $this->config->getValue('react_vue_config/junk/defer_js');
        return $configValue === null || $configValue === '' ? true : boolval($configValue);
    }

}
