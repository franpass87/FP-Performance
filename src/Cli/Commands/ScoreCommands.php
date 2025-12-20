<?php

namespace FP\PerfSuite\Cli\Commands;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Score\Scorer;

/**
 * Comandi WP-CLI per il calcolo del performance score
 * 
 * @package FP\PerfSuite\Cli\Commands
 * @author Francesco Passeri
 */
class ScoreCommands
{
    /**
     * Calculate performance score
     */
    public function calculate(): void
    {
        try {
            $container = Plugin::container();
            $scorer = $container->get(Scorer::class);

            \WP_CLI::log('Calculating performance score...');
            $score = $scorer->calculate();

            \WP_CLI::log("\n" . \WP_CLI::colorize('%G=== Performance Score ===%n'));
            \WP_CLI::log(\WP_CLI::colorize('%YTotal Score: %G' . $score['total'] . '%n'));

            \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Breakdown ===%n'));
            foreach ($score['breakdown'] as $label => $points) {
                $color = $points >= 10 ? '%G' : ($points >= 5 ? '%Y' : '%R');
                \WP_CLI::log("  {$label}: " . \WP_CLI::colorize("{$color}{$points}%n"));
            }

            if (!empty($score['suggestions'])) {
                \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Suggestions ===%n'));
                foreach ($score['suggestions'] as $suggestion) {
                    \WP_CLI::log("  - {$suggestion}");
                }
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to calculate score: ' . $e->getMessage());
        }
    }
}
















