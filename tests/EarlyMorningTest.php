<?php

use PHPUnit\Framework\TestCase;
use Shimoning\Worktime\EarlyMorning;
use Shimoning\Worktime\Constants\RoundingMethod;
use Carbon\Carbon;

class EarlyMorningTest extends TestCase
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

        EarlyMorning::getMinutes('2024-01-02 03:00:00', '2024-01-02 08:00:00');
        EarlyMorning::getMinutes('2024-01-03 03:00:00', '2024-01-02 03:00:00');
        EarlyMorning::getMinutes('2024-01-03 00:00:00', '2024-01-02 00:00:00');
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

        EarlyMorning::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -1);
        EarlyMorning::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, -999);
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

        EarlyMorning::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 24);
        EarlyMorning::getMinutes('2024-01-02 03:00:00', '2024-01-02 10:00:00', null, 999);
    }

    /**
     * Unix time がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_unixtime()
    {
        // 0 = 1970-01-01 00:00:00
        $this->assertEquals(0, EarlyMorning::getMinutes(0, 0), '同じ時刻');
        $this->assertEquals(0, EarlyMorning::getMinutes(0, 1), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes(0, 60), '1分');

        // 1704074400 = 2024-01-01 02:00:00
        $this->assertEquals(0, EarlyMorning::getMinutes(1704074400, 1704074400), '同じ時刻');
        $this->assertEquals(0, EarlyMorning::getMinutes(1704074400, 1704074401), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes(1704074400, 1704074460), '1分');
    }

    /**
     * Carbon がパースできることを確認する
     *
     * @return void
     */
    public function test_parse_carbon()
    {
        $carbon = Carbon::parse('2024-01-01 02:00:00');
        $this->assertEquals(0, EarlyMorning::getMinutes($carbon, $carbon), '同じ時刻');

        $carbon2 = Carbon::parse('2024-01-01 03:00:01');
        $this->assertEquals(60, EarlyMorning::getMinutes($carbon, $carbon2), '1時間1秒');
    }

    /**
     * 秒を四捨五入することを確認する
     *
     * @return void
     */
    public function test_round()
    {
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 07:00:00'), '範囲外');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00'), '範囲外~早朝開始丁度');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:01'), '範囲外~早朝開始後1秒');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:00'), '同じ時間');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 05:00:00', '2024-01-01 05:00:00'), '終了時刻');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:01'), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:01:00'), '1分');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:29'), '29秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:30'), '30秒');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:00'), '1時間');
        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:01'), '1時間1秒');

        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 00:00:00', '2024-01-01 04:00:00'), '早朝終了時間丁度');
        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 23:00:00', '2024-01-02 04:00:00'), '早朝終了時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 05:00:00'), '早朝終了時間丁度');
        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 06:00:00'), '早朝終了時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 00:00:00'), '早朝から0時');
        $this->assertEquals(300, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 03:00:00'), '早朝から早朝');
    }

    /**
     * 秒を切り上げるすることを確認する
     *
     * @return void
     */
    public function test_ceil()
    {
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 07:00:00', RoundingMethod::CEIL), '範囲外');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '範囲外~深夜開始丁度');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:01', RoundingMethod::CEIL), '範囲外~深夜開始後1秒');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:00', RoundingMethod::CEIL), '同じ時間');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 05:00:00', '2024-01-01 05:00:00', RoundingMethod::CEIL), '終了時刻');

        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:01', RoundingMethod::CEIL), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:01:00', RoundingMethod::CEIL), '1分');

        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:29', RoundingMethod::CEIL), '29秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:30', RoundingMethod::CEIL), '30秒');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:00', RoundingMethod::CEIL), '1時間');
        $this->assertEquals(61, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:01', RoundingMethod::CEIL), '1時間1秒');

        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 00:00:00', '2024-01-01 04:00:00', RoundingMethod::CEIL), '早朝開始時間丁度');
        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 23:00:00', '2024-01-02 04:00:00', RoundingMethod::CEIL), '早朝開始時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 05:00:00', RoundingMethod::CEIL), '早朝終了時間丁度');
        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 06:00:00', RoundingMethod::CEIL), '早朝終了時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 00:00:00', RoundingMethod::CEIL), '早朝から0時');
        $this->assertEquals(300, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 03:00:00', RoundingMethod::CEIL), '早朝から早朝');
    }

    /**
     * 秒が切り捨てられることを確認する
     *
     * @return void
     */
    public function test_floor()
    {
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 07:00:00', RoundingMethod::FLOOR), '範囲外');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '範囲外~深夜開始丁度');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:01', RoundingMethod::FLOOR), '範囲外~深夜開始後1秒');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:00', RoundingMethod::FLOOR), '同じ時間');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 05:00:00', '2024-01-01 05:00:00', RoundingMethod::FLOOR), '終了時刻');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:01', RoundingMethod::FLOOR), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:01:00', RoundingMethod::FLOOR), '1分');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:29', RoundingMethod::FLOOR), '29秒');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:30', RoundingMethod::FLOOR), '30秒');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:00', RoundingMethod::FLOOR), '1時間');
        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:01', RoundingMethod::FLOOR), '1時間1秒');

        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 00:00:00', '2024-01-01 04:00:00', RoundingMethod::FLOOR), '早朝開始時間丁度');
        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 23:00:00', '2024-01-02 04:00:00', RoundingMethod::FLOOR), '早朝開始時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 05:00:00', RoundingMethod::FLOOR), '早朝終了時間丁度');
        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 06:00:00', RoundingMethod::FLOOR), '早朝終了時間跨ぎ');

        $this->assertEquals(120, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 00:00:00', RoundingMethod::FLOOR), '早朝から0時');
        $this->assertEquals(300, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 03:00:00', RoundingMethod::FLOOR), '早朝から早朝');
    }

    /**
     * 時間を変更して計算する
     *
     * @return void
     */
    public function test_hour()
    {
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 07:00:00', RoundingMethod::ROUND, 4), '範囲外: -1');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 07:00:00', RoundingMethod::ROUND, 6), '範囲外: +1');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:00', RoundingMethod::ROUND, 4), '範囲外~深夜開始丁度');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 22:00:00', '2024-01-02 00:00:01', RoundingMethod::ROUND, 4), '範囲外~深夜開始後1秒');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:00', RoundingMethod::ROUND, 4), '同じ時間');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 04:00:00', '2024-01-01 04:00:00', RoundingMethod::ROUND, 4), '終了時刻: -1');
        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 06:00:00', '2024-01-01 06:00:00', RoundingMethod::ROUND, 6), '終了時刻: +1');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:01', RoundingMethod::ROUND, 4), '1秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:01:00', RoundingMethod::ROUND, 4), '1分');

        $this->assertEquals(0, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:29', RoundingMethod::ROUND, 4), '29秒');
        $this->assertEquals(1, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 03:00:30', RoundingMethod::ROUND, 4), '30秒');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 02:00:00', '2024-01-01 03:00:00', RoundingMethod::ROUND, 4), '1時間');
        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 02:00:00', '2024-01-01 03:00:01', RoundingMethod::ROUND, 4), '1時間1秒');

        $this->assertEquals(180, EarlyMorning::getMinutes('2024-01-01 00:00:00', '2024-01-01 03:00:00', RoundingMethod::ROUND, 4), '早朝開始時間丁度');
        $this->assertEquals(180, EarlyMorning::getMinutes('2024-01-01 23:00:00', '2024-01-02 03:00:00', RoundingMethod::ROUND, 4), '早朝開始時間跨ぎ');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 04:00:00', RoundingMethod::ROUND, 4), '早朝終了時間丁度');
        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-01 06:00:00', RoundingMethod::ROUND, 4), '早朝終了時間跨ぎ');

        $this->assertEquals(60, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 00:00:00', RoundingMethod::ROUND, 4), '早朝から0時');
        $this->assertEquals(240, EarlyMorning::getMinutes('2024-01-01 03:00:00', '2024-01-02 03:00:00', RoundingMethod::ROUND, 4), '早朝から早朝');
    }
}
