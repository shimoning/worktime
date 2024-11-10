
<?php

use PHPUnit\Framework\TestCase;
use Shimoning\Worktime\Utilities\Round;
use Shimoning\Worktime\Constants\RoundingMethod;

class RoundTest extends TestCase
{
    /**
     * undefined -> ROUND
     *
     * @return void
     */
    public function test_undefined()
    {
        $this->assertSame(0, Round::calculate(0), '0');
        $this->assertSame(0, Round::calculate(0.0), '0.0');
        $this->assertSame(0, Round::calculate(0.4), '0.4');
        $this->assertSame(1, Round::calculate((30 / 60)), '0.5');
        $this->assertSame(1, Round::calculate(0.999), '0.999');
    }

    /**
     * null -> RAW
     *
     * @return void
     */
    public function test_null()
    {
        $this->assertSame(0, Round::calculate(0, null), '0');
        $this->assertSame(0.0, Round::calculate(0.0, null), '0.0');
        $this->assertSame(0.4, Round::calculate(0.4, null), '0.4');
        $this->assertSame(0.5, Round::calculate((30 / 60), null), '0.5');
        $this->assertSame(0.999, Round::calculate(0.999, null), '0.999');
    }

    /**
     * RAW
     *
     * @return void
     */
    public function test_raw()
    {
        $this->assertSame(0, Round::calculate(0, RoundingMethod::RAW), '0');
        $this->assertSame(0.0, Round::calculate(0.0, RoundingMethod::RAW), '0.0');
        $this->assertSame(0.4, Round::calculate(0.4, RoundingMethod::RAW), '0.4');
        $this->assertSame(0.5, Round::calculate((30 / 60), RoundingMethod::RAW), '0.5');
        $this->assertSame(0.999, Round::calculate(0.999, RoundingMethod::RAW), '0.999');
    }

    /**
     * CEIL
     *
     * @return void
     */
    public function test_ceil()
    {
        $this->assertSame(0, Round::calculate(0, RoundingMethod::CEIL), '0');
        $this->assertSame(0, Round::calculate(0.0, RoundingMethod::CEIL), '0.0');
        $this->assertSame(1, Round::calculate(0.4, RoundingMethod::CEIL), '0.1');
        $this->assertSame(1, Round::calculate(0.4, RoundingMethod::CEIL), '0.4');
        $this->assertSame(1, Round::calculate(0.4, RoundingMethod::CEIL), '0.9');
        $this->assertSame(1, Round::calculate((30 / 60), RoundingMethod::CEIL), '0.5');
    }

    /**
     * FLOOR
     *
     * @return void
     */
    public function test_floor()
    {
        $this->assertSame(0, Round::calculate(0, RoundingMethod::FLOOR), '0');
        $this->assertSame(0, Round::calculate(0.0, RoundingMethod::FLOOR), '0.0');
        $this->assertSame(0, Round::calculate(0.4, RoundingMethod::FLOOR), '0.1');
        $this->assertSame(0, Round::calculate(0.4, RoundingMethod::FLOOR), '0.4');
        $this->assertSame(0, Round::calculate(0.4, RoundingMethod::FLOOR), '0.9');
        $this->assertSame(0, Round::calculate((30 / 60), RoundingMethod::FLOOR), '0.5');
    }

    /**
     * ROUND
     *
     * @return void
     */
    public function test_round()
    {
        $this->assertSame(0, Round::calculate(0, RoundingMethod::ROUND), '0');
        $this->assertSame(0, Round::calculate(0.0, RoundingMethod::ROUND), '0.0');
        $this->assertSame(0, Round::calculate(0.4, RoundingMethod::ROUND), '0.4');
        $this->assertSame(1, Round::calculate((30 / 60), RoundingMethod::ROUND), '0.5');
    }
}
