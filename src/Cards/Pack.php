<?php
namespace Cards;

use Countable;
use LogicException;

class Pack implements Countable
{
	/**
	 * @var Deck[]
	 */
	private $decks = [];
	/**
	 * @var Game
	 */
	private $game;

	public function __construct()
	{
		// 2 decks a pack is the status quo for now
		$this->decks[] = new Deck();
		$this->decks[] = new Deck();
	}

	public function canBeDrawnFrom()
	{
		return count($this) > 0;
	}

	public function draw()
	{
		shuffle($this->decks);
		foreach ($this->decks as $deck) {
			if ($deck->canBeDrawnFrom()) {
				$card = $deck->draw();

				$game = $this->game;
				$game->addCardToGame($card);
				$game->sendToAllPlayers('packsize;' . count($this));
				$game->sendToOwnPlayer('draw;' . $card->getId() . ';' . $card->getShortCode());
				$game->sendToOpposingPlayers('draw;opposing');
				return;
			}
		}

		throw new LogicException('Pack is empty, can not draw form it again.');
	}

	public function count()
	{
		$count = 0;
		foreach ($this->decks as $deck) {
			$count += count($deck);
		}
		return $count;
	}

	public function setGame(Game $game)
	{
		$this->game = $game;
	}
}