# React-Luma Architecture Documentation

## Overview

React-Luma is a Magento 2 performance optimization module that provides a modern, secure, and maintainable architecture for frontend optimization. This document describes the architectural patterns, design principles, and key components of the module.

## Architecture Principles

### 1. Separation of Concerns
Each class has a single, well-defined responsibility:
- **Services**: Business logic and reusable functionality
- **Plugins**: Magento integration points
- **Observers**: Event-based processing
- **Blocks**: UI component rendering

### 2. Dependency Injection
All dependencies are injected through constructors, following Magento 2 best practices:
- No direct use of `ObjectManager`
- Testable code with mockable dependencies
- Clear dependency graphs

### 3. Security First
- Validated and sanitized input parameters
- Protection against cache poisoning
- Safe header manipulation
- Proper error handling and logging

### 4. Proper HTML Processing
- Uses DOM parser instead of regex for HTML manipulation
- Handles malformed HTML gracefully
- Fallback mechanisms for edge cases

## Directory Structure

```
React/React/
├── Service/                          # Business logic services
│   ├── ConfigurationProvider.php    # Centralized configuration
│   ├── HtmlProcessor.php            # HTML parsing and manipulation
│   ├── RequestValidator.php         # Input validation and security
│   └── ResponseHeaderService.php    # Safe header management
├── Block/                           # UI components
├── Controller/                      # Admin controllers
├── Plugin/                          # Magento plugins
├── Observer/                        # Event observers
├── DeferJS.php                     # JS deferral observer
├── DeferCSS.php                    # CSS deferral observer
├── RemoveMagentoInitScripts.php    # Init script removal plugin
├── ReactInjectPlugin.php           # Asset optimization plugin
└── Template.php                    # Template helper class
```

## Core Services

### ConfigurationProvider

**Purpose**: Centralized access to module configuration

**Responsibilities**:
- Read configuration values from Magento config
- Provide typed configuration getters
- Manage allowed page types for optimization
- Default value handling

**Usage**:
```php
$configProvider->isReactEnabled();
$configProvider->isDeferJsEnabled();
$configProvider->isPageTypeAllowed($pageType);
```

### HtmlProcessor

**Purpose**: Parse and manipulate HTML content safely

**Responsibilities**:
- Remove Magento init scripts using DOM parser
- Move scripts to page bottom
- Validate HTML structure
- Fallback to regex when DOM parsing fails

**Key Features**:
- UTF-8 encoding support
- Handles malformed HTML gracefully
- Preserves `no-defer` attributes
- Comprehensive error logging

**Usage**:
```php
$html = $htmlProcessor->removeMagentoInitScripts($html);
$html = $htmlProcessor->moveScriptsToBottom($html);
$isValid = $htmlProcessor->isValidHtml($html);
```

### RequestValidator

**Purpose**: Validate and sanitize request parameters

**Responsibilities**:
- Validate boolean parameters
- Prevent injection attacks
- Check parameter override permissions
- CSRF protection (future enhancement)

**Security Features**:
- Whitelist-based validation
- Type coercion prevention
- Permission checks
- Logging of override usage

**Usage**:
```php
$value = $requestValidator->getValidatedBoolParam($request, 'defer-js', true);
$allowed = $requestValidator->isParameterOverrideAllowed($request);
```

### ResponseHeaderService

**Purpose**: Manage HTTP response headers safely

**Responsibilities**:
- Set headers without causing errors
- Handle headers-already-sent scenarios
- Server-Timing header management
- Comprehensive error logging

**Usage**:
```php
$headerService->setHeader($response, 'X-Custom', 'value');
$headerService->setServerTiming($response, 'processing', 123.45);
$headerService->setCustomHeaders($response, ['X-Foo' => 'bar']);
```

## Plugin Architecture

### RemoveMagentoInitScripts

**Type**: Plugin on `HttpResponse::getContent()`

**Purpose**: Remove Magento's x-magento-init scripts for performance

**Flow**:
1. Get configuration and check for parameter overrides
2. Validate page type is allowed
3. Use HtmlProcessor to remove init scripts via DOM parser
4. Track performance with Server-Timing headers
5. Fallback to moving scripts if junk removal is disabled

**Key Improvements**:
- Uses dependency injection (no ObjectManager)
- Validated parameter overrides
- Safe header manipulation
- DOM-based HTML processing

### ReactInjectPlugin

**Type**: Plugin on `PageConfig\Renderer::renderAssetHtml()`

**Purpose**: Optimize CSS and JS asset loading

**Responsibilities**:
- CSS optimization (remove unused, inject optimized files)
- JS optimization (defer loading, remove junk)
- Critical CSS handling
- Store-specific asset loading

**Note**: This class needs further refactoring (see Phase 2 plan)

## Observer Architecture

### DeferJS

**Event**: `controller_action_layout_render_before_*`

**Purpose**: Move JavaScript to page bottom for better performance

**Flow**:
1. Skip if junk removal is already enabled
2. Check configuration and validated parameters
3. Use HtmlProcessor to move scripts
4. Preserve scripts with `no-defer` attribute

**Key Improvements**:
- Uses service classes
- Validated parameter overrides
- DOM-based processing with regex fallback

### DeferCSS

**Event**: `controller_action_layout_render_before_*`

**Purpose**: Defer non-critical CSS loading

