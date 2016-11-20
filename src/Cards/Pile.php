<?php
namespace Cards;

use Countable;

class Pile implements Countable
{
	/**
	 * @var Card[]
	 */
	private $cards = [];

	public function add(Card $card)
	{
		$this->cards[] = $card;
	}

	public function count()
	{
		return count($this->cards);
	}

	/**
	 * @return Card[]
	 */
	public function fetchAll() : array
	{
		$cards = $this->cards;
		$this->cards = [];
		return $cards;
	}
}