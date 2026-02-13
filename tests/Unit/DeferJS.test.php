<?php
/**
 * Pest test for React\React\DeferJS
 * Tests styles-l.css deferral logic with ACTUAL class and mocked dependencies
 * 
 * Run: vendor/bin/pest Unit/DeferJS.test.php
 * 
 * APPROACH: Hybrid bootstrap (bootstrap.php)
 * - Loads Pest's PHPUnit 10+ first
 * - Then loads Magento classes via custom autoloader (skips PHPUnit)
 * - Both coexist without conflicts!
 * - We use the ACTUAL DeferJS class
 * - Dependencies are mocked
 * - NO method copying needed!
 * 
 * Updated to use MockRequest instead of direct $_GET access
 */

/**
 * Uses the ACTUAL DeferJS class from Magento!
 * NO method copying - tests the real implementation directly!
 * 
 * The hybrid bootstrap loads both Pest's PHPUnit and Magento classes successfully
 */
class DeferJSTestHelper
{
    private $actualInstance;
    private $reflection;
    private $request;
    
    public function __construct($dependencies = [])
    {
        // Create mock dependencies if not provided
        $scopeConfig = $dependencies['scopeConfig'] ?? new MockScopeConfig();
        $this->request = $dependencies['request'] ?? new MockRequest();
        
        // Create the ACTUAL DeferJS instance from Magento!
        // The bootstrap autoloader will handle loading interfaces
        $this->actualInstance = new \React\React\DeferJS($scopeConfig, $this->request);
        $this->reflection = new \ReflectionClass($this->actualInstance);
    }
    
    /**
     * Get the actual instance
     */
    public function getInstance()
    {
        return $this->actualInstance;
    }
    
    /**
     * Get the mock request object
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Call a private/protected method using Reflection API
     */
    public function callMethod($methodName, ...$args)
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invoke($this->actualInstance, ...$args);
    }
    
    /**
     * Convenience methods for testing private methods
     */
    public function shouldDeferJS(): bool
    {
        return $this->callMethod('shouldDeferJS');
    }
}

// Mock classes that implement actual Magento interfaces
// MockScopeConfig and MockRequest are loaded from Unit/Mocks.php

beforeEach(function () {
    $this->helper = new DeferJSTestHelper();
});

test('shouldDeferJS returns false when request parameter defer-js is false', function () {
    $mockRequest = new MockRequest(['defer-js' => 'false']);
    $helper = new DeferJSTestHelper(['request' => $mockRequest]);
    $result = $helper->callMethod('shouldDeferJS');
    
    expect($result)->toBeFalse();
});

test('shouldDeferJS returns true when request parameter defer-js is true', function () {
    $mockRequest = new MockRequest(['defer-js' => 'true']);
    $helper = new DeferJSTestHelper(['request' => $mockRequest]);
    $result = $helper->callMethod('shouldDeferJS');
    
    expect($result)->toBeTrue();
});

test('shouldDeferJS uses config value when request parameter not set', function () {
    // Test with config disabled
    $helperDisabled = new DeferJSTestHelper([
        'scopeConfig' => new MockScopeConfig(['react_vue_config/junk/defer_js' => '0']),
        'request' => new MockRequest()
    ]);
    expect($helperDisabled->callMethod('shouldDeferJS'))->toBeFalse();
    
    // Test with config enabled
    $helperEnabled = new DeferJSTestHelper([
        'scopeConfig' => new MockScopeConfig(['react_vue_config/junk/defer_js' => '1']),
        'request' => new MockRequest()
    ]);
    expect($helperEnabled->callMethod('shouldDeferJS'))->toBeTrue();
});

test('shouldDeferJS defaults to true when config is not set', function () {
    // Test with config not set (null)
    $helperDefault = new DeferJSTestHelper([
        'scopeConfig' => new MockScopeConfig([]),
        'request' => new MockRequest()
    ]);
    expect($helperDefault->callMethod('shouldDeferJS'))->toBeTrue();
});


