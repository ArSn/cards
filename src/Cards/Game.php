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
	/**
	 * @var Player Player whos turn it currently is.
	 */
	private $currentPlayer;
	/**
	 * @var Player Player who sends the current message that triggers any game logic.
	 */
	private $sendingPlayer;
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
	/**
	 * @var Chat
	 */
	private $chat;

	public function __construct(Player $player1, Player $player2)
	{
		$this->players = new SplObjectStorage();
		$this->discardPile = new Pile();
		$this->chat = new Chat($this);
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

		// Random player shall start
		$allPlayers = $this->getAllPlayers();
		$this->setCurrentPlayer($allPlayers[array_rand($allPlayers)]);
	}

	public function getPack()
	{
		return $this->pack;
	}

	public function getSendingPlayer() : Player
	{
		return $this->sendingPlayer;
	}

	public function getChat() : Chat
	{
		return $this->chat;
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

	public function pickupDiscardPile()
	{
		$cards = $this->discardPile->fetchAll();

		foreach ($cards as $card) {
			$this->sendToOwnPlayer('draw;' . $card->getId() . ';' . $card->getShortCode());
			$this->sendToOpposingPlayers('draw;opposing');
		}

		$this->sendToAllPlayers('clearDiscardPile');
	}

	/**
	 * @return Player[]
	 */
	private function getAllPlayers() : array
	{
		$players = [];
		foreach ($this->players as $player) {
			$players[] = $player;
		}
		return $players;
	}

	private function setCurrentPlayer(Player $player)
	{
		$this->currentPlayer = $player;
		$this->sendToAllPlayers('currentPlayer;' . $player->getName());
	}

	public function endTurn()
	{
		foreach ($this->players as $player) {
			if ($player != $this->currentPlayer) {
				$this->setCurrentPlayer($player);
				return;
			}
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
			if ($player != $this->sendingPlayer) {
				$player->send($msg);
			}
		}
	}

	public function sendToOwnPlayer($msg)
	{
		foreach ($this->players as $player) {
			if ($player == $this->sendingPlayer) {
				$player->send($msg);
			}
		}
	}

	public function setCurrentPlayerByConnection($from)
	{
		foreach ($this->players as $player) {
			if ($from == $player->getConnection()) {
				$this->sendingPlayer = $player;
			}
		}
	}
}