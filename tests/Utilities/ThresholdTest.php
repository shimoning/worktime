
<?php

use PHPUnit\Framework\TestCase;
use Carbon\CarbonImmutable;
use Shimoning\Worktime\Utilities\Threshold;

class ThresholdTest extends TestCase
{
    /**
     * 文字列 がパースできることを確認する
     * TODO: 他のフォーマットの時間もテストする
     *
     * @return void
     */
    public function test_string()
    {
        $this->assertSame('2024-01-01 09:00:00', Threshold::get('2024-01-01 09:00:00', 9)->format('Y-m-d H:i:s'), '同じ時刻');
        $this->assertSame('2024-01-01 10:00:00', Threshold::get('2024-01-01 09:00:00', 10)->format('Y-m-d H:i:s'), '1時間後');
        $this->assertSame('2024-01-01 00:00:00', Threshold::get('2024-01-01 10:00:00', 0)->format('Y-m-d H:i:s'), '0時間');
    }

    /**
     * unixtime がパースできることを確認する
     *
     * @return void
     */
    public function test_unixtime()
    {
        $this->assertSame('1970-01-01 09:00:00', Threshold::get(0, 9)->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:00:00', Threshold::get(1704099600, 10)->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:00:00', Threshold::get(1704099600, 10)->format('Y-m-d H:i:s'));
    }

    /**
     * carbon がパースできることを確認する
     *
     * @return void
     */
    public function test_carbon()
    {
        $carbon = CarbonImmutable::parse('2024-01-01 00:00:00');
        $this->assertSame('2024-01-01 10:00:00', Threshold::get($carbon, 10)->format('Y-m-d H:i:s'));

        $carbon2 = CarbonImmutable::parse('2024-01-01 09:00:00');
        $this->assertSame('2024-01-01 10:00:00', Threshold::get($carbon2, 10)->format('Y-m-d H:i:s'));
    }
}
