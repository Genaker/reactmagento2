<?php

namespace React\React;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use React\React\Service\ConfigurationProvider;
use React\React\Service\HtmlProcessor;
use React\React\Service\RequestValidator;
use React\React\Service\ResponseHeaderService;
use Psr\Log\LoggerInterface;

/**
 * Plugin to remove Magento init scripts from HTML output
 */
class RemoveMagentoInitScripts
{
    /**
     * @param RequestInterface $request
     * @param ConfigurationProvider $configProvider
     * @param HtmlProcessor $htmlProcessor
     * @param RequestValidator $requestValidator
     * @param ResponseHeaderService $headerService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private RequestInterface $request,
        private ConfigurationProvider $configProvider,
        private HtmlProcessor $htmlProcessor,
        private RequestValidator $requestValidator,
        private ResponseHeaderService $headerService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Modify the final HTML output before sending it to the browser.
     *
     * @param HttpResponse $subject
     * @param string $result
     * @return string
     */
    public function afterGetContent(HttpResponse $subject, $result)
    {
        // Get configuration
        $removeAdobeJSJunk = $this->configProvider->isJunkRemovalEnabled();
        
        // Check for parameter override (validated)
        $paramOverride = $this->requestValidator->getValidatedBoolParam($this->request, 'js-junk');
        if ($paramOverride !== null && $this->requestValidator->isParameterOverrideAllowed($this->request)) {
            $removeAdobeJSJunk = $paramOverride;
        }

        if ($removeAdobeJSJunk) {
            $actionName = $this->request->getFullActionName();
            $content = $result;

            // Check if page type is allowed for optimization
            if (!$this->configProvider->isPageTypeAllowed($actionName)) {
                return $result;
            }
            
            if (!is_string($content) || empty($content)) {
                return $result;
            }

            $startTime = microtime(true);
            
            // Use HtmlProcessor service to remove init scripts
            $result = $this->htmlProcessor->removeMagentoInitScripts($result);
            
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            // Set performance timing header
            $this->headerService->setServerTiming($subject, 'x-mag-init', $duration);
        }

        if (!$removeAdobeJSJunk) {
            // Move scripts to bottom if junk removal is disabled
            $html = $result;
            if (empty($html)) {
                return $result;
            }
            
            $result = $this->htmlProcessor->moveScriptsToBottom($html);
        }

        return $result;
    }
}
