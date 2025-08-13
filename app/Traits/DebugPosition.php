<?php

namespace App\Traits;

trait DebugPosition
{
    public function getDebugPosition(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($trace as $frame) {
            if (
                isset($frame['class']) &&
                str_starts_with($frame['class'], 'App\\') &&
                ! str_starts_with($frame['class'], 'App\\Exceptions')
            ) {
                return $frame['class'].'|'.($frame['function']).'|'.($frame['line'] ?? 'unknown').':';
            }
        }

        return 'unknown location';
    }
}
