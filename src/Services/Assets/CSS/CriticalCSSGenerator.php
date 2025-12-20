<?php

namespace FP\PerfSuite\Services\Assets\CSS;

/**
 * Genera CSS critico per above-the-fold content
 * 
 * @package FP\PerfSuite\Services\Assets\CSS
 * @author Francesco Passeri
 */
class CriticalCSSGenerator
{
    /**
     * Genera CSS critico ottimizzato
     * 
     * @return string CSS critico
     */
    public function generate(): string
    {
        return '
            /* Critical CSS for villadianella.it - Above the fold optimization */
            * { box-sizing: border-box; }
            
            body { 
                font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 0;
                color: #333;
                background: #fff;
            }
            
            /* Header critical styles */
            .site-header, header, .header { 
                display: block;
                position: relative;
                z-index: 100;
                background: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .site-branding, .logo {
                display: block;
                padding: 1rem 0;
            }
            
            .site-title, .logo-text {
                font-size: 1.5rem;
                font-weight: bold;
                margin: 0;
                color: #333;
            }
            
            /* Navigation critical styles */
            .main-navigation, .nav-menu {
                display: block;
                position: relative;
            }
            
            .nav-menu li {
                display: inline-block;
                margin: 0 1rem;
            }
            
            .nav-menu a {
                text-decoration: none;
                color: #333;
                font-weight: 500;
                padding: 0.5rem 0;
                display: block;
            }
            
            /* Hero section critical styles */
            .hero, .banner, .hero-section, .entry-header {
                display: block;
                position: relative;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 4rem 0;
                text-align: center;
            }
            
            .hero h1, .entry-title {
                font-size: 2.5rem;
                font-weight: bold;
                margin: 0 0 1rem 0;
                line-height: 1.2;
            }
            
            .hero p, .entry-summary {
                font-size: 1.2rem;
                margin: 0 0 2rem 0;
                opacity: 0.9;
            }
            
            /* Content critical styles */
            .site-main, main, .main, .content {
                display: block;
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem 1rem;
            }
            
            /* Button critical styles */
            .button, .btn, button {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background: #667eea;
                color: #fff;
                text-decoration: none;
                border-radius: 4px;
                font-weight: 500;
                border: none;
                cursor: pointer;
                transition: background 0.3s;
            }
            
            .button:hover, .btn:hover, button:hover {
                background: #5568d3;
            }
        ';
    }
}
















