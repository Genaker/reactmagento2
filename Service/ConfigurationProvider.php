<?php

namespace React\React\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Centralized configuration service for React-Luma module
 */
class ConfigurationProvider
{
    private const CONFIG_PATH_REACT_ENABLE = 'react_vue_config/react/enable';
    private const CONFIG_PATH_VUE_ENABLE = 'react_vue_config/vue/enable';
    private const CONFIG_PATH_JUNK_REMOVE = 'react_vue_config/junk/remove';
    private const CONFIG_PATH_DEFER_JS = 'react_vue_config/junk/defer_js';
    private const CONFIG_PATH_CSS_REMOVE = 'react_vue_config/css/remove';
    private const CONFIG_PATH_CSS_CRITICAL = 'react_vue_config/css/critical';
    private const CONFIG_PATH_CSS_DEFER = 'react_vue_config/css/defer_css';

    /**
     * Page types that are allowed for optimization
     */
    private const ALLOWED_PAGE_TYPES = [
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
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if React is enabled
     * 
     * @return bool
     */
    public function isReactEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_REACT_ENABLE);
    }

    /**
     * Check if Vue is enabled
     * 
     * @return bool
     */
    public function isVueEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_VUE_ENABLE);
    }

    /**
     * Check if JS junk removal is enabled
     * 
     * @return bool
     */
    public function isJunkRemovalEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_JUNK_REMOVE);
    }

    /**
     * Check if JS defer is enabled
     * 
     * @return bool
     */
    public function isDeferJsEnabled(): bool
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_DEFER_JS);
        // Default to true if not set
        return $value === null || $value === '' ? true : (bool) $value;
    }

    /**
     * Check if CSS removal is enabled
     * 
     * @return bool
     */
    public function isCssRemovalEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_CSS_REMOVE);
    }

    /**
     * Check if critical CSS is enabled
     * 
     * @return bool
     */
    public function isCriticalCssEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_CSS_CRITICAL);
    }

    /**
     * Check if CSS defer is enabled
     * 
     * @return bool
     */
    public function isDeferCssEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_CSS_DEFER);
    }

    /**
     * Check if page type is allowed for optimization
     * 
     * @param string $pageType
     * @return bool
     */
    public function isPageTypeAllowed(string $pageType): bool
    {
        return in_array($pageType, self::ALLOWED_PAGE_TYPES, true);
    }

    /**
     * Get all allowed page types
     * 
     * @return array
     */
    public function getAllowedPageTypes(): array
    {
        return self::ALLOWED_PAGE_TYPES;
    }

    /**
     * Get configuration value
     * 
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    public function getConfigValue(string $path, $default = null)
    {
        $value = $this->scopeConfig->getValue($path);
        return $value !== null ? $value : $default;
    }
}
