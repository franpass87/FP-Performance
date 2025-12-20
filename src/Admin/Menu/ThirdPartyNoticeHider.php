<?php

namespace FP\PerfSuite\Admin\Menu;

use function get_current_screen;

/**
 * Nasconde i notice di altri plugin nelle pagine del plugin
 * 
 * @package FP\PerfSuite\Admin\Menu
 * @author Francesco Passeri
 */
class ThirdPartyNoticeHider
{
    /**
     * Nasconde i notice di altri plugin nelle pagine del plugin FP Performance
     */
    public function hideThirdPartyNotices(): void
    {
        // Controlla se siamo su una pagina del plugin
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'fp-performance-suite') === false) {
            return;
        }
        
        // Inietta CSS e JavaScript per nascondere i notice di altri plugin
        ?>
        <style type="text/css">
            /* Stile per i notice del plugin FP Performance */
            .fp-ps-admin-notice {
                margin: 15px 0;
            }
        </style>
        <script type="text/javascript">
            // BUGFIX #28-29: Wrapper waitForjQuery per evitare "jQuery is not defined"
            (function waitForjQuery() {
                if (typeof jQuery === 'undefined') {
                    setTimeout(waitForjQuery, 50);
                    return;
                }
                jQuery(document).ready(function($) {
                    // Rimuovi i notice di altri plugin nelle pagine FP Performance
                    $('.notice:not(.fp-ps-admin-notice)').each(function() {
                        const $notice = $(this);
                        // Mantieni solo i notice di WordPress core e del plugin FP Performance
                        if (!$notice.hasClass('fp-ps-admin-notice') && 
                            !$notice.closest('.fp-performance-suite').length) {
                            $notice.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    });
                });
            })();
        </script>
        <?php
    }
}















