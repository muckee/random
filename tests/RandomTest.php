<?php

namespace Http5\Random\Tests;

use Http5\Random\AbstractRand;
use Http5\Random\HashRand;
use Http5\Random\MtRand;
use Http5\Random\RandException;
use Http5\Random\Random;
use Http5\Random\XorShiftRand;
use PHPUnit\Framework\TestCase;

class RandomTest extends TestCase
{
    public function testCreate(): void
    {
        // Default generator and seed
        /** @var AbstractRand $rnd */
        $rnd = Random::create();
        $this->assertInstanceOf(XorShiftRand::class, $rnd);
        $this->assertTrue(is_int($rnd->getSeed()));

        // Predefined seed and generator
        $rnd = Random::create('seeeedz', Random::MT);
        $this->assertInstanceOf(MtRand::class, $rnd);
        $this->assertSame('seeeedz', $rnd->getSeed());

        $rnd = Random::create('some data', Random::HASH);
        $this->assertInstanceOf(HashRand::class, $rnd);
        $this->assertSame('some data', $rnd->getSeed());
    }

    /**
     * @expectedException RandException
     */
    public function testCreateException(): void
    {
        $rnd = Random::create(null, __CLASS__);
    }

    public function testCreateFromState(): void
    {
        /** @var AbstractRand $rnd */
        $rnd = Random::create(null, Random::HASH);
        $rnd->random(); $rnd->random(); $rnd->random();

        $state = $rnd->getState();
        $seq1 = [];
        for($i=0; $i<100; $i++) {
            $seq1[] = $rnd->random(0, $i);
        }

        $rnd = Random::createFromState($state);
        $this->assertInstanceOf('\Http5\Random\HashRand', $rnd);

        $seq1test = [];
        for($i=0; $i<100; $i++) {
            $seq1test[] = $rnd->random(0, $i);
        }
        $this->assertSame($seq1, $seq1test);
    }

    /**
     * @expectedException RandException
     */
    public function testBadStateException(): void
    {
        $rnd = Random::createFromState(['bad' => 'state']);
    }

    /**
     * @expectedException RandException
     */
    public function testBadStateClassException(): void
    {
        $rnd = Random::createFromState(['class' => __CLASS__]);
    }
}
