<?php

use React\React\Service\HtmlProcessor;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->processor = new HtmlProcessor($this->logger);
});

afterEach(function () {
    Mockery::close();
});

test('removeMagentoInitScripts removes init scripts', function () {
    $html = '<html><body><script type="text/x-magento-init">{"*": {"some": "data"}}</script><div>content</div></body></html>';
    
    $result = $this->processor->removeMagentoInitScripts($html);
    
    expect($result)->not->toContain('text/x-magento-init');
    expect($result)->toContain('content');
});

test('removeMagentoInitScripts returns empty string for empty input', function () {
    expect($this->processor->removeMagentoInitScripts(''))->toBe('');
});

test('removeMagentoInitScripts preserves regular scripts', function () {
    $html = '<html><body><script>console.log("test");</script><div>content</div></body></html>';
    
    $result = $this->processor->removeMagentoInitScripts($html);
    
    expect($result)->toContain('console.log');
});

test('moveScriptsToBottom moves scripts to end', function () {
    $html = '<html><head><script>var a = 1;</script></head><body><div>content</div></body></html>';
    
    $result = $this->processor->moveScriptsToBottom($html);
    
    // Script should be after the content
    $contentPos = strpos($result, 'content');
    $scriptPos = strpos($result, 'var a = 1');
    
    expect($scriptPos)->toBeGreaterThan($contentPos);
});

test('moveScriptsToBottom preserves no-defer scripts', function () {
    $html = '<html><head><script no-defer>var important = 1;</script></head><body><div>content</div></body></html>';
    
    $result = $this->processor->moveScriptsToBottom($html);
    
    // no-defer script should remain in head
    expect($result)->toContain('no-defer');
    expect($result)->toContain('var important = 1');
});

test('moveScriptsToBottom returns empty string for empty input', function () {
    expect($this->processor->moveScriptsToBottom(''))->toBe('');
});

test('isValidHtml returns false for empty string', function () {
    expect($this->processor->isValidHtml(''))->toBeFalse();
});

test('isValidHtml returns true for valid HTML', function () {
    $html = '<html><body><div>content</div></body></html>';
    expect($this->processor->isValidHtml($html))->toBeTrue();
});

test('isValidHtml returns true for malformed but parseable HTML', function () {
    $html = '<div>content<p>test</div>';
    expect($this->processor->isValidHtml($html))->toBeTrue();
});
