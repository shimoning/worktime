<?php

use PHPUnit\Framework\TestCase;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\LateNight;
use Shimoning\Worktime\Constants\RoundingMethod;

class LateNightTest extends TestCase
{
    /**
     * 開始より過去の終了時間を指定してエラーが出ることを確認する
     *
     * @return void
     */
    public function test_exception_before()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The end time must be after the start time.');

        LateNight::getMinutes('2024-01-02 03:00:00', '2024-01-02 08:00:00');
        LateNight::getMinutes('2024-01-03 03:00:00', '2024-01-02 03:00:00');
        LateNight::getMinutes('2024-01-03 00:00:00', '2024-01-02 00:00:00');
    }

    /**
     * 負の時間を指定してエラーが出ることを確認する
     *
     * @return void
     */
    public function test_exception_negative_hour()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The hour must be greater than or equal to 0.');

        LateNight::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -1);
        LateNight::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -999);
    }

    /**
     * 24以上の時間を指定してエラーが出ることを確認する
     *
     * @return void
     */
    public function test_exception_over_24hours()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The hour must be less than 24.');

        LateNight::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 24);
        LateNight::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 999);
    }

    /**
     * Unix time がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_unixtime()
    {
        // 0 = 1970-01-01 00:00:00
        $this->assertSame(0, LateNight::getMinutes(0, 0), '同じ時刻');
        $this->assertSame(0, LateNight::getMinutes(0, 79201), '1秒');
        $this->assertSame(1, LateNight::getMinutes(0, 79260), '1分');

        // 1704146400 = 2024-01-01 22:00:00
        $this->assertSame(0, LateNight::getMinutes(1704146400, 1704146400), '同じ時刻');
        $this->assertSame(0, LateNight::getMinutes(1704146400, 1704146401), '1秒');
        $this->assertSame(1, LateNight::getMinutes(1704146400, 1704146460), '1分');
    }

    /**
     * Carbon がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_carbon()
    {
        $carbon = CarbonImmutable::parse('2024-01-01 22:00:00');
        $this->assertSame(0, LateNight::getMinutes($carbon, $carbon), '同じ時刻');

        $carbon2 = CarbonImmutable::parse('2024-01-01 23:00:01');
        $this->assertSame(60, LateNight::getMinutes($carbon, $carbon2), '1時間1秒');
    }

    /**
     * 差分を Time オブジェクトとして取得することを確認する
     *
     * @return void
     */
    public function test_getTime()
    {
        $time = LateNight::getTime('2024-01-01 23:00:00', '2024-01-01 23:00:00');
        $this->assertSame(0, $time->getMinutes(), '0分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = LateNight::getTime('2024-01-01 23:00:00', '2024-01-01 23:30:00');
        $this->assertSame(30, $time->getMinutes(), '30分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = LateNight::getTime('2024-01-01 22:00:00', '2024-01-02 00:00:00');
        $this->assertSame(120, $time->getMinutes(), '120分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = LateNight::getTime('2024-01-01 21:59:01', '2024-01-02 00:00:00');
        $this->assertSame(120, $time->getMinutes(), '120分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = LateNight::getTime('2024-01-01 22:00:00', '2024-01-02 00:00:01');
        $this->assertSame(120, $time->getMinutes(), '120分');
        $this->assertSame(0, $time->getSeconds(), '0秒');

        $time = LateNight::getTime('2024-01-01 22:00:00', '2024-01-02 00:00:01', 23);
        $this->assertSame(60, $time->getMinutes(), '60分');
        $this->assertSame(0, $time->getSeconds(), '0秒');
    }

    /**
     * 秒を四捨五入することを確認する
     *
     * @return void
     */
    public function test_round()
    {
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 06:00:00', '2024-01-01 21:00:00'), '範囲外');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 19:00:00', '2024-01-01 22:00:00'), '範囲外~夜間開始丁度');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:01'), '範囲外夜間開始後1秒');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:00'), '同じ時間');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:00'), '開始時刻');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:01'), '1秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:01:00'), '1分');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:29'), '29秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:30'), '30秒');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:00'), '1時間');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:01'), '1時間1秒');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00'), '夜間開始終了丁度');
        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 01:00:00'), '夜間開始終了跨ぎ');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 00:00:00'), '夜間開始前終了丁度');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:00'), '夜間開始後終了跨ぎ');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 23:00:00'), '夜間開始前終了前');

        $this->assertSame(180, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 23:00:00'), '2連続夜間');
        $this->assertSame(300, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-03 23:00:00'), '3連続夜間');
    }

    /**
     * 秒を切り上げるすることを確認する
     *
     * @return void
     */
    public function test_ceil()
    {
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 06:00:00', '2024-01-01 21:00:00', RoundingMethod::CEIL), '範囲外');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 19:00:00', '2024-01-01 22:00:00', RoundingMethod::CEIL), '範囲外~夜間開始丁度');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:01', RoundingMethod::CEIL), '範囲外夜間開始後1秒');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:00', RoundingMethod::CEIL), '同じ時間');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:00', RoundingMethod::CEIL), '開始時刻');

        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:01', RoundingMethod::CEIL), '1秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:01:00', RoundingMethod::CEIL), '1分');

        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:29', RoundingMethod::CEIL), '29秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:30', RoundingMethod::CEIL), '30秒');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:00', RoundingMethod::CEIL), '1時間');
        $this->assertSame(61, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:01', RoundingMethod::CEIL), '1時間1秒');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '夜間開始終了丁度');
        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 01:00:00', RoundingMethod::CEIL), '夜間開始終了跨ぎ');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '夜間開始前終了丁度');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:00', RoundingMethod::CEIL), '夜間開始後終了跨ぎ');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 23:00:00', RoundingMethod::CEIL), '夜間開始前終了前');

        $this->assertSame(180, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 23:00:00', RoundingMethod::CEIL), '2連続夜間');
        $this->assertSame(300, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-03 23:00:00', RoundingMethod::CEIL), '3連続夜間');
    }

    /**
     * 秒が切り捨てられることを確認する
     *
     * @return void
     */
    public function test_floor()
    {
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 06:00:00', '2024-01-01 21:00:00', RoundingMethod::FLOOR), '範囲外');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 19:00:00', '2024-01-01 22:00:00', RoundingMethod::FLOOR), '範囲外~夜間開始丁度');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:01', RoundingMethod::FLOOR), '範囲外夜間開始後1秒');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:00', RoundingMethod::FLOOR), '同じ時間');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 22:00:00', RoundingMethod::FLOOR), '開始時刻');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:01', RoundingMethod::FLOOR), '1秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:01:00', RoundingMethod::FLOOR), '1分');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:29', RoundingMethod::FLOOR), '29秒');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:30', RoundingMethod::FLOOR), '30秒');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:00', RoundingMethod::FLOOR), '1時間');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:00:01', RoundingMethod::FLOOR), '1時間1秒');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '夜間開始終了丁度');
        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '夜間開始終了跨ぎ');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '夜間開始前終了丁度');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '夜間開始後終了跨ぎ');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 23:00:00', RoundingMethod::FLOOR), '夜間開始前終了前');

        $this->assertSame(180, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 23:00:00', RoundingMethod::FLOOR), '2連続夜間');
        $this->assertSame(300, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-03 23:00:00', RoundingMethod::FLOOR), '3連続夜間');
    }

    /**
     * 時間を変更して計算する
     *
     * @return void
     */
    public function test_hour()
    {
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 06:00:00', '2024-01-01 21:00:00', RoundingMethod::ROUND, 21), '範囲外: -1');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 19:00:00', '2024-01-01 22:00:00', RoundingMethod::ROUND, 23), '範囲外: +1');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 19:00:00', '2024-01-01 21:00:01', RoundingMethod::ROUND, 21), '範囲外~深夜開始丁度');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:00', RoundingMethod::ROUND, 21), '同じ時間');
        $this->assertSame(0, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 21:00:00', RoundingMethod::ROUND, 21), '開始時刻');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:01', RoundingMethod::ROUND, 21), '1秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:01:00', RoundingMethod::ROUND, 21), '1分');

        $this->assertSame(0, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:29', RoundingMethod::ROUND, 21), '29秒');
        $this->assertSame(1, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:30', RoundingMethod::ROUND, 21), '30秒');

        $this->assertSame(60, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 22:00:00', RoundingMethod::ROUND, 21), '1時間');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-01 22:00:01', RoundingMethod::ROUND, 21), '1時間1秒');

        $this->assertSame(180, LateNight::getMinutes('2024-01-01 21:00:00', '2024-01-02 00:00:00', RoundingMethod::ROUND, 21), '夜間開始終了丁度');
        $this->assertSame(180, LateNight::getMinutes('2024-01-01 20:00:00', '2024-01-02 01:00:00', RoundingMethod::ROUND, 21), '夜間開始終了跨ぎ');

        $this->assertSame(180, LateNight::getMinutes('2024-01-01 20:00:00', '2024-01-02 00:00:00', RoundingMethod::ROUND, 21), '夜間開始前終了丁度');
        $this->assertSame(60, LateNight::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:00', RoundingMethod::ROUND, 21), '夜間開始後終了跨ぎ');

        $this->assertSame(120, LateNight::getMinutes('2024-01-01 20:00:00', '2024-01-01 23:00:00', RoundingMethod::ROUND, 21), '夜間開始前終了前');

        $this->assertSame(300, LateNight::getMinutes('2024-01-01 20:00:00', '2024-01-02 23:00:00', RoundingMethod::ROUND, 21), '2連続夜間');
        $this->assertSame(480, LateNight::getMinutes('2024-01-01 20:00:00', '2024-01-03 23:00:00', RoundingMethod::ROUND, 21), '3連続夜間');
    }
}
