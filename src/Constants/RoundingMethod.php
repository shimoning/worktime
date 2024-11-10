<?php

namespace Shimoning\Worktime\Constants;

enum RoundingMethod: string
{
    case RAW    = 'raw';    // そのまま
    case ROUND  = 'round';  // 四捨五入
    case CEIL   = 'ceil';   // 切り上げ
    case FLOOR  = 'floor';  // 切り捨て

    /**
     * 端数処理
     *
     * @param int|float $value
     * @return int|float
     */
    public function round(int|float $value): int|float
    {
        return match ($this) {
            self::RAW   => $value,
            self::ROUND => (int)round($value),
            self::CEIL  => (int)ceil($value),
            self::FLOOR => (int)floor($value),
        };
    }
}
