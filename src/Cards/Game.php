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
	/**
	 * @var Pile
	 */
	private $discardPile;

	public function __construct(Player $player1, Player $player2)
	{
		$this->players = new SplObjectStorage();
		$this->discardPile = new Pile();
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

	private function findCardById($cardId) : Card
	{
		foreach ($this->cardsInGame as $card) {
			if ($card->getId() == $cardId) {
				return $card;
			}
		}

		throw new LogicException('Card with ID "' . $cardId . '" was not found in this game.');
	}

	public function showCard($cardId)
	{
		$card = $this->findCardById($cardId);
		$this->sendToOpposingPlayers('show;' . $card->getId() . ';' . $card->getShortCode());
	}

	public function tabOrUnTabCard($cardId)
	{
		$card = $this->findCardById($cardId);
		$card->toggleTabbed();
		$this->sendToOpposingPlayers('tab;' . $card->getId() . ';' . $card->isTabbedAsInteger());
	}

	public function discardCard($cardId)
	{
		$card = $this->findCardById($cardId);
		$this->discardPile->add($card);
		$this->sendToOpposingPlayers('discard;' . $card->getId() . ';' . $card->getShortCode());
		$this->sendToAllPlayers('discardsize;' . count($this->discardPile));

		// 2s and fools have to be tabbed on discard pile according to canasta (default game mode) rules
		if ($card->getValue() == 2 || $card->getValue() == 'F') {
			$card->setTabbed(true);
			$this->sendToAllPlayers('tab;' . $card->getId() . ';' . $card->isTabbedAsInteger());
		}
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