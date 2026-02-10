# Changelog

All notable changes to the React-Luma module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Security
- **CRITICAL**: Fixed path traversal vulnerability in `Template.php` getInlineJs() method
  - Implemented whitelist validation for allowed JavaScript files
  - Added realpath() verification to prevent directory traversal
  - Prevents unauthorized file system access
- **HIGH**: Fixed XSS vulnerability in `react-header.phtml` template
  - Replaced string concatenation with json_encode() for JavaScript output
  - Prevents script injection attacks
- **HIGH**: Eliminated all direct superglobal access ($_GET, $_COOKIE, $_POST)
  - Replaced with Magento's RequestInterface throughout codebase
  - Affects: Template.php, DeferJS.php, DeferCSS.php, RemoveMagentoInitScripts.php
- **MEDIUM**: Fixed directory traversal in PostDeployCopy.php
  - Replaced opendir()/readdir() with RecursiveDirectoryIterator
  - Added symlink protection to prevent traversal attacks
  - Improved error handling

### Changed
- **BREAKING**: Updated React from 16.8.6 to 18.2.0
  - May require changes in custom React components
  - See [React 18 upgrade guide](https://react.dev/blog/2022/03/08/react-18-upgrade-guide)
- **BREAKING**: Updated Webpack from 4.x to 5.x
  - Updated webpack.config.js for Webpack 5 compatibility
  - Changed CopyWebpackPlugin syntax to use patterns array
- Updated Babel from 7.4.x to 7.23.x
- Updated babel-loader from 8.0.5 to 9.1.3
- Updated css-loader from 2.1.1 to 6.8.1
- Updated style-loader from 0.23.1 to 3.3.3
- Updated webpack-cli from 3.3.2 to 5.1.4
- Updated copy-webpack-plugin from 5.0.3 to 11.0.0
- Updated html-react-parser from 0.7.1 to 5.1.0
- Updated js-cookie from 2.2.0 to 3.0.5
- Updated webpack-livereload-plugin from 2.2.0 to 3.0.2
- Updated html-webpack-harddisk-plugin from 1.0.1 to 2.0.0

### Fixed
- **PHP 8.1 Compatibility**: Replaced deprecated mime_content_type() with finfo_file()
  - Fixes compatibility issues with PHP 8.1+
  - Affects Template.php imageToBase64() method
- Fixed typo in JavaScript variable naming: curentUenc → currentUenc
  - Affects: react-header.phtml, react-core.js, react-core.min.js
- Removed error suppression (@) operators
  - Added proper error handling in react-header.phtml
  - Improved code reliability and debugging
- Fixed spelling: "Dirrectory" → "Directory" in webpack.config.js

### Added
- Created comprehensive SECURITY.md documentation
  - Security best practices for developers
  - Vulnerability reporting process
  - Security checklist for new features
- Added PHPDoc type hints to security-critical methods
- Added MockRequest class for unit testing
  - Implements RequestInterface for proper test isolation
  - Replaces direct $_GET manipulation in tests
- Updated unit tests to use MockRequest
  - DeferJS.test.php now uses proper mocking
  - DeferCSS.test.php now uses proper mocking

### Improved
- Enhanced code documentation with inline comments
- Improved error handling throughout the codebase
- Added security validation in file operations
- Strengthened input validation

## Security Notes

### For Users Upgrading
1. **Review custom code**: If you have custom JavaScript that uses `window.curentUenc`, update it to `window.currentUenc`
2. **Test thoroughly**: The React 18 and Webpack 5 updates may affect custom implementations
3. **Check file permissions**: Ensure proper permissions on pub/static directories after upgrade
4. **Review security**: See SECURITY.md for new security guidelines

### For Developers
- All new code must use Magento's RequestInterface instead of superglobals
- Follow the security checklist in SECURITY.md before submitting PRs
- Run security scans: `npm audit` and CodeQL before deploying

## Verification
- ✅ Code review completed with no issues
- ✅ CodeQL security scan passed with 0 alerts
- ✅ All unit tests updated and passing
- ✅ No regression in functionality

## Migration Guide

### Updating from Previous Versions

1. **Backup your installation**
2. **Update via Composer**
   ```bash
   composer require genaker/react-luma
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```
3. **Install new npm dependencies** (if building from source)
   ```bash
   cd vendor/genaker/magento-reactjs
   npm install
   npm run build
   ```
4. **Test your site thoroughly**
   - Verify all React components still work
   - Test cart, checkout, and product pages
   - Check browser console for errors

### Breaking Changes Details

#### React 18 Update
- Automatic batching is now enabled by default
- `ReactDOM.render` is deprecated (use `createRoot`)
- Concurrent features are opt-in
- See migration guide: https://react.dev/blog/2022/03/08/react-18-upgrade-guide

#### Webpack 5 Update
- Node.js polyfills are no longer included by default
- Module federation is now available
- Better tree shaking and chunk splitting
- Asset modules replace file-loader, url-loader, raw-loader

## Acknowledgments
- Thanks to the security research community for responsible disclosure practices
- Contributors who reported issues and provided feedback

---

For security vulnerabilities, please see SECURITY.md for reporting instructions.
