# Deployment and Testing Guide

## Prerequisites

- PHP 7.4 or higher
- Composer
- Node.js and npm (for CSS compilation and purging)
- Magento 2.4.x

## Installation

### 1. Install via Composer

```bash
composer require genaker/react-luma
```

### 2. Enable the Module

```bash
php bin/magento module:enable React_React
php bin/magento setup:upgrade
php bin/magento cache:clean
```

### 3. Deploy Static Content

React-Luma uses pre-optimized CSS files. Copy them to your pub/static directory:

```bash
cp -R vendor/genaker/react-luma/pub/static/* pub/static/
```

Alternatively, enable the automatic deployment plugin which copies files after `setup:static-content:deploy`.

### 4. Configure the Module

#### Via Admin Panel

Navigate to: **Stores > Configuration > React-Luma integration > Configuration**

Available settings:
- **React/Vue Framework**: Enable React or Vue.js framework support
- **Remove Magento JS Junk**: Remove Magento's x-magento-init scripts
- **Defer JS**: Move JavaScript to page bottom
- **Remove Magento CSS**: Use optimized CSS files instead of Magento defaults
- **Critical CSS**: Enable critical CSS loading
- **Defer CSS**: Defer non-critical CSS

#### Via CLI

```bash
# Enable JS junk removal
php bin/magento config:set react_vue_config/junk/remove 1

# Enable JS deferral
php bin/magento config:set react_vue_config/junk/defer_js 1

# Disable JavaScript bundling (recommended)
php bin/magento config:set dev/js/enable_js_bundling 0

# Enable JavaScript minification
php bin/magento config:set dev/js/minify_files 1

# Clear cache
php bin/magento cache:clean
```

### Complete Setup for Optimal Performance

```bash
php bin/magento config:set react_vue_config/junk/defer_js 1
php bin/magento config:set react_vue_config/junk/remove 1
php bin/magento config:set react_vue_config/css/remove 1
php bin/magento config:set dev/js/enable_js_bundling 0
php bin/magento config:set dev/js/minify_files 1
php bin/magento cache:clean
```

## CSS Compilation

### Compile SCSS to CSS

Navigate to the module directory and run:

```bash
cd vendor/genaker/react-luma/
node css-compile.js
```

This will:
- Find all `.scss` files in `pub/static/`
- Compile them to minified CSS using Sass
- Apply PostCSS with autoprefixer and cssnano
- Output `.min.css` files with statistics

### CSS Purging

To remove unused CSS selectors:

```bash
cd vendor/genaker/react-luma/
node css-purge.js --css path/to/your/file.css
```

Options:
- `--css <path>`: Path to CSS file to purge
- `--url <url>`: Fetch content from URL
- `--path <path>`: Scan local files
- `--config <path>`: Use custom configuration file

For more details, see `PURGE_README.md`.

## Testing

### Running Unit Tests

The module uses Pest for testing. To run tests:

```bash
# Install dependencies (if not already installed)
composer install

# Run all tests
vendor/bin/pest

# Run specific test suite
vendor/bin/pest tests/Unit/Service/

# Run with coverage
vendor/bin/pest --coverage
```

### Testing Configuration Overrides

You can test configuration changes without modifying the database using URL parameters:

**Enable/Disable JS Junk Removal:**
```
https://yourstore.com/?js-junk=true
https://yourstore.com/?js-junk=false
```

**Enable/Disable JS Deferral:**
```
https://yourstore.com/?defer-js=true
https://yourstore.com/?defer-js=false
```

**Enable/Disable CSS Deferral:**
```
https://yourstore.com/?defer-css=true
https://yourstore.com/?defer-css=false
```

**Note**: These parameters are validated and sanitized for security. Use them for testing only.

## Troubleshooting

### CSS Not Loading

**Issue**: Layout is broken due to missing CSS files.

**Solution**:
1. Check if optimized CSS files exist:
   ```bash
   ls -la pub/static/styles-m.css
   ls -la pub/static/styles-l.css
   ```
2. If missing, copy from module:
   ```bash
   cp -R vendor/genaker/react-luma/pub/static/* pub/static/
   ```
3. Clear cache:
   ```bash
   php bin/magento cache:clean
   ```

### Performance Not Improved

**Issue**: No performance improvement after installation.

**Solution**:
1. Verify configuration is enabled:
   ```bash
   php bin/magento config:show react_vue_config/junk/defer_js
   php bin/magento config:show react_vue_config/junk/remove
   ```
