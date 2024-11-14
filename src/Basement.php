<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Entities\Time;
use Shimoning\Worktime\Utilities\Round;
use Shimoning\Worktime\Constants\RoundingMethod;

class Basement
{
    /**
     * 差分を Time オブジェクトとして取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @return Time
     */
    static public function diffInTime(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
    ): Time {
        $diffSeconds = self::diffInSeconds($start, $end);
        return Time::fromSeconds($diffSeconds);
    }

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
        $diffSeconds = self::diffInSeconds($start, $end);
        return Round::calculate($diffSeconds / 60, $rounding);
    }

    /**
     * 差分を秒単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @return int|float
     */
    static public function diffInSeconds(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
    ): int|float {
        $start = CarbonImmutable::parse($start);
        $end = CarbonImmutable::parse($end);

        // 終了が開始より前の時はエラー
        if ($end->isBefore($start)) {
            throw new \InvalidArgumentException('The end time must be after the start time.');
        }

        return $end->diffInSeconds($start);
    }
}
