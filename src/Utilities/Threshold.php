<?php

namespace Shimoning\Worktime\Utilities;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;

class Threshold
{
    /**
     * 閾いとなる時間を取得する
     *
     * @param string|int|CarbonInterface $datetime
     * @param int $hour
     * @return CarbonInterface
     */
    static public function get(string|int|CarbonInterface $datetime, int $hour): CarbonInterface
    {
        return CarbonImmutable::parse($datetime)
            ->setHour($hour)
            ->setMinute(0)
            ->setSecond(0)
            ->setMilliseconds(0)
            ->setMicroseconds(0);
    }
}
