<?php

namespace React\React;

use Magento\Framework\App\Config\ScopeConfigInterface as Config;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

class RemoveMagentoInitScripts
{
    private $flag = false;

    private $actionFilter = [
        'catalog_category_view',
        'cms_index_index',
        'cms_page_view',
        'catalog_product_view',
        'catalogsearch_result_index',
        'cms_noroute_index',
        'customer_account_login',
        'customer_account_create',
    ];

    /**
     * Modify the final HTML output before sending it to the browser.
     *
     * @param HttpResponse $subject
     * @param string $result
     * @return string
     */
    public function afterGetContent(HttpResponse $subject, $result)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
        $config = $objectManager->get(Config::class);
        
        // Use request object instead of direct $_GET access
        $removeAdobeJSJunk = boolval($config->getValue('react_vue_config/junk/remove'));
        $jsJunkParam = $request->getParam('js-junk');
        if ($jsJunkParam === "false") {
            $removeAdobeJSJunk = false;
        }
        if ($jsJunkParam === "true") {
            $removeAdobeJSJunk = true;
        }

        if ($removeAdobeJSJunk) {
            $actionName = $request->getFullActionName();
            $content = $result;

            if (!in_array($actionName, $this->actionFilter)) {
                return $result;
            }
            if ((!is_string($content) || empty($content) || $this->flag)) {
                return $result;
            }

            $startTime = microtime(true);
            // Remove all `<script type="text/x-magento-init">` blocks
            $result = preg_replace('/<script[^>]+type=["\']text\/x-magento-init["\'][^>]*>.*?<\/script>/is', '', $result);
            //$this->flag = true;
            $endTime = microtime(true);
            $time = $endTime - $startTime;
            header("Server-Timing: x-mag-init;dur=" . number_format($time * 1000, 2), false);
        }

        if ($removeAdobeJSJunk) {
            return $result;
        }

        $html = $result;
        if ($html == '') {
            return $result;
        }
        $conditionalJsPattern = '@(?:<script type="text/javascript"|<script)(.*)</script>@msU';
        preg_match_all($conditionalJsPattern, $html, $_matches);
        $jsHtml = implode('', $_matches[0]);
        $html = preg_replace($conditionalJsPattern, '', $html);
        $html .= $jsHtml;

        $result = $html;

        return $result;
    }
}
