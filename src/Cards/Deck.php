<?php
namespace Cards;

class Deck
{
	private $cards = [];

	public function __construct()
	{
		foreach (Card::SUITS as $suit) {
			foreach (array_merge(range(2, 10), ['J', 'Q', 'K', 'A']) as $value) {
				$this->addCard($suit, $value);
			}
		}

		for ($i = 0; $i < 3; $i++) {
			// todo: suit for joker does not actually make sense
			$this->addCard(Card::SUIT_CLUBS, 'F');
		}
	}

	private function addCard($suit, $value)
	{
		$this->cards[] = new Card($suit, $value);
	}

	public function shuffle()
	{
		shuffle($this->cards);
		// todo: do we need this ksort?
		ksort($this->cards);
	}
}