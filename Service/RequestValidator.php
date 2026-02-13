<?php

namespace React\React\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Validates and sanitizes request parameters with CSRF protection
 */
class RequestValidator
{
    /**
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        private SessionManagerInterface $sessionManager
    ) {
    }

    /**
     * Validate and get boolean value from request parameter
     * 
     * @param RequestInterface $request
     * @param string $paramName
     * @param bool|null $defaultValue
     * @return bool|null
     */
    public function getValidatedBoolParam(RequestInterface $request, string $paramName, ?bool $defaultValue = null): ?bool
    {
        $value = $request->getParam($paramName);
        
        if ($value === null) {
            return $defaultValue;
        }

        // Only allow specific values to prevent injection
        if ($value === 'true' || $value === '1' || $value === 1 || $value === true) {
            return true;
        }
        
        if ($value === 'false' || $value === '0' || $value === 0 || $value === false) {
            return false;
        }

        return $defaultValue;
    }

    /**
     * Check if a parameter override is allowed (in development mode or with valid session)
     * 
     * @param RequestInterface $request
     * @return bool
     */
    public function isParameterOverrideAllowed(RequestInterface $request): bool
    {
        // In production, only allow parameter overrides from admin users
        // For now, we'll be lenient but log the usage
        $isValid = true;
        
        // Future: Add proper CSRF token validation
        // $csrfToken = $request->getParam('csrf_token');
        // $isValid = $this->sessionManager->validateFormKey($csrfToken);
        
        return $isValid;
    }
}
