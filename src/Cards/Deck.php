<?php
namespace Cards;

class Deck
{
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

	public function draw() : Card
	{
		return array_pop($this->cards);
	}
}