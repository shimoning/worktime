<?php

use PHPUnit\Framework\TestCase;
use Shimoning\Worktime\OvernightWorktime;
use Shimoning\Worktime\Constants\RoundingMethod;
use Carbon\CarbonImmutable;

class OvernightWorktimeTest extends TestCase
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

        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 08:00:00');
        OvernightWorktime::getMinutes('2024-01-03 03:00:00', '2024-01-02 03:00:00');
        OvernightWorktime::getMinutes('2024-01-03 00:00:00', '2024-01-02 00:00:00');
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

        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -1);
        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -999);

        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 0, -1);
        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 0, -999);
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

        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 24);
        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 999);
        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 0, 24);
        OvernightWorktime::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 0, 999);
    }

    /**
     * Unix time がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_unixtime()
    {
        // 0 = 1970-01-01 00:00:00
        $this->assertEquals(0, OvernightWorktime::getMinutes(0, 0), '1970-01-01 00:00:00');

        // 1704074400 = 2024-01-01 02:00:00
        $this->assertEquals(0, OvernightWorktime::getMinutes(1704074400, 1704074400), '2024-01-01 02:00:00');

        // 1704146400 = 2024-01-01 22:00:00
        $this->assertEquals(0, OvernightWorktime::getMinutes(1704146400, 1704146400), '2024-01-01 22:00:00');
    }

    /**
     * CarbonImmutable がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_CarbonImmutable()
    {
        $CarbonImmutable = CarbonImmutable::parse('2024-01-01 02:00:00');
        $this->assertEquals(0, OvernightWorktime::getMinutes($CarbonImmutable, $CarbonImmutable), '同じ時刻');

        $CarbonImmutable = CarbonImmutable::parse('2024-01-01 22:00:00');
        $this->assertEquals(0, OvernightWorktime::getMinutes($CarbonImmutable, $CarbonImmutable), '同じ時刻');
    }

    /**
     * 秒を四捨五入することを確認する
     *
     * @return void
     */
    public function test_round()
    {

        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:01'), '1秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:29'), '29秒');
        $this->assertEquals(121, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:30'), '30秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:01', '2024-01-02 01:00:00'), '-1秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:29', '2024-01-02 01:00:00'), '-29秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:30', '2024-01-02 01:00:00'), '-30秒');
        $this->assertEquals(119, OvernightWorktime::getMinutes('2024-01-01 23:00:31', '2024-01-02 01:00:00'), '-31秒');
    }

    /**
     * 秒を切り上げるすることを確認する
     *
     * @return void
     */
    public function test_ceil()
    {
        $this->assertEquals(121, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:01', RoundingMethod::CEIL), '1秒');
        $this->assertEquals(121, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:29', RoundingMethod::CEIL), '29秒');
        $this->assertEquals(121, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:30', RoundingMethod::CEIL), '30秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:01', '2024-01-02 01:00:00', RoundingMethod::CEIL), '-1秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:29', '2024-01-02 01:00:00', RoundingMethod::CEIL), '-29秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:30', '2024-01-02 01:00:00', RoundingMethod::CEIL), '-30秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:31', '2024-01-02 01:00:00', RoundingMethod::CEIL), '-31秒');
    }

    /**
     * 秒が切り捨てられることを確認する
     *
     * @return void
     */
    public function test_floor()
    {
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:01', RoundingMethod::FLOOR), '1秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:29', RoundingMethod::FLOOR), '29秒');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 01:00:30', RoundingMethod::FLOOR), '30秒');
        $this->assertEquals(119, OvernightWorktime::getMinutes('2024-01-01 23:00:01', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '-1秒');
        $this->assertEquals(119, OvernightWorktime::getMinutes('2024-01-01 23:00:29', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '-29秒');
        $this->assertEquals(119, OvernightWorktime::getMinutes('2024-01-01 23:00:30', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '-30秒');
        $this->assertEquals(119, OvernightWorktime::getMinutes('2024-01-01 23:00:31', '2024-01-02 01:00:00', RoundingMethod::FLOOR), '-31秒');
    }

    /**
     * 日中のみ
     *
     * @return void
     */
    public function test_dayWork()
    {
        $this->assertEquals(0, OvernightWorktime::getMinutes('2024-01-01 09:00:00', '2024-01-01 18:00:00'), '標準 (9-18時)');
        $this->assertEquals(0, OvernightWorktime::getMinutes('2024-01-01 05:00:00', '2024-01-01 14:00:00'), '早朝終了直後 (5-14時)');
        $this->assertEquals(0, OvernightWorktime::getMinutes('2024-01-01 13:00:00', '2024-01-01 22:00:00'), '夜間開始直前 (13-22)');

    }

    /**
     * 早朝のみ
     *
     * @return void
     */
    public function test_earlyMorning()
    {
        $this->assertEquals(0, OvernightWorktime::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:00'), '同一時刻');
        $this->assertEquals(61, OvernightWorktime::getMinutes('2024-01-01 00:00:00', '2024-01-01 01:01:01'), '開始丁度');
        $this->assertEquals(59, OvernightWorktime::getMinutes('2024-01-01 04:01:01', '2024-01-01 05:00:00'), '終了丁度');
        $this->assertEquals(300, OvernightWorktime::getMinutes('2024-01-01 00:00:00', '2024-01-01 05:00:00'), '時間いっぱい');
        $this->assertEquals(300, OvernightWorktime::getMinutes('2024-01-01 00:00:00', '2024-01-01 05:01:01'), '終了後まで');
    }

    /**
     * 夜間のみ
     *
     * @return void
     */
    public function test_lateNight()
    {
        $this->assertEquals(0, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-01 23:00:00'), '同一時刻');
        $this->assertEquals(61, OvernightWorktime::getMinutes('2024-01-01 22:00:00', '2024-01-01 23:01:01'), '開始丁度');
        $this->assertEquals(59, OvernightWorktime::getMinutes('2024-01-01 23:01:01', '2024-01-02 00:00:00'), '終了丁度');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00'), '時間いっぱい');
        $this->assertEquals(120, OvernightWorktime::getMinutes('2024-01-01 21:58:01', '2024-01-02 00:00:00'), '開始直前');
    }

    /**
     * 深夜
     *
     * @return void
     */
    public function test_overnight()
    {
        $this->assertEquals(241, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 03:01:01'), '日跨ぎ');
        $this->assertEquals(301, OvernightWorktime::getMinutes('2024-01-01 22:00:00', '2024-01-02 03:01:01'), '開始丁度');
        $this->assertEquals(359, OvernightWorktime::getMinutes('2024-01-01 23:01:01', '2024-01-02 05:00:00'), '終了丁度');
        $this->assertEquals(420, OvernightWorktime::getMinutes('2024-01-01 22:00:00', '2024-01-02 05:00:00'), '時間いっぱい');

        $this->assertEquals(180, OvernightWorktime::getMinutes('2024-01-01 21:58:01', '2024-01-02 01:00:00'), '開始直前');
        $this->assertEquals(360, OvernightWorktime::getMinutes('2024-01-01 23:00:00', '2024-01-02 05:01:01'), '終了直後');

        $this->assertEquals(420, OvernightWorktime::getMinutes('2024-01-01 21:58:01', '2024-01-02 05:01:01'), '開始前〜終了後');
    }
}
