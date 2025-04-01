<?php

namespace App\Enums;

enum MessageTypeEnum
{
    case ERROR;
    case WARNING;
    case INFO;
    case SUCCESS;

    public function getClassColor(): string
    {
        return match ($this) {
            self::ERROR => 'bg-red-500',
            self::WARNING => 'bg-yellow-500',
            self::INFO => 'bg-blue-500',
            self::SUCCESS => 'bg-green-500',
        };
    }
}
