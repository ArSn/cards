<?php
namespace Cards;

use SplObjectStorage;

class Game
{
	/**
	 * @var SplObjectStorage|Player[]
	 */
	private $players;

	private $currentPlayer;
	/**
	 * @var Deck
	 */
	private $deck;

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
		$this->deck = new Deck();
		$this->deck->setGame($this);
	}

	public function getDeck()
	{
		return $this->deck;
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