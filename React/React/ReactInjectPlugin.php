<?php

namespace React\React;

use Magento\Framework\View\Page\Config\Renderer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Metadata\MsApplicationTileImage;

/**
 * Page config Renderer model Plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReactInjectPlugin extends Renderer 
{
    /**
     * @param Config $pageConfig
     * @param \Magento\Framework\View\Asset\MergeService $assetMergeService
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param MsApplicationTileImage|null $msApplicationTileImage
     */
    public function __construct(
        Config $pageConfig,
        \Magento\Framework\View\Asset\MergeService $assetMergeService,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        MsApplicationTileImage $msApplicationTileImage = null
    ) {
        parent::__construct($pageConfig, $assetMergeService, $urlBuilder, $escaper, $string, $logger, $msApplicationTileImage);
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string
     */
    protected function renderAssetHtml(\Magento\Framework\View\Asset\PropertyGroup $group)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $reactEnabled = boolval($config->getValue('react_vue_config/react/enable'));
        $vueEnabled = boolval($config->getValue('react_vue_config/vue/enable'));
        /* remove default magento Junky JS */
        $removeAdobeJSJunk = boolval($config->getValue('react_vue_config/junk/remove'));
        $state = $objectManager->get('Magento\Framework\App\State');
        $store = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $area = $state->getAreaCode();
        $pageFilter = ['checkout', 'customer'];
        $actionFilter = ['catalog_category_view', 
                         'cms_index_index', 
                         'cms_page_view', 
                         'catalog_product_view', 
                         'catalogsearch_result_index'];

        $request = $objectManager->get(\Magento\Framework\App\Request\Http::class);
        $actionName = $request->getFullActionName();
        @header("Action-Name: $actionName");
        $requestURL = $_SERVER['REQUEST_URI'];
        $removeProtection = boolval(boolval(strpos($requestURL,'checkout')) || boolval(strpos($requestURL,'customer')) || $area === 'adminhtml');
        @header("React-Protection: $removeProtection");
        $block = $objectManager->get(\Magento\Framework\View\Element\Template::class);
        $assets = $this->processMerge($group->getAll(), $group);
        $attributes = $this->getGroupAttributes($group);

        $result = '';
        $template = '';
       $assetOptimized = false ;

        try {
            /** @var $asset \Magento\Framework\View\Asset\AssetInterface */
            //Changes Start
          $baseURL = $store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
          foreach ($assets as $key => $asset) {
                 if (in_array($actionName, $actionFilter) && strpos($asset->getUrl(),'styles-m.css')) {
                    // http://**/static/version1642788857/frontend/Magento/luma/en_US/css/styles-m.css
                    $optimisedCSSFileUrl = $baseURL . 'styles-m.css';
                    $optimisedCSSFilePath = BP . '/pub/static/styles-m.css';
                    if(file_exists($optimisedCSSFilePath)) {
                        //echo $optimisedCSSFileUrl;
                        $assetOptimized = $optimisedCSSFileUrl;
                        unset($assets[$key]);
                    } else {
                        @header("Optimised-CSS: false");
                    }
                }
          }

          foreach ($assets as $key  => $asset) {
                if (strpos($asset->getUrl(),'js/react')) {
                    unset($assets[$key]);
                    if ($reactEnabled)
                    array_unshift($assets, $asset);
                } else if (strpos($asset->getUrl(),'vue')) {
                    unset($assets[$key]);
                    if ($vueEnabled)
                    array_unshift($assets, $asset);
                } else if (strpos($asset->getUrl(),'require')) {
                    unset($assets[$key]);
                    // junk True ; protection False
                        //echo "require " . (string) $removeProtection;
                    //var_dump($removeAdobeJSJunk); die();
                    if (!$removeAdobeJSJunk || !in_array($actionName, $actionFilter))
                    array_unshift($assets, $asset);
                }
            }
            //we need execute it one more time to make scripts the same order 
           foreach ($assets as $key  => $asset) {
                if (strpos($asset->getUrl(),'require')){
                unset($assets[$key]);
                array_unshift($assets, $asset);
                }
            }

            foreach ($assets as $key  => $asset) {
//              var_dump($asset->getUrl());
                if (strpos($asset->getUrl(),'js/react') || strpos($asset->getUrl(),'vue')){
                unset($assets[$key]);
                array_unshift($assets, $asset);
                }
            }


            //Changes Ends
            
            foreach ($assets as $asset) {
                $template = $this->getAssetTemplate(
                    $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                    $this->addDefaultAttributes($this->getAssetContentType($asset), $attributes)
                );
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        if($assetOptimized !== false) {
                $result = '<link  rel="stylesheet" type="text/css"  media="all" href="'.$assetOptimized.'" />' . "\n" . $result;
        }
        return $result;
    }

}
