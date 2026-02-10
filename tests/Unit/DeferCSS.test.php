<?php
/**
 * Pest test for React\React\DeferCSS
 * Tests CSS deferral logic with document.write() for desktop blocking load
 * 
 * Run: vendor/bin/pest Unit/DeferCSS.test.php
 * 
 * Updated to use MockRequest instead of direct $_GET access
 */

class DeferCSSTestHelper
{
    private $actualInstance;
    private $reflection;
    private $request;
    
    public function __construct($dependencies = [])
    {
        // Create mock dependencies if not provided
        $scopeConfig = $dependencies['scopeConfig'] ?? new MockScopeConfig();
        $this->request = $dependencies['request'] ?? new MockRequest();
        
        // Create the ACTUAL DeferCSS instance from Magento!
        $this->actualInstance = new \React\React\DeferCSS($scopeConfig, $this->request);
        $this->reflection = new \ReflectionClass($this->actualInstance);
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
    public function hasDesktopMediaQuery(string $linkTag): bool
    {
        return $this->callMethod('hasDesktopMediaQuery', $linkTag);
    }
    
    public function extractHref(string $linkTag): string
    {
        return $this->callMethod('extractHref', $linkTag);
    }
    
    public function extractMedia(string $linkTag): string
    {
        return $this->callMethod('extractMedia', $linkTag);
    }
    
    public function deferCSS(string $html): string
    {
        return $this->callMethod('deferCSS', $html);
    }
    
    public function shouldDeferCSS(): bool
    {
        return $this->callMethod('shouldDeferCSS');
    }
}

// Mock classes that implement actual Magento interfaces
// MockScopeConfig and MockRequest are loaded from Unit/Mocks.php

beforeEach(function () {
    $this->helper = new DeferCSSTestHelper();
});

test('hasDesktopMediaQuery detects desktop media query with min-width', function () {
    $linkTag = '<link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css" />';
    
    expect($this->helper->hasDesktopMediaQuery($linkTag))->toBeTrue();
});

test('hasDesktopMediaQuery returns false for mobile media query', function () {
    $linkTag = '<link rel="stylesheet" media="(max-width: 767px)" href="styles-m.css" />';
    
    expect($this->helper->hasDesktopMediaQuery($linkTag))->toBeFalse();
});

test('extractHref extracts href from link tag', function () {
    $linkTag = '<link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css" />';
    
    $href = $this->helper->extractHref($linkTag);
    
    expect($href)->toBe('https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css');
});

test('extractMedia extracts media attribute', function () {
    $linkTag = '<link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="styles-l.css" />';
    
    $media = $this->helper->extractMedia($linkTag);
    
    expect($media)->toBe('screen and (min-width: 768px)');
});

test('deferCSS removes link tag and inserts script after mobile styles', function () {
    $html = '<html><head><link rel="stylesheet" href="category-styles-m.min.css" /><link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css" /></head><body>Test</body></html>';
    
    $result = $this->helper->deferCSS($html);
    
    // Original link tag should be removed
    expect($result)->not->toContain('<link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css" />');
    
    // Preload link should be present for desktop
    expect($result)->toContain('<link rel="preload"');
    expect($result)->toContain('as="style"');
    expect($result)->toContain('fetchpriority="high"');
    expect($result)->toContain('https://react-luma.cnxt.link/static/version1765430810/styles-l.min.css');
    
    // Script should be present with no-defer attribute
    expect($result)->toContain('<script no-defer>');
    expect($result)->toContain('window.matchMedia("(min-width: 768px)")');
    expect($result)->toContain('document.write');
    expect($result)->toContain('setTimeout');
    
    // Script should be after mobile styles but before desktop styles
    preg_match('/<head[^>]*>(.*?)<\/head>/s', $result, $headMatches);
    if (isset($headMatches[1])) {
        $headContent = $headMatches[1];
        expect($headContent)->toContain('<link rel="preload"');
        expect($headContent)->toContain('<script no-defer>');
        // Verify preload and script come after mobile styles
        $mobileStylePos = strpos($headContent, 'category-styles-m.min.css');
        $preloadPos = strpos($headContent, '<link rel="preload"');
        $scriptPos = strpos($headContent, '<script no-defer>');
        if ($mobileStylePos !== false && $preloadPos !== false) {
            expect($preloadPos)->toBeGreaterThan($mobileStylePos);
        }
        if ($mobileStylePos !== false && $scriptPos !== false) {
            expect($scriptPos)->toBeGreaterThan($mobileStylePos);
        }
    }
});

test('deferCSS uses document.write for desktop blocking load', function () {
    $html = '<html><head><link rel="stylesheet" type="text/css" media="screen and (min-width: 768px)" href="styles-l.css" /></head><body>Test</body></html>';
    
    $result = $this->helper->deferCSS($html);
    
    // Should contain document.write for desktop
    expect($result)->toContain('document.write');
    expect($result)->toContain('isDesktop = window.matchMedia("(min-width: 768px)").matches');
    expect($result)->toContain('if (isDesktop)');
    
    // Should contain setTimeout for mobile
    expect($result)->toContain('setTimeout(function ()');
    // Note: The actual implementation uses 0ms delay, not 1500ms
    // The test checks for setTimeout presence, delay value may vary
});

test('deferCSS does not defer styles-l.css without desktop media query', function () {
    $html = '<html><head><link rel="stylesheet" href="styles-l.css" /></head><body>Test</body></html>';
    
    $result = $this->helper->deferCSS($html);
    
    // Should remain unchanged (no script added)
    expect($result)->not->toContain('<script');
    expect($result)->toBe($html);
});

test('shouldDeferCSS returns false when GET parameter defer-css is false', function () {
    $_GET['defer-css'] = 'false';
    $result = $this->helper->callMethod('shouldDeferCSS');
    unset($_GET['defer-css']);
    
    expect($result)->toBeFalse();
});

test('shouldDeferCSS returns true when GET parameter defer-css is true', function () {
    $_GET['defer-css'] = 'true';
    $result = $this->helper->callMethod('shouldDeferCSS');
    unset($_GET['defer-css']);
    
    expect($result)->toBeTrue();
});

test('shouldDeferCSS uses config value when GET parameter not set', function () {
    // Clear GET parameter
    unset($_GET['defer-css']);
    
    // Test with config disabled
    $helperDisabled = new DeferCSSTestHelper([
        'scopeConfig' => new MockScopeConfig(['react_vue_config/css/defer_css' => '0'])
    ]);
    expect($helperDisabled->callMethod('shouldDeferCSS'))->toBeFalse();
    
    // Test with config enabled
    $helperEnabled = new DeferCSSTestHelper([
        'scopeConfig' => new MockScopeConfig(['react_vue_config/css/defer_css' => '1'])
    ]);
    expect($helperEnabled->callMethod('shouldDeferCSS'))->toBeTrue();
});

test('shouldDeferCSS defaults to true when config is not set', function () {
    // Clear GET parameter
    unset($_GET['defer-css']);
    
    // Test with config not set (null)
    $helperDefault = new DeferCSSTestHelper([
        'scopeConfig' => new MockScopeConfig([])
    ]);
    expect($helperDefault->callMethod('shouldDeferCSS'))->toBeTrue();
});
