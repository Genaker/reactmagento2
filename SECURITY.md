# Security Policy

## Supported Versions

This module is actively maintained. Security updates are provided for the latest version.

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Recent Security Improvements

### Version 1.x.x (Current)
- **Fixed Path Traversal Vulnerability**: Implemented whitelist validation in `getInlineJs()` method to prevent unauthorized file access
- **Fixed XSS Vulnerability**: Replaced string concatenation with `json_encode()` for JavaScript variable output
- **Replaced Deprecated Functions**: Migrated from `mime_content_type()` to `finfo_file()` for PHP 8.1+ compatibility
- **Removed Superglobal Access**: Replaced direct `$_GET`, `$_POST`, `$_COOKIE` access with Magento's `RequestInterface`
- **Fixed Directory Traversal**: Implemented symlink protection in file copy operations
- **Removed Error Suppression**: Replaced `@` operators with proper error handling

## Security Best Practices

### For Developers

1. **Never use direct superglobal access** (`$_GET`, `$_POST`, `$_COOKIE`, `$_SERVER`)
   - Always use Magento's `RequestInterface` instead
   - Example: `$this->request->getParam('key')` instead of `$_GET['key']`

2. **Always escape output in templates**
   - Use `$this->escapeHtml()` for HTML context
   - Use `$this->escapeJs()` for JavaScript context
   - Use `json_encode()` for JSON data in JavaScript

3. **Validate file paths**
   - Use whitelist validation for file operations
   - Use `realpath()` and verify paths are within expected directories
   - Never construct file paths directly from user input

4. **Avoid error suppression**
   - Don't use `@` operator to hide errors
   - Implement proper error handling with try-catch blocks
   - Log errors appropriately

5. **Keep dependencies updated**
   - Regularly update npm packages: `npm audit` and `npm update`
   - Monitor security advisories for React, Webpack, and other dependencies

### For Users

1. **Keep the module updated**
   - Always use the latest version from the repository
   - Review CHANGELOG for security updates

2. **Use HTTPS**
   - Always serve your Magento store over HTTPS
   - Configure proper SSL/TLS certificates

3. **File Permissions**
   - Ensure proper file permissions on pub/static directories
   - Follow Magento's security best practices for file permissions

4. **Content Security Policy**
   - Consider implementing CSP headers to prevent XSS attacks
   - Test thoroughly before deploying to production

## Reporting a Vulnerability

If you discover a security vulnerability in this module, please report it responsibly:

1. **Do NOT open a public issue** for security vulnerabilities
2. **Email the maintainer** at egorshitikov@gmail.com with:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if available)
3. **Allow time for a fix** before public disclosure
   - We aim to respond within 48 hours
   - We aim to release a fix within 7-14 days for critical issues

## Security Checklist for New Features

Before submitting new features or modifications:

- [ ] No direct access to `$_GET`, `$_POST`, `$_COOKIE`, `$_SERVER`
- [ ] All user input is validated and sanitized
- [ ] All output in templates is properly escaped
- [ ] No use of `eval()`, `exec()`, or similar dangerous functions
- [ ] File operations validate paths and use whitelists
- [ ] No error suppression with `@` operator
- [ ] No secrets or credentials in code
- [ ] Dependencies are up-to-date and have no known vulnerabilities
- [ ] Security-focused code review completed

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Magento Security Best Practices](https://devdocs.magento.com/guides/v2.4/config-guide/prod/security.html)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [React Security](https://reactjs.org/docs/dom-elements.html#dangerouslysetinnerhtml)

## Acknowledgments

We appreciate the security research community and all contributors who help keep this module secure.
