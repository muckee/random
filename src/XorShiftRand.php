<?php

namespace Http5\Random;

/**
 * Fast and good quality random generator, based on XorShift+ algorithm with 128bit state
 * Based on C code from http://xorshift.di.unimi.it/xorshift128plus.c
 * More info: https://en.wikipedia.org/wiki/Xorshift
 * Comparison of different xorshifts: http://xorshift.di.unimi.it/
 *
 * @package Http5\Random
 * @author  SavvoT <savvot@ya.ru>
 */
class XorShiftRand extends AbstractRand
{
    /**
     * This is 63bit generator because PHP does not support unsigned 64bit int
     */
    const INT_MAX = 0x7FFFFFFFFFFFFFFF;

    const INT_MAX_32 = 0x7FFFFFFF;

    public function randomInt()
    {
        if (PHP_INT_SIZE > 4) {
            $s1 = $this->state[0];
            $s0 = $this->state[1];
            $this->state[0] = $s0;
            $s1 ^= ($s1 << 23) & self::INT_MAX;

            // s1 ^ s0 ^ (s1 >> 17) ^ (s0 >> 26)
            // Original C algorithm operates uint64, but in PHP 64bit int is signed only.
            // Also right shift in PHP is arithmetic, so we need to unset filled sign bits
            $this->state[1] = $s1 ^ $s0 ^ (($s1 >> 17) & (self::INT_MAX >> 16)) ^ (($s0 >> 26) & (self::INT_MAX >> 25));

            return (int) ($this->state[1] + $s0) & self::INT_MAX;
        } else {
            $s1 = $this->state[0];
            $s0 = $this->state[1];
            $this->state[0] = $s0;
            $s1 ^= ($s1 << 23) & self::INT_MAX_32;

            // s1 ^ s0 ^ (s1 >> 17) ^ (s0 >> 26)
            // Original C algorithm operates uint64, but in PHP 64bit int is signed only.
            // Also right shift in PHP is arithmetic, so we need to unset filled sign bits
            $this->state[1] = $s1 ^ $s0 ^ (($s1 >> 17) & (self::INT_MAX >> 16)) ^ (($s0 >> 26) & (self::INT_MAX >> 25));

            return (int) ($this->state[1] + $s0) & self::INT_MAX_32;
        }
    }

    protected function init()
    {
        // Unfortunately P flag for 64bit is 5.6+
        $state = unpack('V*', $this->hashedSeed);
        $this->state = [
            ($state[2] << 32 | $state[1]) & self::INT_MAX,
            ($state[4] << 32 | $state[3]) & self::INT_MAX,
        ];

        // Values must not be 0
        while ($this->state[0] === 0 || $this->state[1] === 0) {
            $this->hashedSeed = md5($this->hashedSeed, true);
            $state = unpack('V*', $this->hashedSeed);
            $this->state = [
                ($state[2] << 32 | $state[1]) & self::INT_MAX,
                ($state[4] << 32 | $state[3]) & self::INT_MAX,
            ];
        }
    }
}
