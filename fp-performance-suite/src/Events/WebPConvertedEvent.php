<?php

namespace FP\PerfSuite\Events;

/**
 * Event fired when image is converted to WebP
 */
class WebPConvertedEvent extends Event
{
    public function name(): string
    {
        return 'webp_converted';
    }

    /**
     * Get original file path
     */
    public function getOriginalFile(): string
    {
        return (string)$this->get('original_file', '');
    }

    /**
     * Get WebP file path
     */
    public function getWebPFile(): string
    {
        return (string)$this->get('webp_file', '');
    }

    /**
     * Get size reduction percentage
     */
    public function getSizeReduction(): float
    {
        $originalSize = (int)$this->get('original_size', 0);
        $webpSize = (int)$this->get('webp_size', 0);
        
        if ($originalSize === 0) {
            return 0.0;
        }
        
        return round((($originalSize - $webpSize) / $originalSize) * 100, 2);
    }
}