**Responsibilities**:
- Identify critical vs non-critical CSS
- Implement responsive loading strategies
- Optimize for mobile and desktop

## Configuration Management

### Configuration Paths

```
react_vue_config/
├── react/
│   └── enable                 # Enable React framework
├── vue/
│   └── enable                 # Enable Vue.js framework
├── junk/
│   ├── remove                 # Remove Magento init scripts
│   └── defer_js               # Defer JavaScript loading
└── css/
    ├── remove                 # Remove default Magento CSS
    ├── critical               # Enable critical CSS
    └── defer_css              # Defer CSS loading
```

### Allowed Page Types

The following page types are optimized by default:
- `catalog_category_view` - Category pages
- `cms_index_index` - Homepage
- `cms_page_view` - CMS pages
- `catalog_product_view` - Product pages
- `catalogsearch_result_index` - Search results
- `cms_noroute_index` - 404 pages
- `customer_account_login` - Login page
- `customer_account_create` - Registration page

These can be configured in `ConfigurationProvider::ALLOWED_PAGE_TYPES`.

## Security Considerations

### Input Validation

All URL parameters that override configuration are:
1. Validated using whitelist approach
2. Type-checked to prevent injection
3. Permission-checked before application
4. Logged for security audit

### Cache Poisoning Prevention

The module prevents cache poisoning by:
1. Validating parameter values strictly
2. Requiring proper permissions for overrides
3. Not caching parameter-overridden responses (Magento default behavior)

### Header Injection Prevention

Headers are set safely:
1. Check if headers already sent
2. Use Response object methods when available
3. Catch and log exceptions
4. Never use user input directly in headers

## Error Handling

### Logging Strategy

- **Debug**: Non-critical issues (headers already sent)
- **Warning**: Recoverable errors (DOM parsing fails, falls back to regex)
- **Error**: Serious issues that affect functionality
- **Critical**: Fatal errors that prevent operation

### Graceful Degradation

The module degrades gracefully:
1. DOM parsing fails → Falls back to regex
2. Header setting fails → Logs and continues
3. HTML validation fails → Returns original content
4. Service unavailable → Disables optimization

## Testing Strategy

### Unit Tests

Each service class should have comprehensive unit tests:
- `ConfigurationProvider`: Test configuration reading
- `HtmlProcessor`: Test DOM parsing and fallbacks
- `RequestValidator`: Test validation logic
- `ResponseHeaderService`: Test header manipulation

### Integration Tests

Test plugin and observer integration:
- Test parameter override workflows
- Test HTML processing pipeline
- Test configuration-driven behavior
- Test error handling paths

## Performance Considerations

### Optimization Techniques

1. **Lazy Loading**: Only process when needed
2. **Caching**: Configuration values cached by Magento
3. **Early Returns**: Skip processing when not needed
4. **DOM Reuse**: Single DOM parse per request when possible

### Performance Monitoring

Use Server-Timing headers to track:
- HTML processing time
- Script removal time
- Asset optimization time
- Total optimization overhead

## Future Enhancements

### Phase 2: Refactor ReactInjectPlugin
- Split into AssetOptimizer, CSSOptimizer, JSOptimizer services
- Extract page type detection logic
- Improve asset caching strategy

### Phase 3: Advanced Security
- Implement CSRF token validation
- Add rate limiting for parameter overrides
- Admin-only parameter override mode
- Security audit logging

### Phase 4: Performance Improvements
- Implement asset caching layer
- Add HTTP/2 Server Push support
- Optimize DOM parsing performance
- Add progressive enhancement support

## Extension Points

### Adding New Page Types

Edit `ConfigurationProvider::ALLOWED_PAGE_TYPES`:

```php
private const ALLOWED_PAGE_TYPES = [
    // ... existing types ...
    'your_module_controller_action',
];
```

### Custom HTML Processing

Extend `HtmlProcessor` and override in `di.xml`:

```xml
<preference for="React\React\Service\HtmlProcessor" 
            type="YourModule\CustomHtmlProcessor" />
```

### Custom Validation Logic

Extend `RequestValidator` and override in `di.xml`:

```xml
<preference for="React\React\Service\RequestValidator" 
            type="YourModule\CustomValidator" />
```

## Troubleshooting

### DOM Parser Issues

If DOM parser fails consistently:
1. Check HTML validity with `isValidHtml()`
2. Review error logs for specific issues
3. Module falls back to regex automatically
4. Consider adding HTML tidy preprocessing

### Performance Degradation

If optimization slows down pages:
1. Check Server-Timing headers
2. Disable specific optimizations via config
3. Use parameter overrides for testing
4. Profile DOM parsing vs regex performance

### Configuration Not Applied

If config changes don't apply:
1. Clear Magento cache
2. Check configuration scope (store vs default)
3. Verify configuration path matches constants
4. Check logs for validation errors

## Contributing

When contributing to this module:
1. Follow architectural patterns documented here
2. Add unit tests for new services
3. Use dependency injection (no ObjectManager)
4. Validate all user input
5. Log appropriately (not too verbose, not too sparse)
6. Update this documentation

## References

- [Magento 2 Dependency Injection](https://developer.adobe.com/commerce/php/development/components/dependency-injection/)
- [Magento 2 Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/)
- [Magento 2 Events and Observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/)
- [PHP DOMDocument](https://www.php.net/manual/en/class.domdocument.php)
