<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Constants\RoundingMethod;

/**
 * 早朝時間の計算
 * (深夜労働となる時間帯のうち、日付が変わった後の時間)
 *
 * 実装時の法律だと 0-5時 が早朝時間となる (深夜:22-翌5時)
 * 1日までの日跨ぎを考慮する (2日以上はエラー)
 */
class EarlyMorning
{
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
            return self::getMinutesInternal($start, $end, $rounding, $hour);
        } else {
            // 日跨ぎあり
            $days = $end->copy()->startOfDay()->diffInDays($start->copy()->startOfDay());

            $minutes = 0;
            for ($day = 0; $day <= $days; $day++) {
                if ($day === 0) {
                    // 初日
                    $endDay = Basement::getThreshold($start, 24);
                    $minutes += self::getMinutesInternal($start, $endDay, $rounding, $hour);
                } else if ($day === $days) {
                    // 最終日
                    $startDay = Basement::getThreshold($end, 0);
                    $minutes += self::getMinutesInternal($startDay, $end, $rounding, $hour);
                } else {
                    // 中間日
                    $minutes += $hour * 60;
                }
            }
            return $minutes;
        }
    }

    /**
     * 日を考慮せずに早朝時間の計算 (分)
     * $start <= $end が保証されていること
     *
     * @param CarbonInterface $start
     * @param CarbonInterface $end
     * @param RoundingMethod $rounding
     * @param integer $hour
     * @return integer|float
     */
    static private function getMinutesInternal(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $hour = 5,
    ): int|float {
        // 開始が $hour を過ぎていれば早朝なし
        if ($start->hour >= $hour) {
            return 0;
        }

        if (!$start->isSameDay($end)) {
            // 日を跨いでいた場合、終了はその日の $hour までとする
            $end = Basement::getThreshold($start, $hour);
        }

        // 終了が過ぎていたら $hour を終了とする
        if ($end->hour >= $hour) {
            $end = Basement::getThreshold($end, $hour);
        }

        return Basement::diffInMinutes($start, $end, $rounding);
    }
}
