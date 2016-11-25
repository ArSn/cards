<?php

namespace CardTests;

use Cards\Card;
use Cards\Pile;
use PHPUnit\Framework\TestCase;

class PileTest extends TestCase
{
	public function testPileIsEmptyAfterCreation()
	{
		$pile = new Pile();
		$this->assertCount(0, $pile, 'Pile is not empty after creation.');
	}

	public function testOneCardCanBeAddedToPileAndFetched()
	{
		$mockBuilder = $this->getMockBuilder(Card::class);
		$mockBuilder->disableOriginalConstructor();

		$firstCard = $mockBuilder->getMock();

		$pile = new Pile();
		$pile->add($firstCard);
		$this->assertSame([$firstCard], $pile->fetchAll(), 'Fetching one card from pile did not return the card.');
	}

	public function testMultipleCardsCanBeAddedToPileAndFetchedTogether()
	{
		$mockBuilder = $this->getMockBuilder(Card::class);
		$mockBuilder->disableOriginalConstructor();

		$firstCard = $mockBuilder->getMock();
		$secondCard = $mockBuilder->getMock();

		$pile = new Pile();
		$pile->add($firstCard);
		$pile->add($secondCard);
		$this->assertSame(
			[$firstCard, $secondCard],
			$pile->fetchAll(),
			'Fetching all cards from pile did not return all cards.'
		);
	}
}
