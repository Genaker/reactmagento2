<?php
// React-Luma extended Template for Block
namespace React\React;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template as MTemplate;
use Magento\Framework\View\Element\Template\Context;

class Template extends MTemplate
{
    public $om;
    public $registry;
    public $config;
    
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Context $context,
        ObjectManager $om,
        Registry $registry,
        ScopeConfigInterface $config,
        array $data = []
    ) {
        $this->om = $om;
        $this->registry = $registry;
        $this->config = $config;
        $this->request = $context->getRequest();

        parent::__construct($context, $data);
    }

    /**
     * Function to encode an image as Base64
     * 
     * @param string $imagePath
     * @return string
     */
    public function imageToBase64($imagePath)
    {
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $base64 = base64_encode($imageData);
            
            // Use finfo instead of deprecated mime_content_type()
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $imagePath);
            finfo_close($finfo);
            
            return "data:$mimeType;base64,$base64";
        }
        return "";
    }

    /**
     * Check if Adobe JS Junk removal is enabled
     * 
     * @return bool
     */
    public function removeAdobeJSJunk()
    {
        // Check cookie first (use request object)
        $cookieValue = $this->request->getCookie('js-junk');
        if ($cookieValue !== null) {
            return $cookieValue === "true";
        }
        
        // Fall back to GET parameter (use request object)
        $getParam = $this->request->getParam('js-junk');
        if ($getParam === "false") {
            return false;
        }
        if ($getParam === "true") {
            return true;
        }
        
        // Fall back to config
        return boolval($this->config->getValue('react_vue_config/junk/remove'));
    }

    /**
     * Check if Adobe CSS Junk removal is enabled
     * 
     * @return bool
     */
    public function removeAdobeCSSJunk()
    {
        // Check cookie first (use request object)
        $cookieValue = $this->request->getCookie('css-react');
        if ($cookieValue !== null) {
            return $cookieValue === "true";
        }
        
        // Fall back to GET parameter (use request object)
        $getParam = $this->request->getParam('css-react');
        if ($getParam === null) {
            return boolval($this->config->getValue('react_vue_config/junk/remove'));
        }

        if ($getParam === "false") {
            return false;
        }
        if ($getParam === "true") {
            return true;
        }
        
        // Fall back to config (should not reach here, but safety fallback)
        return boolval($this->config->getValue('react_vue_config/junk/remove'));
    }

    /**
     * Check if JS deferral is enabled
     * 
     * @return bool
     */
    public function deferJS()
    {
        // Check GET parameter first (use request object)
        $getParam = $this->request->getParam('defer-js');
        if ($getParam === "false") {
            return false;
        }
        if ($getParam === "true") {
            return true;
        }
        
        // Fall back to config (default to true if not set)
        $configValue = $this->config->getValue('react_vue_config/junk/defer_js');
        return $configValue === null || $configValue === '' ? true : boolval($configValue);
    }

    /**
     * Get inline JS content from a file
     * Security: Only allow whitelisted filenames to prevent path traversal
     * 
     * @param string $file
     * @return string
     */
    public function getInlineJs($file) {
        // Whitelist of allowed JS files to prevent path traversal attacks
        $allowedFiles = [
            'cash.js',
            'custom.js',
            'utils.js'
        ];
        
        // Validate filename against whitelist
        if (!in_array($file, $allowedFiles, true)) {
            return '<script>console.error("Invalid JS file requested");</script>';
        }
        
        // Construct safe path and validate it exists
        $filePath = __DIR__ . '/view/frontend/web/js/' . $file;
        $realPath = realpath($filePath);
        
        // Additional security: ensure the real path is within the expected directory
        $expectedDir = realpath(__DIR__ . '/view/frontend/web/js/');
        if ($realPath === false || strpos($realPath, $expectedDir) !== 0) {
            return '<script>console.error("Invalid JS file path");</script>';
        }
        
        if (!file_exists($realPath)) {
            return '<script>console.error("JS file not found");</script>';
        }
        
        $jsContent = file_get_contents($realPath);
        return '<script>' . $jsContent . '</script>';
    }


    /**
     * Check if minification is enabled
     *
     * @return bool|null
     */
    public function isMinifyEnabled($flag = false)
    {
        return $this->getData('minify');
    }

}
