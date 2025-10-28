<?php

namespace FP\PerfSuite\Services\Logs;

class RealtimeLog
{
    public const MAX_LINES = 1000;
    private DebugToggler $toggler;

    public function __construct(DebugToggler $toggler)
    {
        $this->toggler = $toggler;
    }

    public function tail(int $lines = 200, string $level = '', string $query = ''): array
    {
        $lines = max(1, min(self::MAX_LINES, $lines));
        $status = $this->toggler->status();
        $file = $status['log_file'];
        if (empty($file) || !file_exists($file)) {
            return [];
        }
        $content = rtrim($this->readTail($file, $lines), "\r\n");
        if ($content === '') {
            return [];
        }
        $rows = preg_split('/\r?\n/', $content) ?: [];
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
        $filtered = array_values($filtered);
        $requested = min($lines, count($filtered));
        if ($requested === 0) {
            return [];
        }
        if (count($filtered) > $requested) {
            $filtered = array_slice($filtered, -$requested);
        }
        return $filtered;
    }

    private function readTail(string $file, int $lines): string
    {
        $handle = fopen($file, 'rb');
        if (!$handle) {
            return '';
        }
        $targetLines = max(0, min(self::MAX_LINES, $lines));
        if ($targetLines === 0) {
            fclose($handle);
            return '';
        }
        $buffer = '';
        $chunk = 4096;
        $remainingLines = $targetLines;
        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);
        while ($position > 0 && $remainingLines > 0) {
            $seek = max($position - $chunk, 0);
            $read = $position - $seek;
            fseek($handle, $seek);
            $chunkBuffer = fread($handle, $read);
            if ($chunkBuffer === false) {
                break;
            }
            $buffer = $chunkBuffer . $buffer;
            $position = $seek;
            if ($chunkBuffer !== '') {
                $remainingLines = max(0, $remainingLines - substr_count($chunkBuffer, "\n"));
            }
        }
        fclose($handle);
        $buffer = rtrim($buffer, "\r\n");
        if ($buffer === '') {
            return '';
        }
        $parts = preg_split('/\r?\n/', $buffer) ?: [];
        $sliceLength = min($targetLines, count($parts));
        if ($sliceLength <= 0) {
            return '';
        }
        return implode("\n", array_slice($parts, -$sliceLength));
    }
}
