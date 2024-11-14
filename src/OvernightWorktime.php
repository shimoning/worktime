<?php

namespace Shimoning\Worktime;

use Carbon\CarbonInterface;
use Shimoning\Worktime\Entities\Time;
use Shimoning\Worktime\Utilities\Round;
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
     * 深夜時間を Time オブジェクトとして取得する
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
     * 深夜時間を分単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param RoundingMethod|string|callable|null $rounding
     * @param int $lateNightStartHour 夜間が始まる時間 (default: 22時)
     * @param int $earlyMorningEndHour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getMinutes(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        RoundingMethod|string|callable|null $rounding = RoundingMethod::ROUND,
        int $lateNightStartHour = 22,
        int $earlyMorningEndHour = 5,
    ): int|float {
        $diffSeconds = self::getSeconds($start, $end, $lateNightStartHour, $earlyMorningEndHour);
        return Round::calculate($diffSeconds / 60, $rounding);
    }

    /**
     * 深夜時間を秒単位で取得する
     *
     * @param string|int|CarbonInterface $start
     * @param string|int|CarbonInterface $end
     * @param int $lateNightStartHour 夜間が始まる時間 (default: 22時)
     * @param int $earlyMorningEndHour 早朝が終わる時間 (default: 5時)
     * @return int|float
     */
    static public function getSeconds(
        string|int|CarbonInterface $start,
        string|int|CarbonInterface $end,
        int $lateNightStartHour = 22,
        int $earlyMorningEndHour = 5,
    ): int|float {
        $lateNight = LateNight::getSeconds($start, $end, $lateNightStartHour);
        $earlyMorning = EarlyMorning::getSeconds($start, $end, $earlyMorningEndHour);
        return $lateNight + $earlyMorning;
    }
}
