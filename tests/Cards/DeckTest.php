<?php

namespace CardTests;

use Cards\Card;
use Cards\Deck;
use LogicException;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
	public function testAllCardsCanBeDrawnFromAFreshDeck()
	{
		$deck = new Deck();

		// All standard cards (52) plus 3 jokers should be in the deck
		for ($i = 55; $i > 0; $i--) {
			$this->assertSame($i, $deck->count(), 'Deck count is not what it should before drawing cards.');
			$this->assertTrue($deck->canBeDrawnFrom(), 'Deck claims that it can not be drawn from it although it should');
			$card = $deck->draw();
			$this->assertInstanceOf(Card::class, $card, 'Drawn card does not appear to be a "Card" instance.');
		}
	}

	/**
	 * @expectedException LogicException
	 */
	public function testDrawingFromADeckThatCanNotBeDrawnFromThrowsException()
	{
		$deck = new Deck();

		for ($i = 55; $i > 0; $i--) {
			$deck->draw();
		}

		$this->assertFalse($deck->canBeDrawnFrom(), 'Deck claims that it can be drawn from although it should not.');
		$this->setExpectedExceptionFromAnnotation();
		$deck->draw();
	}
}
