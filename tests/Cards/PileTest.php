<?php

namespace CardTests;

use Cards\Pile;
use PHPUnit\Framework\TestCase;

class PileTest extends TestCase
{
	public function testPileIsEmptyAfterCreation()
	{
		$pile = new Pile();
		$this->assertCount(0, $pile, 'Pile is not empty after creation.');
	}
}
