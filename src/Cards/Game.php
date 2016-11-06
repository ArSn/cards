<?php
namespace Cards;

use LogicException;
use SplObjectStorage;

class Game
{
	/**
	 * @var SplObjectStorage|Player[]
	 */
	private $players;

	private $currentPlayer;
	/**
	 * @var Pack
	 */
	private $pack;
	/**
	 * @var Card[]
	 */
	private $cardsInGame;

	public function __construct(Player $player1, Player $player2)
	{
		$this->players = new SplObjectStorage();
		$this->addPlayer($player1);
		$this->addPlayer($player2);
	}

	public function addPlayer(Player $player)
	{
		$player->setGame($this);
		$this->players->attach($player);
	}

	public function start()
	{
		$this->pack = new Pack();
		$this->pack->setGame($this);
	}

	public function getPack()
	{
		return $this->pack;
	}

	public function addCardToGame(Card $card)
	{
		$this->cardsInGame[] = $card;
	}

	public function showCard($cardId)
	{
		foreach ($this->cardsInGame as $card) {
			if ($card->getId() == $cardId) {
				$this->sendToOpposingPlayers('show;' . $card->getId() . ';' . $card->getShortCode());
				return;
			}
		}

		throw new LogicException('Card with ID "' . $cardId . '" was not found in this deck.');
	}

	public function sendToAllPlayers($msg)
	{
		foreach ($this->players as $player) {
			$player->send($msg);
		}
	}

	public function sendToOpposingPlayers($msg)
	{
		foreach ($this->players as $player) {
			if ($player != $this->currentPlayer) {
				$player->send($msg);
			}
		}
	}

	public function sendToOwnPlayer($msg)
	{
		foreach ($this->players as $player) {
			if ($player == $this->currentPlayer) {
				$player->send($msg);
			}
		}
	}

	public function setCurrentPlayerByConnection($from)
	{
		foreach ($this->players as $player) {
			if ($from == $player->getConnection()) {
				$this->currentPlayer = $player;
			}
		}
	}
}