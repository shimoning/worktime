<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Entities\Time;
use Shimoning\Worktime\Utilities\Threshold;
use Shimoning\Worktime\Utilities\Round;
use Shimoning\Worktime\Constants\RoundingMethod;

/**
 * 夜間時間計算
 * (深夜労働となる時間帯のうち、日付が変わるまでの時間)
 *
 * 実装時の法律だと 22-24時 が夜間時間となる (深夜:22-翌5時)
 */
class LateNight
{
    /**
     * 夜間時間を Time オブジェクトとして取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @return Time
     */
    static public function getDiffTime(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
    ): Time {
        $diffSeconds = self::getSeconds($start, $end);
        return Time::fromSeconds($diffSeconds);
    }

    /**
     * 夜間時間を分単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param RoundingMethod|string|callable|null $rounding
     * @param int $hour 夜間が始まる時間 (default: 22時)
     * @return int|float
     */
    static public function getMinutes(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $hour = 22,
    ): int|float {
        $diffSeconds = self::getSeconds($start, $end, $hour);
        return Round::calculate($diffSeconds / 60, $rounding);
    }

    /**
     * 夜間時間を秒単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param int $hour 夜間が始まる時間 (default: 22時)
     * @return int|float
     */
    static public function getSeconds(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        int $hour = 22,
    ): int|float {
        $start = CarbonImmutable::parse($start);
        $end = CarbonImmutable::parse($end);

        // 終了が開始より前の時はエラー
        if ($end->isBefore($start)) {
            throw new \InvalidArgumentException('The end time must be after the start time.');
        }

        // 時間が負の場合はエラー
        if ($hour < 0) {
            throw new \InvalidArgumentException('The hour must be greater than or equal to 0.');
        }
        // 時間が24以上の場合はエラー
        if ($hour >= 24) {
            throw new \InvalidArgumentException('The hour must be less than 24.');
        }

        if ($start->isSameDay($end)) {
            // 日跨ぎなし
            return self::getSecondsInternal($start, $end, $hour);
        } else {
            // 日跨ぎあり
            $days = $end->copy()->startOfDay()->diffInDays($start->copy()->startOfDay());

            $seconds = 0;
            for ($day = 0; $day <= $days; $day++) {
                if ($day === 0) {
                    // 初日
                    $endDay = Threshold::get($start, 24);
                    $seconds += self::getSecondsInternal($start, $endDay, $hour);
                } else if ($day === $days) {
                    // 最終日
                    $startDay = Threshold::get($end, 0);
                    $seconds += self::getSecondsInternal($startDay, $end, $hour);
                } else {
                    // 中間日
                    $seconds += (24 - $hour) * 3600;
                }
            }
            return $seconds;
        }
    }

    /**
     * 日を考慮せずに夜間時間の計算 (秒)
     * $start <= $end が保証されていること
     *
     * @param CarbonInterface $start
     * @param CarbonInterface $end
     * @param int $hour
     * @return int|float
     */
    static private function getSecondsInternal(
        CarbonInterface $start,
        CarbonInterface $end,
        int $hour,
    ): int|float {
        if ($start->isSameDay($end)) {
            // 終了が $hour より前の場合は夜間なし
            if ($end->hour < $hour) {
                return 0;
            }
        } else {
            // 日を跨いでいた場合、終了はその日の24時までとする
            $end = Threshold::get($start, 24);
        }

        // 開始が夜間時間の前だったら、 $hour を開始とする
        if ($start->hour < $hour) {
            $start = Threshold::get($start, $hour);
        }
        return Basement::diffInSeconds($start, $end);
    }
}
