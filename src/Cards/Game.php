<?php
namespace Cards;

use SplObjectStorage;

class Game
{
	private $players;
	private $deck;

	public function __construct(Player $player1, Player $player2)
	{
		$this->players = new SplObjectStorage();
		$this->addPlayer($player1);
		$this->addPlayer($player2);
	}

	public function addPlayer(Player $player)
	{
		$this->players->attach($player);
	}

	public function start()
	{
		$this->deck = new Deck();


	}
}