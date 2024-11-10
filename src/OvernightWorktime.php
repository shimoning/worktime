<?php

namespace Shimoning\Worktime;

use Carbon\Carbon;
use Shimoning\Worktime\Constants\RoundingMethod;

/**
 * 深夜労働時間計算
 * GraveyardShift
 *
 * 実装時の法律だと 22-翌0時 が深夜時間となる
 */
class OvernightWorktime
{
    /**
     * 深夜時間の計算 (分)
     *
     * @param string|int|Carbon $start
     * @param string|int|Carbon $end
     * @param RoundingMethod|string|callable|null $rounding
     * @param int $lateNightStartHour 夜間が始まる時間 (default: 22時)
     * @param int $earlyMorningEndHour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getMinutes(
        string|int|Carbon $start,
        string|int|Carbon $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $lateNightStartHour = 22,
        int $earlyMorningEndHour = 5,
    ): int|float {
        $lateNight = LateNight::getMinutes($start, $end, $rounding, $lateNightStartHour);
        $earlyMorning = EarlyMorning::getMinutes($start, $end, $rounding, $earlyMorningEndHour);
        return $lateNight + $earlyMorning;
    }
}
