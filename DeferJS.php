<?php

namespace React\React;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use React\React\Service\ConfigurationProvider;
use React\React\Service\HtmlProcessor;
use React\React\Service\RequestValidator;

/**
 * Observer to defer JavaScript loading
 */
class DeferJS implements ObserverInterface
{
    /**
     * @param RequestInterface $request
     * @param ConfigurationProvider $configProvider
     * @param HtmlProcessor $htmlProcessor
     * @param RequestValidator $requestValidator
     */
    public function __construct(
        private RequestInterface $request,
        private ConfigurationProvider $configProvider,
        private HtmlProcessor $htmlProcessor,
        private RequestValidator $requestValidator
    ) {
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getData('response');
        if (!$response) {
            return;
        }
        
        $html = $response->getBody();
        if (empty($html)) {
            return;
        }
        
        // If junk removal is enabled, skip defer (handled by RemoveMagentoInitScripts)
        if ($this->configProvider->isJunkRemovalEnabled()) {
            return;
        }
        
        // Check if defer JS is enabled (config or validated parameter)
        if ($this->shouldDeferJS()) {
            $html = $this->htmlProcessor->moveScriptsToBottom($html);
            $response->setBody($html);
        }
    }

    /**
     * Determine if JS should be deferred
     * 
     * @return bool
     */
    private function shouldDeferJS(): bool
    {
        // Check for validated parameter override
        $paramOverride = $this->requestValidator->getValidatedBoolParam($this->request, 'defer-js');
        
        if ($paramOverride !== null && $this->requestValidator->isParameterOverrideAllowed($this->request)) {
            return $paramOverride;
        }
        
        // Fall back to configuration
        return $this->configProvider->isDeferJsEnabled();
    }
}
