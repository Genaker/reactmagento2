<?php
/**
 * Shared mock classes for tests
 * Prevents duplicate class declarations when running all tests
 */

if (!class_exists('MockScopeConfig')) {
    class MockScopeConfig implements \Magento\Framework\App\Config\ScopeConfigInterface
    {
        private $values = [];
        
        public function __construct($values = [])
        {
            $this->values = $values;
        }
        
        public function getValue($path, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode = null)
        {
            return $this->values[$path] ?? null;
        }
        
        public function isSetFlag($path, $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode = null)
        {
            return (bool) $this->getValue($path, $scopeType, $scopeCode);
        }
        
        public function setValue($path, $value)
        {
            $this->values[$path] = $value;
        }
    }
}

if (!class_exists('MockRequest')) {
    /**
     * Mock Request object to simulate Magento's RequestInterface
     * Used for testing DeferJS, DeferCSS, and other classes that use request params
     */
    class MockRequest implements \Magento\Framework\App\RequestInterface
    {
        private $params = [];
        private $cookies = [];
        
        public function __construct($params = [], $cookies = [])
        {
            $this->params = $params;
            $this->cookies = $cookies;
        }
        
        public function getParam($key, $defaultValue = null)
        {
            return $this->params[$key] ?? $defaultValue;
        }
        
        public function getCookie($name, $default = null)
        {
            return $this->cookies[$name] ?? $default;
        }
        
        public function setParam($key, $value)
        {
            $this->params[$key] = $value;
            return $this;
        }
        
        // Required interface methods (minimal implementations)
        public function getModuleName() { return 'test'; }
        public function setModuleName($name) { return $this; }
        public function getActionName() { return 'test'; }
        public function setActionName($name) { return $this; }
        public function getControllerName() { return 'test'; }
        public function setControllerName($name) { return $this; }
        public function getParams() { return $this->params; }
        public function setParams(array $params) { $this->params = $params; return $this; }
        public function getRequestString() { return ''; }
        public function getMethod() { return 'GET'; }
        public function isSecure() { return true; }
        public function getQuery($key = null, $default = null) { return $default; }
        public function getPost($key = null, $default = null) { return $default; }
        public function isXmlHttpRequest() { return false; }
        public function isGet() { return true; }
        public function isPost() { return false; }
        public function isPut() { return false; }
        public function isDelete() { return false; }
        public function getPathInfo() { return '/'; }
        public function setPathInfo($pathInfo = null) { return $this; }
        public function getRequestUri() { return '/'; }
        public function getDistroBaseUrl() { return 'http://example.com'; }
        public function getHeader($header, $default = false) { return $default; }
        public function getServer($key = null, $default = null) { return $default; }
        public function getContent() { return ''; }
        public function getFullActionName($delimiter = '_') { return 'test_test_test'; }
    }
}
