<?php

namespace FP\PerfSuite\Utils;

class Semaphore
{
    /**
     * @param string $key
     * @param string $color
     * @param string $description
     * @return array<string, string>
     */
    public function describe(string $key, string $color, string $description): array
    {
        return [
            'key' => $key,
            'color' => $color,
            'description' => $description,
        ];
    }
}
