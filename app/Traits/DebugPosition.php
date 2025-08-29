<?php

namespace App\Traits;

trait DebugPosition
{
    public function getDebugPosition(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        $frame1 = $trace[1] ?? null;
        $frame2 = $trace[2] ?? null;

        if ($frame1 && isset($frame1['class']) && $frame2 && isset($frame2['class'])) {
            return $frame2['class'].'|'.($frame2['function'] ?? 'unknown').'|'.($frame1['line'] ?? 'unknown').':';
        }

        return 'unknown location';
    }
}
