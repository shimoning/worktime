<?php

namespace Shimoning\Worktime;

use Carbon\Carbon;
use Shimoning\Worktime\Constants\RoundingMethod;

/**
 * 早朝時間の計算
 *
 * 実装時の法律だと 0-5時 が早朝時間となる (深夜:22-翌5時)
 * 1日までの日跨ぎを考慮する (2日以上はエラー)
 */
class EarlyMorning
{
    /**
     * 早朝時間を分単位で取得する
     *
     * @param string|int|Carbon $start
     * @param string|int|Carbon $end
     * @param RoundingMethod|string|callable|null $rounding
     * @param int $hour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getMinutes(
        string|int|Carbon $start,
        string|int|Carbon $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $hour = 5,
    ): int|float {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // 終了が開始より前の時はエラー
        if ($end->isBefore($start)) {
            throw new \InvalidArgumentException('The end time must be after the start time.');
        }

        // 終了が開始より2日以上だったらエラー
        if ($end->diffInDays($start) > 1) {
            throw new \InvalidArgumentException('The end time must be within 1 day from the start time.');
        }

        // 時間が負の場合はエラー
        if ($hour < 0) {
            throw new \InvalidArgumentException('The hour must be greater than or equal to 0.');
        }

        // 日跨ぎなし
        if ($start->isSameDay($end)) {
            // 開始が過ぎている : $hour < $start <= $end
            if ($start->hour >= $hour) {
                return 0;
            }

            // 終了が過ぎている : 0 < $start < $hour < $end
            if ($end->hour >= $hour) {
                $threshold = Basement::getThreshold($end, $hour);
                return Basement::diffInMinutes($start, $threshold, $rounding);
            }

            // 開始も終了も早朝時間帯の中 : 0 < $start < $end < $hour
            return Basement::diffInMinutes($start, $end, $rounding);
        }

        // 日跨ぎあり
        else {
            // 開始が過ぎている: $start < 24 = 0 < $end
            if ($start->hour >= $hour) {
                // 終了が過ぎている = $hour までの時間がそのまま早朝
                if ($end->hour >= $hour) {
                    return $hour * 60;
                }

                $midnight = Basement::getThreshold($end, 0);
                return Basement::diffInMinutes($midnight, $end, $rounding);
            }

            // 開始の時点で早朝時間がある
            $threshold = Basement::getThreshold($start, $hour);
            $startDiff = Basement::diffInMinutes($start, $threshold, $rounding);
            $endDiff = 0;

            // 終了時間が過ぎているかどうか
            if ($end->hour >= $hour) {
                // 過ぎている
                $endDiff = $hour * 60;
            } else {
                // 過ぎていない
                $midnight = Basement::getThreshold($end, 0);
                $endDiff = Basement::diffInMinutes($midnight, $end, $rounding);
            }

            return $startDiff + $endDiff;
        }
    }
}
