<?php

namespace React\React\Service;

use Magento\Framework\App\Response\HttpInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for handling HTTP response headers safely
 */
class ResponseHeaderService
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Set response header safely
     * 
     * @param HttpInterface $response
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return void
     */
    public function setHeader(HttpInterface $response, string $name, string $value, bool $replace = false): void
    {
        try {
            // Check if headers have already been sent
            if (headers_sent($file, $line)) {
                $this->logger->debug("Cannot set header '$name', headers already sent", [
                    'file' => $file,
                    'line' => $line
                ]);
                return;
            }

            // Use response object's header method if available
            if (method_exists($response, 'setHeader')) {
                $response->setHeader($name, $value, $replace);
            } else {
                // Fallback to PHP's header function
                header("$name: $value", $replace);
            }
        } catch (\Exception $e) {
            $this->logger->warning("Failed to set header '$name'", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set Server-Timing header for performance tracking
     * 
     * @param HttpInterface $response
     * @param string $name
     * @param float $duration Duration in milliseconds
     * @return void
     */
    public function setServerTiming(HttpInterface $response, string $name, float $duration): void
    {
        $value = sprintf('%s;dur=%.2f', $name, $duration);
        $this->setHeader($response, 'Server-Timing', $value, false);
    }

    /**
     * Set custom React-Luma headers
     * 
     * @param HttpInterface $response
     * @param array $headers
     * @return void
     */
    public function setCustomHeaders(HttpInterface $response, array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($response, $name, (string) $value, false);
        }
    }
}
