# Developer Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Architecture Overview](#architecture-overview)
3. [Extending the Module](#extending-the-module)
4. [Creating Custom Services](#creating-custom-services)
5. [Adding New Page Types](#adding-new-page-types)
6. [Custom HTML Processing](#custom-html-processing)
7. [Plugin Development](#plugin-development)
8. [Best Practices](#best-practices)
9. [Testing](#testing)
10. [Debugging](#debugging)

## Getting Started

### Development Environment Setup

1. Clone the repository or install via Composer
2. Install dependencies:
   ```bash
   composer install --dev
   npm install
   ```
3. Set up your IDE with Magento 2 support
4. Configure xdebug for debugging

### Project Structure

```
React/React/
├── Service/              # Business logic services
├── Block/               # UI components
├── Controller/          # Controllers
├── Plugin/              # Magento plugins
├── Observer/            # Event observers (DeferJS, DeferCSS)
├── etc/                 # Configuration files
├── view/                # Templates and layouts
├── tests/               # Unit and integration tests
└── pub/static/          # Pre-optimized static assets
```

## Architecture Overview

React-Luma follows a **service-oriented architecture** with clear separation of concerns:

### Core Architectural Patterns

1. **Service Layer**: Business logic encapsulated in service classes
2. **Dependency Injection**: All dependencies injected via constructor
3. **Plugin Pattern**: Non-invasive integration with Magento core
4. **Observer Pattern**: Event-based processing
5. **Strategy Pattern**: Conditional logic based on configuration

### Key Components

```
Request → Plugin/Observer → Service Layer → Response
                              ↓
                         Configuration
```

For detailed architecture information, see [ARCHITECTURE.md](ARCHITECTURE.md).

## Extending the Module

### 1. Adding New Services

Create a new service in `Service/` directory:

```php
<?php
namespace React\React\Service;

use Psr\Log\LoggerInterface;

class MyCustomService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function doSomething(): bool
    {
        // Your logic here
        return true;
    }
}
```

Register it in `etc/di.xml` if needed:

```xml
<type name="React\React\MyClass">
    <arguments>
        <argument name="myService" xsi:type="object">React\React\Service\MyCustomService</argument>
    </arguments>
</type>
```

### 2. Creating Custom Validators

Extend `RequestValidator` for custom validation logic:

```php
<?php
namespace YourModule\Service;

use React\React\Service\RequestValidator as BaseValidator;

class CustomRequestValidator extends BaseValidator
{
    public function validateCustomParam(string $value): bool
    {
        // Custom validation logic
        return in_array($value, ['allowed1', 'allowed2']);
    }
}
```

Override in `di.xml`:

```xml
<preference for="React\React\Service\RequestValidator" 
            type="YourModule\Service\CustomRequestValidator" />
```

### 3. Custom HTML Processing

Extend `HtmlProcessor` for custom HTML manipulation:

```php
<?php
namespace YourModule\Service;

use React\React\Service\HtmlProcessor as BaseProcessor;

class CustomHtmlProcessor extends BaseProcessor
{
    public function customProcessing(string $html): string
    {
        // Your custom processing
        return parent::removeMagentoInitScripts($html);
    }
}
```

## Adding New Page Types

To optimize additional page types:

### Method 1: Configuration (Recommended)

Edit `Service/ConfigurationProvider.php`:

```php
private const ALLOWED_PAGE_TYPES = [
    'catalog_category_view',
    'cms_index_index',
    // ... existing types ...
    
    // Add your new types
    'your_module_controller_action',
    'another_module_action',
];
```

### Method 2: Plugin

Create a plugin to modify allowed page types dynamically:

```php
<?php
namespace YourModule\Plugin;

use React\React\Service\ConfigurationProvider;

class ConfigurationProviderPlugin
{
    public function afterIsPageTypeAllowed(
        ConfigurationProvider $subject,
        bool $result,
        string $pageType
    ): bool {
        // Add your custom logic
        if ($pageType === 'your_custom_action') {
            return true;
        }
        return $result;
    }
}
```

Register in `di.xml`:

```xml
<type name="React\React\Service\ConfigurationProvider">
    <plugin name="your_module_config_provider" 
            type="YourModule\Plugin\ConfigurationProviderPlugin" />
</type>
```

## Custom HTML Processing

### Example: Remove Custom Scripts

```php
<?php
namespace YourModule\Service;

use React\React\Service\HtmlProcessor;
use Psr\Log\LoggerInterface;

class CustomScriptRemover
{
    public function __construct(
        private HtmlProcessor $htmlProcessor,
        private LoggerInterface $logger
    ) {
    }

    public function removeCustomScripts(string $html): string
    {
        try {
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            $scripts = $xpath->query('//script[@data-remove="true"]');
            
            foreach ($scripts as $script) {
                $script->parentNode->removeChild($script);
            }
            
            return $dom->saveHTML();
        } catch (\Exception $e) {
            $this->logger->warning('Failed to remove custom scripts', [
                'error' => $e->getMessage()
            ]);
            return $html;
        }
    }
}
```

## Plugin Development

### Creating a New Plugin

Example: Add custom header to responses

```php
<?php
namespace YourModule\Plugin;

use Magento\Framework\App\Response\HttpInterface;
use React\React\Service\ResponseHeaderService;

class ResponsePlugin
{
    public function __construct(
        private ResponseHeaderService $headerService
    ) {
    }

    public function afterSendResponse(HttpInterface $subject, $result)
    {
        $this->headerService->setHeader(
            $subject, 
            'X-Custom-Header', 
            'custom-value'
        );
        return $result;
    }
}
```

Register in `di.xml`:

```xml
<type name="Magento\Framework\App\Response\HttpInterface">
    <plugin name="your_module_response_plugin" 
            type="YourModule\Plugin\ResponsePlugin" 
            sortOrder="100" />
</type>
```

### Plugin Best Practices

1. **Use specific plugin types**: before, after, around
2. **Sort order matters**: Use `sortOrder` attribute wisely
3. **Performance**: Avoid heavy processing in plugins
4. **Return values**: Always return the appropriate value
5. **Logging**: Log important operations for debugging

## Best Practices

### 1. Dependency Injection

**Good:**
```php
public function __construct(
    private LoggerInterface $logger,
    private ConfigurationProvider $configProvider
) {
}
```

**Bad:**
```php
public function __construct() {
    $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
}
```

### 2. Error Handling

**Good:**
```php
try {
    $result = $this->doSomething();
} catch (\Exception $e) {
    $this->logger->error('Failed to do something', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return $fallbackValue;
}
```

**Bad:**
```php
$result = $this->doSomething();
if (!$result) {
    die('Error');
}
```

### 3. Configuration

**Good:**
```php
$isEnabled = $this->configProvider->isReactEnabled();
```

**Bad:**
```php
$isEnabled = $this->scopeConfig->getValue('react_vue_config/react/enable');
```

### 4. HTML Processing

**Good:**
```php
$html = $this->htmlProcessor->removeMagentoInitScripts($html);
```

**Bad:**
```php
$html = preg_replace('/<script.*?>.*?<\/script>/is', '', $html);
```

### 5. Input Validation

**Good:**
```php
$value = $this->requestValidator->getValidatedBoolParam($request, 'param', false);
```

**Bad:**
```php
$value = isset($_GET['param']) && $_GET['param'] === 'true';
```

## Testing

### Unit Test Structure

```php
<?php

use YourModule\Service\YourService;

beforeEach(function () {
    $this->service = new YourService(/* dependencies */);
});

test('it does something correctly', function () {
    $result = $this->service->doSomething();
    expect($result)->toBeTrue();
});

test('it handles errors gracefully', function () {
    // Test error scenarios
});
```

### Running Tests

```bash
# All tests
vendor/bin/pest

# Specific directory
vendor/bin/pest tests/Unit/Service/

# Specific file
vendor/bin/pest tests/Unit/Service/ConfigurationProvider.test.php

# With coverage
vendor/bin/pest --coverage

# Verbose output
vendor/bin/pest --verbose
```

### Writing Testable Code

1. **Use dependency injection**: Makes mocking easy
2. **Single responsibility**: Each method does one thing
3. **Avoid static calls**: Hard to mock
4. **Return values**: Always return something testable
5. **Throw exceptions**: Instead of returning error codes

## Debugging

### Enable Magento Developer Mode

```bash
php bin/magento deploy:mode:set developer
php bin/magento cache:disable
```

### Logging

Add logging to your code:

```php
$this->logger->debug('Debug information', ['data' => $data]);
$this->logger->info('Information message');
$this->logger->warning('Warning message', ['context' => $context]);
$this->logger->error('Error occurred', ['error' => $e->getMessage()]);
```

View logs:
```bash
tail -f var/log/system.log
tail -f var/log/exception.log
tail -f var/log/debug.log
```

### Xdebug

Configure `php.ini`:
```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=localhost
xdebug.client_port=9003
```

### Server-Timing Headers

Check optimization performance:

```bash
curl -I https://yourstore.com/ | grep -i "server-timing"
```

Or in browser DevTools:
1. Open Network tab
2. Select a request
3. Look for "Timing" → "Server Timing"

### Parameter Override Testing

Test configurations without database changes:

```
https://yourstore.com/?js-junk=true&defer-js=true
```

Check response headers:
```bash
curl -I 'https://yourstore.com/?js-junk=true'
```

### Profiling

Use Magento's built-in profiler:

```bash
php bin/magento dev:profiler:enable
```

Or use Blackfire/New Relic for production profiling.

## Common Development Tasks

### Adding a New Configuration Option

1. Add to `etc/config.xml`:
```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <default>
        <react_vue_config>
            <my_section>
                <my_option>1</my_option>
            </my_section>
        </react_vue_config>
    </default>
</config>
```

2. Add to `Service/ConfigurationProvider.php`:
```php
private const CONFIG_PATH_MY_OPTION = 'react_vue_config/my_section/my_option';

public function isMyOptionEnabled(): bool
{
    return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_MY_OPTION);
}
```

3. Use in your code:
```php
if ($this->configProvider->isMyOptionEnabled()) {
    // Your logic
}
```

### Creating a Custom Observer

1. Create observer class:
```php
<?php
namespace YourModule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MyObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        // Your logic
    }
}
```

2. Register in `etc/events.xml`:
```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <event name="controller_action_layout_render_before">
        <observer name="your_module_observer" 
                  instance="YourModule\Observer\MyObserver" />
    </event>
</config>
```

### Adding Custom CSS Files

1. Create optimized CSS file:
```bash
cp custom-styles.css pub/static/custom-styles-m.css
```

2. Inject in plugin:
```php
protected function addCustomCss(string $html): string
{
    $cssLink = '<link rel="stylesheet" href="/static/custom-styles-m.css">';
    return str_replace('</head>', $cssLink . '</head>', $html);
}
```

## Contributing

### Code Style

Follow Magento coding standards:
```bash
vendor/bin/phpcs --standard=Magento2 /path/to/your/code
vendor/bin/phpcbf --standard=Magento2 /path/to/your/code
```

### Pull Request Process

1. Fork the repository
2. Create feature branch: `git checkout -b feature/my-feature`
3. Write tests for your changes
4. Ensure all tests pass: `vendor/bin/pest`
5. Update documentation
6. Commit with clear message
7. Push and create pull request

### Commit Message Format

```
[Type] Short description

Detailed description of changes.

- Change 1
- Change 2

Closes #issue_number
```

Types: `[Feature]`, `[Fix]`, `[Refactor]`, `[Docs]`, `[Test]`

## Resources

- [Magento DevDocs](https://developer.adobe.com/commerce/)
- [PHP Documentation](https://www.php.net/docs.php)
- [Pest Testing Framework](https://pestphp.com/)
- [ARCHITECTURE.md](ARCHITECTURE.md) - Architecture documentation
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide

## Getting Help

- Check existing documentation first
- Search GitHub issues
- Create a new issue with:
  - Clear title
  - Steps to reproduce
  - Expected vs actual behavior
  - Environment details
  - Relevant logs

## License

See [LICENSE.md](LICENSE.md) for license information.
