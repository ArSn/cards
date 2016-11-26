<?php
namespace Cards;

class Chat
{
	private $game;

	public function __construct(Game $game)
	{
		$this->game = $game;
	}

	public function say($message)
	{
		$game = $this->game;
		$playerName = $game->getSendingPlayer()->getName();

		$game->sendToAllPlayers('chat;' . $playerName . ': ' . $message);
	}
}