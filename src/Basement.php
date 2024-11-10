<?php

namespace Shimoning\Worktime;

use Carbon\Carbon;
use Shimoning\Worktime\Constants\RoundingMethod;

class Basement
{
    /**
     * 差分を分単位で取得する
     *
     * @param string|int|Carbon $start
     * @param string|int|Carbon $end
     * @param RoundingMethod|string|callable|null $rounding
     * @return int|float
     */
    static public function diffInMinutes(
        string|int|Carbon $start,
        string|int|Carbon $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
    ): int|float {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // 終了が開始より前の時はエラー
        if ($end->isBefore($start)) {
            throw new \InvalidArgumentException('The end time must be after the start time.');
        }

        $diff = $end->diffInSeconds($start) / 60;
        if ($rounding === null) {
            return $diff;
        }
        if (\is_callable($rounding)) {
            return $rounding($diff);
        }
        if (\is_string($rounding)) {
            $rounding = RoundingMethod::tryFrom($rounding);
        }
        return $rounding?->round($diff) ?? $diff;
    }
}
