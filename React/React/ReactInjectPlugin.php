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
        $vueEnabled =boolval($config->getValue('react_vue_config/vue/enable'));

        $assets = $this->processMerge($group->getAll(), $group);
        $attributes = $this->getGroupAttributes($group);

        $result = '';
        $template = '';
       
        try {
            /** @var $asset \Magento\Framework\View\Asset\AssetInterface */
            //Changes Start
            foreach ($assets as $key  => $asset) {
                if (strpos($asset->getUrl(),'react')) {
                    unset($assets[$key]);
                    if ($reactEnabled)
                    array_unshift($assets, $asset);
                } else if (strpos($asset->getUrl(),'vue')) {
                    unset($assets[$key]);
                    if ($vueEnabled)
                    array_unshift($assets, $asset);
                }
            }
            //we need execute it one more time to make scripts the same order 
            foreach ($assets as $key  => $asset) {
                if (strpos($asset->getUrl(),'react') || strpos($asset->getUrl(),'vue')){
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
        return $result;
    }

}