2. Disable Magento bundling (it's harmful):
   ```bash
   php bin/magento config:set dev/js/enable_js_bundling 0
   ```
3. Clear all caches:
   ```bash
   php bin/magento cache:flush
   ```
4. Check Server-Timing headers for optimization metrics

### Headers Already Sent Error

**Issue**: PHP warning about headers already sent.

**Solution**: This is handled gracefully by the module. Check logs:
```bash
tail -f var/log/system.log | grep "Cannot set header"
```

The module will log the issue but continue working. If frequent, check for:
- Output before headers in custom code
- BOM characters in PHP files
- Whitespace before `<?php` tags

### DOM Parser Issues

**Issue**: HTML processing errors in logs.

**Solution**: The module automatically falls back to regex-based processing if DOM parsing fails. Check:
```bash
grep "Failed to parse HTML" var/log/system.log
```

If this occurs frequently, the HTML might be malformed. The module will still function but may be less accurate.

### Tests Failing

**Issue**: Unit tests fail after installation.

**Solution**:
1. Install dev dependencies:
   ```bash
   composer install --dev
   ```
2. Check PHP version (requires 7.4+):
   ```bash
   php -v
   ```
3. Run tests with verbose output:
   ```bash
   vendor/bin/pest --verbose
   ```

## Store-Specific Configuration

For multi-store setups, you can have store-specific CSS:

```
pub/static/
├── styles-m.css              # Default store mobile
├── styles-l.css              # Default store desktop
├── fr/                       # French store
│   ├── styles-m.css
│   └── styles-l.css
└── de/                       # German store
    ├── styles-m.css
    └── styles-l.css
```

The module automatically detects the current store and loads appropriate files.

## Development Mode

### Disabling Optimizations for Development

To disable optimizations during development:

```bash
# Via config
php bin/magento config:set react_vue_config/junk/remove 0
php bin/magento config:set react_vue_config/junk/defer_js 0
php bin/magento cache:clean

# Or use URL parameters
https://yourstore.com/?js-junk=false&defer-js=false
```

### Webpack Development

For React/Vue development:

```bash
cd vendor/genaker/react-luma/
npm install
npm start  # Starts webpack in watch mode
```

This will:
- Watch for changes in `src/`
- Compile React/Vue components
- Auto-deploy to `pub/static/`
- Enable LiveReload (optional)

## Production Deployment

### Checklist

- [ ] All CSS files copied to `pub/static/`
- [ ] Configuration enabled via CLI or admin
- [ ] JavaScript bundling disabled
- [ ] JavaScript minification enabled
- [ ] Caches cleared
- [ ] Static content deployed
- [ ] Site tested with parameter overrides
- [ ] Server-Timing headers verified
- [ ] Performance metrics improved (Core Web Vitals)

### Post-Deployment Verification

```bash
# Check configuration
php bin/magento config:show react_vue_config/junk/remove
php bin/magento config:show react_vue_config/junk/defer_js

# Verify CSS files
ls -la pub/static/styles-*.css

# Test homepage
curl -I https://yourstore.com/ | grep -i "x-built-with"
# Should show: x-built-with: React-Luma

# Check Server-Timing
curl -I https://yourstore.com/ | grep -i "server-timing"
```

## Performance Monitoring

### Server-Timing Headers

The module adds performance metrics via Server-Timing headers:

- `x-mag-init`: Time to remove Magento init scripts (ms)
- `x-mag-react`: Total optimization time (ms)

View in browser DevTools:
1. Open DevTools (F12)
2. Network tab
3. Select any request
4. Look for "Timing" tab
5. Check "Server Timing" section

### Core Web Vitals

Monitor improvements in:
- **LCP (Largest Contentful Paint)**: Should improve due to CSS optimization
- **FID (First Input Delay)**: Should improve due to JS deferral
- **CLS (Cumulative Layout Shift)**: Should remain stable
- **TBT (Total Blocking Time)**: Should decrease significantly
- **TTI (Time to Interactive)**: Should improve

## Security Considerations

### Parameter Override Security

URL parameter overrides are:
- Validated using whitelist approach
- Type-checked to prevent injection
- Logged for audit purposes
- Should only be used for testing

In production, consider restricting parameter overrides to admin users only by modifying `RequestValidator::isParameterOverrideAllowed()`.

### Content Security Policy

If using CSP headers, ensure they allow:
- Inline styles (for critical CSS)
- Inline scripts (for defer implementations)
- Or use nonces/hashes as appropriate

## Additional Resources

- [ARCHITECTURE.md](ARCHITECTURE.md) - Detailed architecture documentation
- [PURGE_README.md](PURGE_README.md) - CSS purge tool documentation
- [README.md](README.md) - General module information
- GitHub Issues: Report bugs and feature requests

## Support

For issues, questions, or contributions:
- GitHub: https://github.com/Genaker/reactmagento2
- Documentation: See ARCHITECTURE.md
- Tests: See tests/Unit/ directory
