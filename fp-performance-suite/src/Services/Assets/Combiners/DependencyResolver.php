<?php

namespace FP\PerfSuite\Services\Assets\Combiners;

use function array_filter;
use function array_shift;
use function array_values;
use function count;
use function is_array;
use function is_string;
use function trim;
use function usort;

/**
 * Dependency Resolver for Asset Combination
 * 
 * Performs topological sort on dependencies to determine safe combination order
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class DependencyResolver
{
    /**
     * Resolve dependencies using topological sort
     * 
     * @param array<string, array{handle:string,path:string,url:string,deps:array<int,string>}> $candidates
     * @param array<string, int> $positions Original queue positions
     * @return array<int, string>|null Ordered handles or null if circular dependency
     */
    public function resolveDependencies(array $candidates, array $positions): ?array
    {
        if (count($candidates) < 2) {
            return null;
        }

        // Build indegree map and edges
        $indegree = [];
        $edges = [];
        foreach ($candidates as $handle => $data) {
            $indegree[$handle] = 0;
        }

        foreach ($candidates as $handle => $data) {
            foreach ($data['deps'] as $dependency) {
                if (!isset($candidates[$dependency])) {
                    continue;
                }
                $edges[$dependency][] = $handle;
                $indegree[$handle]++;
            }
        }

        // Find nodes with no incoming edges
        $available = [];
        foreach ($indegree as $handle => $count) {
            if (0 === $count) {
                $available[] = $handle;
            }
        }

        if (empty($available)) {
            // Circular dependency detected
            return null;
        }

        // Sort by original queue position
        $sortByPosition = static function (string $a, string $b) use ($positions): int {
            $posA = $positions[$a] ?? PHP_INT_MAX;
            $posB = $positions[$b] ?? PHP_INT_MAX;
            return $posA <=> $posB;
        };

        usort($available, $sortByPosition);

        // Kahn's algorithm for topological sort
        $ordered = [];
        while (!empty($available)) {
            $current = array_shift($available);
            $ordered[] = $current;

            foreach ($edges[$current] ?? [] as $next) {
                $indegree[$next]--;
                if (0 === $indegree[$next]) {
                    $available[] = $next;
                }
            }

            if (!empty($available)) {
                usort($available, $sortByPosition);
            }
        }

        // Check if all nodes were processed (no cycles)
        if (count($ordered) !== count($candidates)) {
            return null;
        }

        return $ordered;
    }

    /**
     * Filter candidates that have external dependencies
     * 
     * @param array<string, array{handle:string,path:string,url:string,deps:array<int,string>}> $candidates
     * @param array<string, bool> $queueLookup Map of all queued handles
     * @return array<string, array{handle:string,path:string,url:string,deps:array<int,string>}>
     */
    public function filterExternalDependencies(array $candidates, array $queueLookup): array
    {
        do {
            $changed = false;
            foreach ($candidates as $handle => $data) {
                foreach ($data['deps'] as $dependency) {
                    if (!isset($queueLookup[$dependency])) {
                        continue;
                    }
                    if (!isset($candidates[$dependency])) {
                        // This candidate depends on a queued item that's not combinable
                        unset($candidates[$handle]);
                        $changed = true;
                        continue 3;
                    }
                }
            }
        } while ($changed);

        return $candidates;
    }

    /**
     * Normalize dependencies array
     * 
     * @param mixed $depsProperty
     * @return array<int, string>
     */
    public function normalizeDependencies($depsProperty): array
    {
        $deps = is_array($depsProperty) ? $depsProperty : (array) $depsProperty;
        return array_values(array_filter($deps, static function ($dep) {
            return is_string($dep) && '' !== trim($dep);
        }));
    }
}