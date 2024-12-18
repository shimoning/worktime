<?php

use PHPUnit\Framework\TestCase;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Basement;
use Shimoning\Worktime\Constants\RoundingMethod;

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
        $this->assertSame(0, Basement::diffInSeconds('2024-01-01 09:00:00', '2024-01-01 09:00:00'), '同じ時刻');

        $this->assertSame(3601, Basement::diffInSeconds('2024-01-01 09:00:00', '2024-01-01 10:00:01'), '1時間1秒');
    }

    /**
     * Unix time がパースできることを確認する
     *
     * @return void
     */
    public function test_diffInSeconds_parse_unixtime()
    {
        // 0 = 1970-01-01 00:00:00
        $this->assertSame(0, Basement::diffInSeconds(0, 0), '同じ時刻');
        $this->assertSame(1, Basement::diffInSeconds(0, 1), '1秒');
        $this->assertSame(60, Basement::diffInSeconds(0, 60), '1分');

        // 1704099600 = 2024-01-01 09:00:00
        $this->assertSame(0, Basement::diffInSeconds(1704099600, 1704099600), '同じ時刻');
        $this->assertSame(1, Basement::diffInSeconds(1704099600, 1704099601), '1秒');
        $this->assertSame(60, Basement::diffInSeconds(1704099600, 1704099660), '1分');
    }

    /**
     * Carbon がパースできることを確認する
     *
     * @return void
     */
    public function test_diffInSeconds_parse_carbon()
    {
        $carbon = CarbonImmutable::parse('2024-01-01 09:00:00');
        $this->assertSame(0, Basement::diffInSeconds($carbon, $carbon), '同じ時刻');

        $carbon2 = CarbonImmutable::parse('2024-01-01 10:00:01');
        $this->assertSame(3601, Basement::diffInSeconds($carbon, $carbon2), '1時間1秒');
    }

    /**
     * 差分を Time オブジェクトとして取得することを確認する
     *
     * @return void
     */
    public function test_diffInTime()
    {
        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 09:00:00');
        $this->assertSame(0, $time->getMinutes(), '0分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 09:00:01');
        $this->assertSame(0, $time->getMinutes(), '0分');
        $this->assertSame(1, $time->getSeconds(), '1秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 09:00:01');
        $this->assertSame(0, $time->getMinutes(), '0分');
        $this->assertSame(1, $time->getSeconds(), '1秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 09:00:59');
        $this->assertSame(0, $time->getMinutes(), '0分');
        $this->assertSame(59, $time->getSeconds(), '59秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 09:01:00');
        $this->assertSame(1, $time->getMinutes(), '1分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = Basement::diffInTime('2024-01-01 09:00:01', '2024-01-01 10:00:00');
        $this->assertSame(59, $time->getMinutes(), '59分');
        $this->assertSame(59, $time->getSeconds(), '59秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 10:00:00');
        $this->assertSame(60, $time->getMinutes(), '60分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 10:00:01');
        $this->assertSame(60, $time->getMinutes(), '60分');
        $this->assertSame(1, $time->getSeconds(), '1秒');

        $time = Basement::diffInTime('2024-01-01 09:00:00', '2024-01-01 10:40:01');
        $this->assertSame(100, $time->getMinutes(), '100分');
        $this->assertSame(1, $time->getSeconds(), '1秒');
    }

    /**
     * 秒を四捨五入することを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_round()
    {
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00'), '同じ時刻');
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01'), '1秒');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00'), '1分');

        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29'), '29秒');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30'), '30秒');

        $this->assertSame(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00'), '1時間');
        $this->assertSame(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01'), '1時間1秒');

        $this->assertSame(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00'), '日跨ぎ:16時間');
        $this->assertSame(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01'), '日跨ぎ:16時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00'), '日跨ぎ:24時間');
        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01'), '日跨ぎ:24時間1秒');

        $this->assertSame(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00'), '日跨ぎ:25時間');
        $this->assertSame(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01'), '日跨ぎ:25時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00'), '日跨ぎ:0時:24時間');
        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01'), '日跨ぎ:0時:24時間1秒');
    }

    /**
     * 秒を切り上げるすることを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_ceil()
    {
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00', RoundingMethod::CEIL), '同じ時刻');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01', RoundingMethod::CEIL), '1秒');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00', RoundingMethod::CEIL), '1分');

        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29', RoundingMethod::CEIL), '29秒');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30', RoundingMethod::CEIL), '30秒');

        $this->assertSame(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00', RoundingMethod::CEIL), '1時間');
        $this->assertSame(61, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01', RoundingMethod::CEIL), '1時間1秒');

        $this->assertSame(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00', RoundingMethod::CEIL), '日跨ぎ:16時間');
        $this->assertSame(961, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01', RoundingMethod::CEIL), '日跨ぎ:16時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00', RoundingMethod::CEIL), '日跨ぎ:24時間');
        $this->assertSame(1441, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01', RoundingMethod::CEIL), '日跨ぎ:24時間1秒');

        $this->assertSame(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00', RoundingMethod::CEIL), '日跨ぎ:25時間');
        $this->assertSame(1501, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01', RoundingMethod::CEIL), '日跨ぎ:25時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '日跨ぎ:0時:24時間');
        $this->assertSame(1441, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01', RoundingMethod::CEIL), '日跨ぎ:0時:24時間1秒');
    }

    /**
     * 秒が切り捨てられることを確認する
     *
     * @return void
     */
    public function test_diffInMinutes_floor()
    {
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:00', RoundingMethod::FLOOR), '同じ時刻');
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:01', RoundingMethod::FLOOR), '1秒');
        $this->assertSame(1, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:01:00', RoundingMethod::FLOOR), '1分');

        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:29', RoundingMethod::FLOOR), '29秒');
        $this->assertSame(0, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 09:00:30', RoundingMethod::FLOOR), '30秒');

        $this->assertSame(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:00', RoundingMethod::FLOOR), '1時間');
        $this->assertSame(60, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-01 10:00:01', RoundingMethod::FLOOR), '1時間1秒');

        $this->assertSame(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '日跨ぎ:16時間');
        $this->assertSame(960, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 01:00:01', RoundingMethod::FLOOR), '日跨ぎ:16時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:00', RoundingMethod::FLOOR), '日跨ぎ:24時間');
        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 09:00:01', RoundingMethod::FLOOR), '日跨ぎ:24時間1秒');

        $this->assertSame(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:00', RoundingMethod::FLOOR), '日跨ぎ:25時間');
        $this->assertSame(1500, Basement::diffInMinutes('2024-01-01 09:00:00', '2024-01-02 10:00:01', RoundingMethod::FLOOR), '日跨ぎ:25時間1秒');

        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '日跨ぎ:0時:24時間');
        $this->assertSame(1440, Basement::diffInMinutes('2024-01-01 00:00:00', '2024-01-02 00:00:01', RoundingMethod::FLOOR), '日跨ぎ:0時:24時間1秒');
    }
}
