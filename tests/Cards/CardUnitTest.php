<?php

namespace CardTests;

use Cards\Card;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CardUnitTest extends TestCase
{
	public function invalidSuitProvider()
	{
		return [
			['eiijej'],
			['uff'],
			['15'],
			[2],
			[false],
		];
	}

	/**
	 * @dataProvider invalidSuitProvider
	 * @param mixed $invalidSuit
	 * @expectedException InvalidArgumentException
	 */
	public function testInstantiatingCardWithInvalidSuitThrowsException($invalidSuit)
	{
		$this->setExpectedExceptionFromAnnotation();
		new Card($invalidSuit, 2);
	}

	public function invalidValueProvider()
	{
		return [
			['eiijej'],
			['uff'],
			['15'],
			[1],
			[false],
		];
	}

	/**
	 * @dataProvider invalidValueProvider
	 * @param mixed $invalidValue
	 * @expectedException InvalidArgumentException
	 */
	public function testInstantiatingCardWithInvalidValueThrowsException($invalidValue)
	{
		$this->setExpectedExceptionFromAnnotation();
		new Card(Card::SUIT_CLUBS, $invalidValue);
	}

	public function testCardIsNotTabbedByDefault()
	{
		$card = new Card(Card::SUIT_CLUBS, 2);
		$this->assertFalse($card->isTabbed(), 'Card is tabbed by default.');
		$this->assertSame(0, $card->isTabbedAsInteger(), 'Card is tabbed as integer by default.');
	}

	public function testCardTabbingMutators()
	{
		$card = new Card(Card::SUIT_CLUBS, 2);

		$card->setTabbed(true);
		$this->assertTrue($card->isTabbed(), 'Card claims it is not tabbed.');
		$this->assertSame(1, $card->isTabbedAsInteger(), 'Card claims it is not tabbed as integer.');

		$card->setTabbed(false);
		$this->assertFalse($card->isTabbed(), 'Card claims it is tabbed.');
		$this->assertSame(0, $card->isTabbedAsInteger(), 'Card claims it is tabbed as integer.');
	}

	public function testCardTabbingToggling()
	{
		$card = new Card(Card::SUIT_CLUBS, 2);
		$card->toggleTabbed();
		$this->assertTrue($card->isTabbed(), 'Card claims it is not tabbed.');
		$card->toggleTabbed();
		$this->assertFalse($card->isTabbed(), 'Card claims it is tabbed.');
	}

	public function testCardHasValidObjectId()
	{
		$card = new Card(Card::SUIT_CLUBS, 2);
		$this->assertInternalType('string', $card->getId(), 'Card ID is not a string.');
		$this->assertNotEmpty($card->getId(), 'Card ID is empty.');
	}

	public function validCardDataProvider()
	{
		return [
			// suit, value
			[Card::SUIT_CLUBS, 2],
			[Card::SUIT_DIAMONDS, 10],
			[Card::SUIT_HEARTS, 'K'],
			[Card::SUIT_SPADES, 'J'],
			[Card::SUIT_JOKER, 'F'],
		];
	}

	/**
	 * @dataProvider validCardDataProvider
	 * @param string $suit
	 * @param mixed $value
	 */
	public function testCardReturnsSuitAndValueAndShortCodeOfValidCards($suit, $value)
	{
		$card = new Card($suit, $value);
		$this->assertSame($suit, $card->getSuit(), 'Suit differs from set one.');
		$this->assertSame($value, $card->getValue(), 'Value differs from set one.');
		$this->assertSame($value . $suit, $card->getShortCode(), 'ShortCode is not as expected.');
	}
}
