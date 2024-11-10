<?php

namespace Shimoning\Worktime\Entities;

final class Time
{
    public function __construct(
        private int $minutes,
        private int $seconds = 0,
    ) {
        if ($minutes < 0) {
            throw new \InvalidArgumentException('The minutes must be greater than or equal to 0.');
        }
        if ($seconds < 0) {
            throw new \InvalidArgumentException('The seconds must be greater than or equal to 0.');
        }
        if ($seconds >= 60) {
            throw new \InvalidArgumentException('The seconds must be less than 60.');
        }
    }

    /**
     * 分を取得
     *
     * @return int
     */
    public function getMinutes(): int
    {
        return $this->minutes;
    }

    /**
     * 秒を取得
     *
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * 分として取得
     *
     * @return float
     */
    public function toMinutes(): float
    {
        return $this->minutes + $this->seconds / 60;
    }

    /**
     * 秒として取得
     *
     * @return int
     */
    public function toSeconds(): int
    {
        return $this->minutes * 60 + $this->seconds;
    }

    /**
     * 秒を加味して分を取得 (切り上げ)
     *
     * @return int
     */
    public function getCeiled(): int
    {
        return (int)ceil($this->toMinutes());
    }

    /**
     * 秒を加味して分を取得 (切り捨て)
     *
     * @return int
     */
    public function getFloored(): int
    {
        return (int)floor($this->toMinutes());
    }

    /**
     * 秒を加味して分を取得 (四捨五入)
     *
     * @param int $mode (PHP_ROUND_HALF_UP | PHP_ROUND_HALF_DOWN | PHP_ROUND_HALF_EVEN | PHP_ROUND_HALF_ODD)    default: PHP_ROUND_HALF_UP
     * @return int
     */
    public function getRounded(int $mode = \PHP_ROUND_HALF_UP): int
    {
        return (int)round($this->toMinutes(), 0, $mode);
    }
}
