<?php
namespace Cards;

use InvalidArgumentException;

class Card
{
	const SUIT_SPADES = 'S';
	const SUIT_HEARTS = 'H';
	const SUIT_DIAMONDS = 'D';
	const SUIT_CLUBS = 'C';
	const SUIT_JOKER = 'J';

	const SUITS = [
		self::SUIT_SPADES,
		self::SUIT_HEARTS,
		self::SUIT_DIAMONDS,
		self::SUIT_CLUBS,
		self::SUIT_JOKER,
	];

	private $value;
	private $suit;
	private $tabbed = false;

	public function __construct($suit, $value)
	{
		$this->guardAgainstInvalidSuit($suit);
		$this->guardAgainstInvalidValue($value);

		$this->suit = $suit;
		$this->value = $value;
	}

	private function guardAgainstInvalidSuit($suit)
	{
		if (in_array($suit, static::SUITS) === false) {
			throw new InvalidArgumentException('Suit "' . $suit . '" is invalid.');
		}
	}

	private function guardAgainstInvalidValue($value)
	{
		if ((is_numeric($value) && $value < 2 && $value > 10)
			// (J)ack, (Q)ueen, (K)ing, (A)ce, (F)ool aka Joker
			|| is_string($value) && in_array($value, ['J', 'Q', 'K', 'A', 'F']) === false
		) {
			throw new InvalidArgumentException('Value "' . $value . '" is invalid.');
		}
	}

	public function getSuit()
	{
		return $this->suit;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getId()
	{
		return spl_object_hash($this);
	}

	public function isTabbed() : bool
	{
		return $this->tabbed;
	}

	public function isTabbedAsInteger() : int
	{
		return (int)$this->isTabbed();
	}

	public function setTabbed(bool $tabbed)
	{
		$this->tabbed = $tabbed;
	}

	public function toggleTabbed()
	{
		if ($this->isTabbed()) {
			$this->setTabbed(false);
		} else {
			$this->setTabbed(true);
		}
	}

	/**
	 * Short code for handling of this card in communication. This is a combination of value and suite codes, e.g.
	 * (K)ing of (H)earts would be "KH" or (7) of (C)lubs would be "7C".
	 *
	 * @return string
	 */
	public function getShortCode()
	{
		return $this->getValue() . $this->getSuit();
	}
}