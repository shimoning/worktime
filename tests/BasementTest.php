<?php

use PHPUnit\Framework\TestCase;
use Shimoning\Worktime\Basement;
use Shimoning\Worktime\Constants\RoundingMethod;
use Carbon\CarbonImmutable;

class BasementTest extends TestCase
{
    /**
     * 開始より過去の終了時間を指定してエラーが出ることを確認する
     *
     * @return void
     */
    public function test_diffInSeconds_exception_before()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The end time must be after the start time.');

        Basement::diffInSeconds('2024-01-02 09:00:00', '2024-01-02 08:00:00');
        Basement::diffInSeconds('2024-01-03 09:00:00', '2024-01-02 09:00:00');
        Basement::diffInSeconds('2024-01-03 00:00:00', '2024-01-02 00:00:00');
    }

    /**
     * 文字列 がパースできることを確認する
     * TODO: 他のフォーマットの時間もテストする
     *
     * @return void
     */
    public function test_diffInSeconds_parse_string()
    {
        $this->assertEquals(0, Basement::diffInSeconds('2024-01-01 09:00:00', '2024-01-01 09:00:00'), '同じ時刻');

        $this->assertEquals(3601, Basement::diffInSeconds('2024-01-01 09:00:00', '2024-01-01 10:00:01'), '1時間1秒');
    }

    /**
     * Unix time がパースできることを確認する
     *
     * @return void
     */
    public function test_diffInSeconds_parse_unixtime()
    {
        // 0 = 1970-01-01 00:00:00
        $this->assertEquals(0, Basement::diffInSeconds(0, 0), '同じ時刻');
        $this->assertEquals(1, Basement::diffInSeconds(0, 1), '1秒');
        $this->assertEquals(60, Basement::diffInSeconds(0, 60), '1分');

        // 1704099600 = 2024-01-01 09:00:00
        $this->assertEquals(0, Basement::diffInSeconds(1704099600, 1704099600), '同じ時刻');
        $this->assertEquals(1, Basement::diffInSeconds(1704099600, 1704099601), '1秒');
        $this->assertEquals(60, Basement::diffInSeconds(1704099600, 1704099660), '1分');
    }

    /**
     * CarbonImmutable がパースできることを確認する
     *
     * @return void
     */
    public function test_diffInSeconds_parse_CarbonImmutable()
    {
        $CarbonImmutable = CarbonImmutable::parse('2024-01-01 09:00:00');
        $this->assertEquals(0, Basement::diffInSeconds($CarbonImmutable, $CarbonImmutable), '同じ時刻');

        $CarbonImmutable2 = CarbonImmutable::parse('2024-01-01 10:00:01');
        $this->assertEquals(3601, Basement::diffInSeconds($CarbonImmutable, $CarbonImmutable2), '1時間1秒');
    }

    /**
     * 秒を四捨五入することを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_round()
    {
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00'), '同じ時刻');
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01'), '1秒');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00'), '1分');

        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29'), '29秒');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30'), '30秒');

        $this->assertEquals(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00'), '1時間');
        $this->assertEquals(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01'), '1時間1秒');

        $this->assertEquals(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00'), '日跨ぎ:16時間');
        $this->assertEquals(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01'), '日跨ぎ:16時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00'), '日跨ぎ:24時間');
        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01'), '日跨ぎ:24時間1秒');

        $this->assertEquals(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00'), '日跨ぎ:25時間');
        $this->assertEquals(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01'), '日跨ぎ:25時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00'), '日跨ぎ:0時:24時間');
        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01'), '日跨ぎ:0時:24時間1秒');
    }

    /**
     * 秒を切り上げるすることを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_ceil()
    {
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00', RoundingMethod::CEIL), '同じ時刻');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01', RoundingMethod::CEIL), '1秒');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00', RoundingMethod::CEIL), '1分');

        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29', RoundingMethod::CEIL), '29秒');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30', RoundingMethod::CEIL), '30秒');

        $this->assertEquals(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00', RoundingMethod::CEIL), '1時間');
        $this->assertEquals(61, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01', RoundingMethod::CEIL), '1時間1秒');

        $this->assertEquals(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00', RoundingMethod::CEIL), '日跨ぎ:16時間');
        $this->assertEquals(961, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01', RoundingMethod::CEIL), '日跨ぎ:16時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00', RoundingMethod::CEIL), '日跨ぎ:24時間');
        $this->assertEquals(1441, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01', RoundingMethod::CEIL), '日跨ぎ:24時間1秒');

        $this->assertEquals(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00', RoundingMethod::CEIL), '日跨ぎ:25時間');
        $this->assertEquals(1501, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01', RoundingMethod::CEIL), '日跨ぎ:25時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '日跨ぎ:0時:24時間');
        $this->assertEquals(1441, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01', RoundingMethod::CEIL), '日跨ぎ:0時:24時間1秒');
    }

    /**
     * 秒が切り捨てられることを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_floor()
    {
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00', RoundingMethod::FLOOR), '同じ時刻');
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01', RoundingMethod::FLOOR), '1秒');
        $this->assertEquals(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00', RoundingMethod::FLOOR), '1分');

        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29', RoundingMethod::FLOOR), '29秒');
        $this->assertEquals(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30', RoundingMethod::FLOOR), '30秒');

        $this->assertEquals(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00', RoundingMethod::FLOOR), '1時間');
        $this->assertEquals(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01', RoundingMethod::FLOOR), '1時間1秒');

        $this->assertEquals(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '日跨ぎ:16時間');
        $this->assertEquals(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01', RoundingMethod::FLOOR), '日跨ぎ:16時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00', RoundingMethod::FLOOR), '日跨ぎ:24時間');
        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01', RoundingMethod::FLOOR), '日跨ぎ:24時間1秒');

        $this->assertEquals(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00', RoundingMethod::FLOOR), '日跨ぎ:25時間');
        $this->assertEquals(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01', RoundingMethod::FLOOR), '日跨ぎ:25時間1秒');

        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '日跨ぎ:0時:24時間');
        $this->assertEquals(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01', RoundingMethod::FLOOR), '日跨ぎ:0時:24時間1秒');
    }
}
