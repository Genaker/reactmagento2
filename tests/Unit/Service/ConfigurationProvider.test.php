<?php

use React\React\Service\ConfigurationProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

beforeEach(function () {
    $this->scopeConfig = Mockery::mock(ScopeConfigInterface::class);
    $this->configProvider = new ConfigurationProvider($this->scopeConfig);
});

afterEach(function () {
    Mockery::close();
});

test('isReactEnabled returns correct value', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/react/enable')
        ->andReturn('1');
    
    expect($this->configProvider->isReactEnabled())->toBeTrue();
});

test('isVueEnabled returns correct value', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/vue/enable')
        ->andReturn('0');
    
    expect($this->configProvider->isVueEnabled())->toBeFalse();
});

test('isJunkRemovalEnabled returns correct value', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/junk/remove')
        ->andReturn('1');
    
    expect($this->configProvider->isJunkRemovalEnabled())->toBeTrue();
});

test('isDeferJsEnabled returns true when config is not set', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/junk/defer_js')
        ->andReturn(null);
    
    expect($this->configProvider->isDeferJsEnabled())->toBeTrue();
});

test('isDeferJsEnabled returns config value when set', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/junk/defer_js')
        ->andReturn('0');
    
    expect($this->configProvider->isDeferJsEnabled())->toBeFalse();
});

test('isPageTypeAllowed returns true for allowed page type', function () {
    expect($this->configProvider->isPageTypeAllowed('catalog_category_view'))->toBeTrue();
    expect($this->configProvider->isPageTypeAllowed('cms_index_index'))->toBeTrue();
    expect($this->configProvider->isPageTypeAllowed('catalog_product_view'))->toBeTrue();
});

test('isPageTypeAllowed returns false for non-allowed page type', function () {
    expect($this->configProvider->isPageTypeAllowed('some_random_action'))->toBeFalse();
    expect($this->configProvider->isPageTypeAllowed('admin_action'))->toBeFalse();
});

test('getAllowedPageTypes returns array', function () {
    $types = $this->configProvider->getAllowedPageTypes();
    expect($types)->toBeArray();
    expect(count($types))->toBeGreaterThan(0);
});

test('isCriticalCssEnabled returns correct value', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('react_vue_config/css/critical')
        ->andReturn('1');
    
    expect($this->configProvider->isCriticalCssEnabled())->toBeTrue();
});

test('getConfigValue returns default when value is null', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('some/path')
        ->andReturn(null);
    
    expect($this->configProvider->getConfigValue('some/path', 'default'))->toBe('default');
});

test('getConfigValue returns actual value when set', function () {
    $this->scopeConfig->shouldReceive('getValue')
        ->with('some/path')
        ->andReturn('actual_value');
    
    expect($this->configProvider->getConfigValue('some/path', 'default'))->toBe('actual_value');
});
