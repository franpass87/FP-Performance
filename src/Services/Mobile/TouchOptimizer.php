<?php

namespace FP\PerfSuite\Services\Mobile;

class TouchOptimizer
{
    private $gesture_optimization;
    
    public function __construct($gesture_optimization = true)
    {
        $this->gesture_optimization = $gesture_optimization;
    }
    
    public function init()
    {
        add_action('wp_footer', [$this, 'addTouchOptimizations'], 51);
    }
    
    public function addTouchOptimizations()
    {
        if (!$this->gesture_optimization) return;
        
        echo '<script>
            // Touch Gesture Optimization
            if ("ontouchstart" in window) {
                let touchStartX = 0;
                let touchStartY = 0;
                let touchEndX = 0;
                let touchEndY = 0;
                
                document.addEventListener("touchstart", function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartY = e.changedTouches[0].screenY;
                });
                
                document.addEventListener("touchend", function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    touchEndY = e.changedTouches[0].screenY;
                    
                    handleSwipe();
                });
                
                function handleSwipe() {
                    const deltaX = touchEndX - touchStartX;
                    const deltaY = touchEndY - touchStartY;
                    const minSwipeDistance = 50;
                    
                    if (Math.abs(deltaX) > Math.abs(deltaY)) {
                        if (Math.abs(deltaX) > minSwipeDistance) {
                            if (deltaX > 0) {
                                // Swipe right
                                document.dispatchEvent(new CustomEvent("swipeRight"));
                            } else {
                                // Swipe left
                                document.dispatchEvent(new CustomEvent("swipeLeft"));
                            }
                        }
                    } else {
                        if (Math.abs(deltaY) > minSwipeDistance) {
                            if (deltaY > 0) {
                                // Swipe down
                                document.dispatchEvent(new CustomEvent("swipeDown"));
                            } else {
                                // Swipe up
                                document.dispatchEvent(new CustomEvent("swipeUp"));
                            }
                        }
                    }
                }
                
                // Optimize touch events
                document.addEventListener("touchmove", function(e) {
                    // Prevent default on certain elements
                    if (e.target.closest(".no-touch-move")) {
                        e.preventDefault();
                    }
                }, { passive: false });
                
                // Add touch feedback
                document.addEventListener("touchstart", function(e) {
                    e.target.classList.add("touch-active");
                });
                
                document.addEventListener("touchend", function(e) {
                    setTimeout(() => {
                        e.target.classList.remove("touch-active");
                    }, 150);
                });
            }
        </script>';
        
        echo '<style>
            .touch-active {
                transform: scale(0.95);
                transition: transform 0.1s;
            }
            
            .no-touch-move {
                touch-action: none;
            }
            
            @media (hover: none) and (pointer: coarse) {
                .touch-optimized {
                    -webkit-tap-highlight-color: transparent;
                    -webkit-touch-callout: none;
                    -webkit-user-select: none;
                    user-select: none;
                }
            }
        </style>';
    }
    
    public function getTouchMetrics()
    {
        return [
            'gesture_optimization' => $this->gesture_optimization,
            'touch_support' => isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}