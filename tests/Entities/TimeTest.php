<?php

use PHPUnit\Framework\TestCase;
use Shimoning\Worktime\Entities\Time;

class TimeTest extends TestCase
{
    /**
     * 分が負の値の場合エラー
     *
     * @return void
     */
    public function test_exception_negative_minutes()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The minutes must be greater than or equal to 0.');
        new Time(-1);
    }

    public function test_exception_negative_seconds()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The seconds must be greater than or equal to 0.');
        new Time(1, -1);
    }

    public function test_exception_over60_seconds()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The seconds must be less than 60.');
        new Time(1, 60);
    }

    /**
     * 分を取得
     *
     * @return void
     */
    public function test_fromSeconds()
    {
        $time = Time::fromSeconds(0);
        $this->assertSame(0, $time->getMinutes(), '0 minute');
        $this->assertSame(0, $time->getSeconds(), '0 second');

        $time = Time::fromSeconds(1);
        $this->assertSame(0, $time->getMinutes(), '0 minute');
        $this->assertSame(1, $time->getSeconds(), '1 second');

        $time = Time::fromSeconds(59);
        $this->assertSame(0, $time->getMinutes(), '0 minute');
        $this->assertSame(59, $time->getSeconds(), '59 seconds');

        $time = Time::fromSeconds(60);
        $this->assertSame(1, $time->getMinutes(), '1 minute');
        $this->assertSame(0, $time->getSeconds(), '0 second');

        $time = Time::fromSeconds(61);
        $this->assertSame(1, $time->getMinutes(), '1 minute');
        $this->assertSame(1, $time->getSeconds(), '1 second');


        $time = Time::fromSeconds(3599);
        $this->assertSame(59, $time->getMinutes(), '59 minutes');
        $this->assertSame(59, $time->getSeconds(), '59 seconds');

        $time = Time::fromSeconds(3600);
        $this->assertSame(60, $time->getMinutes(), '60 minutes');
        $this->assertSame(0, $time->getSeconds(), '0 seconds');

        $time = Time::fromSeconds(3601);
        $this->assertSame(60, $time->getMinutes(), '60 minutes');
        $this->assertSame(1, $time->getSeconds(), '1 seconds');
    }

    /**
     * 分を取得
     *
     * @return void
     */
    public function test_getMinutes()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->getMinutes(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(1, $time->getMinutes(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(10, $time->getMinutes(), 'Without seconds');

        $time = new Time(10, 1);
        $this->assertSame(10, $time->getMinutes(), 'With seconds');

        $time = new Time(100, 1);
        $this->assertSame(100, $time->getMinutes(), 'Allowed over 60 minutes');
    }

    /**
     * 秒を取得
     *
     * @return void
     */
    public function test_getSeconds()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->getMinutes(), 'Without seconds and 0 minute');

        $time = new Time(10);
        $this->assertSame(0, $time->getSeconds(), 'Without seconds to be 0');

        $time = new Time(10, 0);
        $this->assertSame(0, $time->getSeconds(), 'Allowed 0 second');

        $time = new Time(10, 1);
        $this->assertSame(1, $time->getSeconds(), 'Edge case 1 second');

        $time = new Time(10, 59);
        $this->assertSame(59, $time->getSeconds(), 'Edge case 59 seconds');
    }

    /**
     * 分として取得
     *
     * @return void
     */
    public function test_toMinutes()
    {
        $time = new Time(0);
        $this->assertSame(0.0, $time->toMinutes(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(1.0, $time->toMinutes(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(10.0, $time->toMinutes(), 'Without seconds');

        $time = new Time(10, 30);
        $this->assertSame(10.5, $time->toMinutes(), 'With seconds');

        $time = new Time(100, 30);
        $this->assertSame(100.5, $time->toMinutes(), 'Allowed over 60 minutes with seconds');
    }

    /**
     * 秒として取得
     *
     * @return void
     */
    public function test_toSeconds()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->toSeconds(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(60, $time->toSeconds(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(600, $time->toSeconds(), 'Without seconds');

        $time = new Time(10, 1);
        $this->assertSame(601, $time->toSeconds(), 'Edge case 1 second');

        $time = new Time(10, 59);
        $this->assertSame(659, $time->toSeconds(), 'Edge case 59 seconds');

        $time = new Time(100, 30);
        $this->assertSame(6030, $time->toSeconds(), 'Allowed over 60 minutes with seconds');
    }

    /**
     * 秒を加味して分を取得 (切り上げ)
     *
     * @return void
     */
    public function test_getCeiled()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->getCeiled(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(1, $time->getCeiled(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(10, $time->getCeiled(), 'Without seconds');

        $time = new Time(100);
        $this->assertSame(100, $time->getCeiled(), 'Allowed over 60 minutes');

        $time = new Time(10, 1);
        $this->assertSame(1, $time->getSeconds(), 'Edge case 1 second');

        $time = new Time(10, 29);
        $this->assertSame(11, $time->getCeiled(), 'Edge case 29 seconds');

        $time = new Time(10, 30);
        $this->assertSame(11, $time->getCeiled(), 'Edge case 30 seconds');

        $time = new Time(10, 59);
        $this->assertSame(59, $time->getSeconds(), 'Edge case 59 seconds');

        $time = new Time(100, 30);
        $this->assertSame(101, $time->getCeiled(), 'Allowed over 60 minutes with seconds');
    }

    /**
     * 秒を加味して分を取得 (切り捨て)
     *
     * @return void
     */
    public function test_getFloored()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->getFloored(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(1, $time->getFloored(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(10, $time->getFloored(), 'Without seconds');

        $time = new Time(100);
        $this->assertSame(100, $time->getFloored(), 'Allowed over 60 minutes');

        $time = new Time(10, 1);
        $this->assertSame(1, $time->getSeconds(), 'Edge case 1 second');

        $time = new Time(10, 29);
        $this->assertSame(10, $time->getFloored(), 'Edge case 29 seconds');

        $time = new Time(10, 30);
        $this->assertSame(10, $time->getFloored(), 'Edge case 30 seconds');

        $time = new Time(10, 59);
        $this->assertSame(59, $time->getSeconds(), 'Edge case 59 seconds');

        $time = new Time(100, 30);
        $this->assertSame(100, $time->getFloored(), 'Allowed over 60 minutes with seconds');
    }

    /**
     * 秒を加味して分を取得 (四捨五入)
     *
     * @return void
     */
    public function test_getRounded()
    {
        $time = new Time(0);
        $this->assertSame(0, $time->getRounded(), 'Allowed 0 minute');

        $time = new Time(1);
        $this->assertSame(1, $time->getRounded(), 'Edge case 1 minute');

        $time = new Time(10);
        $this->assertSame(10, $time->getRounded(), 'Without seconds');

        $time = new Time(100);
        $this->assertSame(100, $time->getRounded(), 'Allowed over 60 minutes');

        $time = new Time(10, 1);
        $this->assertSame(1, $time->getSeconds(), 'Edge case 1 second');

        $time = new Time(10, 29);
        $this->assertSame(10, $time->getRounded(), 'Edge case 29 seconds');

        $time = new Time(10, 30);
        $this->assertSame(11, $time->getRounded(), 'Edge case 30 seconds');

        $time = new Time(10, 59);
        $this->assertSame(59, $time->getSeconds(), 'Edge case 59 seconds');

        $time = new Time(100, 30);
        $this->assertSame(101, $time->getRounded(), 'Allowed over 60 minutes with seconds');

        // with modes
        $time = new Time(10, 30);
        $this->assertSame(11, $time->getRounded(\PHP_ROUND_HALF_UP), 'Edge case 30 seconds with PHP_ROUND_HALF_UP (default)');
        $this->assertSame(10, $time->getRounded(\PHP_ROUND_HALF_DOWN), 'Edge case 30 seconds with PHP_ROUND_HALF_DOWN');
        $this->assertSame(10, $time->getRounded(\PHP_ROUND_HALF_EVEN), 'Edge case 30 seconds with PHP_ROUND_HALF_EVEN');
        $this->assertSame(11, $time->getRounded(\PHP_ROUND_HALF_ODD), 'Edge case 30 seconds with PHP_ROUND_HALF_ODD');
    }
}
