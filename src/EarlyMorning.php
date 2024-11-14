<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Entities\Time;
use Shimoning\Worktime\Utilities\Threshold;
use Shimoning\Worktime\Utilities\Round;
use Shimoning\Worktime\Constants\RoundingMethod;

/**
 * 早朝時間の計算
 * (深夜労働となる時間帯のうち、日付が変わった後の時間)
 *
 * 実装時の法律だと 0-5時 が早朝時間となる (深夜:22-翌5時)
 */
class EarlyMorning
{
    /**
     * 早朝時間を Time オブジェクトとして取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @return Time
     */
    static public function getTime(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
    ): Time {
        $diffSeconds = self::getSeconds($start, $end);
        return Time::fromSeconds($diffSeconds);
    }

    /**
     * 早朝時間を分単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param RoundingMethod|string|callable|null $rounding
     * @param int $hour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getMinutes(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $hour = 5,
    ): int|float {
        $diffSeconds = self::getSeconds($start, $end, $hour);
        return Round::calculate($diffSeconds / 60, $rounding);
    }

    /**
     * 早朝時間を秒単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param int $hour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getSeconds(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        int $hour = 5,
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
                    $seconds += $hour * 3600;
                }
            }
            return $seconds;
        }
    }

    /**
     * 日を考慮せずに早朝時間の計算 (秒)
     * $start <= $end が保証されていること
     *
     * @param CarbonInterface $start
     * @param CarbonInterface $end
     * @param RoundingMethod $rounding
     * @param int $hour
     * @return int|float
     */
    static private function getSecondsInternal(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        int $hour = 5,
    ): int|float {
        // 開始が $hour を過ぎていれば早朝なし
        if ($start->hour >= $hour) {
            return 0;
        }

        if (! $start->isSameDay($end)) {
            // 日を跨いでいた場合、終了はその日の $hour までとする
            $end = Threshold::get($start, $hour);
        }

        // 終了が過ぎていたら $hour を終了とする
        if ($end->hour >= $hour) {
            $end = Threshold::get($end, $hour);
        }

        return Basement::diffInSeconds($start, $end);
    }
}
