<?php

namespace FP\PerfSuite\Services\Logs;

class RealtimeLog
{
    private DebugToggler $toggler;

    public function __construct(DebugToggler $toggler)
    {
        $this->toggler = $toggler;
    }

    public function tail(int $lines = 200, string $level = '', string $query = ''): array
    {
        $status = $this->toggler->status();
        $file = $status['log_file'];
        if (empty($file) || !file_exists($file)) {
            return [];
        }
        $content = $this->readTail($file, $lines);
        $rows = explode("\n", trim($content));
        $filtered = array_filter($rows, static function ($row) use ($level, $query) {
            $match = true;
            if ($level) {
                $match = stripos($row, $level) !== false;
            }
            if ($match && $query) {
                $match = stripos($row, $query) !== false;
            }
            return $match;
        });
        return array_values($filtered);
    }

    private function readTail(string $file, int $lines): string
    {
        $handle = fopen($file, 'rb');
        if (!$handle) {
            return '';
        }
        $buffer = '';
        $chunk = 4096;
        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);
        while ($position > 0 && $lines > 0) {
            $seek = max($position - $chunk, 0);
            $read = $position - $seek;
            fseek($handle, $seek);
            $buffer = fread($handle, $read) . $buffer;
            $position = $seek;
            $lines -= substr_count($buffer, "\n");
        }
        fclose($handle);
        $parts = explode("\n", trim($buffer));
        return implode("\n", array_slice($parts, -$lines));
    }
}
