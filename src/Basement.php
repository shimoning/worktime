<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Constants\RoundingMethod;

class Basement
{
    /**
     * 差分を分単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param RoundingMethod|string|callable|null $rounding
     * @return int|float
     */
    static public function diffInMinutes(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
    ): int|float {
        $start = CarbonImmutable::parse($start);
        $end = CarbonImmutable::parse($end);

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

    /**
     * 閾いとなる時間を取得する
     *
     * @param string|int|CarbonInterface $datetime
     * @param int $hour
     * @return CarbonInterface
     */
    static public function getThreshold(string|int|CarbonInterface $datetime, int $hour): CarbonInterface
    {
        return CarbonImmutable::parse($datetime)
            ->setHour($hour)
            ->setMinute(0)
            ->setSecond(0)
            ->setMilliseconds(0)
            ->setMicroseconds(0);
    }
}
