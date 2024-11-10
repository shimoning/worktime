<?php

namespace Shimoning\Worktime\Utilities;

use Shimoning\Worktime\Constants\RoundingMethod;

class Round
{
    /**
     * 端数処理
     *
     * @param int|float $value
     * @param RoundingMethod|string|callable|null $rounding
     * @return int|float
     */
    static public function calculate(
        int|float $value,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
    ): int|float {
        if ($rounding === null) {
            return $value;
        }
        if (\is_callable($rounding)) {
            return $rounding($value);
        }
        if (\is_string($rounding)) {
            $rounding = RoundingMethod::tryFrom($rounding) ?? RoundingMethod::ROUND;
        }
        return $rounding->round($value) ?? $value;
    }
}
