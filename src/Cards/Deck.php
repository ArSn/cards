<?php
namespace Cards;

use Countable;
use LogicException;

class Deck implements Countable
{
	/**
	 * @var Card[]
	 */
	private $cards = [];

	public function __construct()
	{
		foreach ([
				Card::SUIT_SPADES,
				Card::SUIT_HEARTS,
				Card::SUIT_DIAMONDS,
				Card::SUIT_CLUBS,
			] as $suit
		) {
			foreach (array_merge(range(2, 10), ['J', 'Q', 'K', 'A']) as $value) {
				$this->addCard($suit, $value);
			}
		}

		for ($i = 0; $i < 3; $i++) {
			// todo: suit for joker does not actually make sense
			$this->addCard(Card::SUIT_JOKER, 'F');
		}

		$this->shuffle();
	}

	private function addCard($suit, $value)
	{
		$this->cards[] = new Card($suit, $value);
	}

	public function shuffle()
	{
		shuffle($this->cards);
	}

	public function count()
	{
		return count($this->cards);
	}

	public function canBeDrawnFrom()
	{
		return count($this) > 0;
	}

	public function draw()
	{
		if (empty($this->cards)) {
			throw new LogicException('Deck is empty, can not draw from it again.');
		}
		return array_pop($this->cards);
	}
}