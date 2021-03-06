<?php
namespace Cards;

use LogicException;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use RuntimeException;
use SplObjectStorage;

class Router implements MessageComponentInterface
{
	private $clients;
	private $lobby;
	private $games;

	public function __construct()
	{
		$this->clients = new SplObjectStorage();
		$this->lobby = new SplObjectStorage();
		$this->games = new SplObjectStorage();
	}

	private function getConnectionId(ConnectionInterface $connection)
	{
		if ($connection instanceof AbstractConnectionDecorator) {
			return $connection->resourceId;
		}
		throw new RuntimeException('Connection ID can not be determined for this connection.');
	}

	public function onOpen(ConnectionInterface $conn)
	{
		$this->clients->attach($conn);

		echo "New connection! (" . $this->getConnectionId($conn) . ")\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo '----';
		echo 'Incoming message from ' . $this->getConnectionId($from) . ': ' . $msg . PHP_EOL;
		$fullMsg = $msg;
		$parts = explode(';', $msg);
		$cmd = array_shift($parts);
		$msg = implode(';', $parts);

		$result = '';
		$sendToAll = false;
		switch ($cmd) {
			case 'name': {
				// Avoid preloading request handling from chrome etc.
				if (empty($msg) || $msg == 'null') {
					return;
				}

				$player = new Player($from);
				$player->setName($msg);
				$this->lobby->attach($player);

				$playerNames = [];
				/** @var Player $player */
				foreach ($this->lobby as $player) {
					$playerNames[] = $player->getName();
				}
				$result = 'lobby;' . implode(';', $playerNames);
				$sendToAll = true;
				break;
			}
			case 'start': {
				if ($this->lobby->count() != 2) {
					throw new LogicException('Not exactly two players in lobby.');
				}
				$this->lobby->rewind();
				/** @var Player $player1 */
				$player1 = $this->lobby->current();
				$this->lobby->detach($player1);
				/** @var Player $player2 */
				$player2 = $this->lobby->current();
				$this->lobby->detach($player2);
				$game = new Game($player1, $player2);
				$this->games->attach($game);

				$game->start();

				$result = 'game;start';

				$sendToAll = true;

				break;
			}
			case 'endTurn': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->endTurn();
				break;
			}
			case 'draw': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->getPack()->draw();
				break;
			}
			case 'pickupDiscardPile': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->pickupDiscardPile();
				break;
			}
			case 'show': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->showCard($msg);
				break;
			}
			case 'move': {
				$handler = new MoveHandler();
				$result = $handler->process($fullMsg);
				break;
			}
			case 'tab': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->tabOrUnTabCard($msg);
				break;
			}
			case 'discard': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->discardCard($msg);
				break;
			}
			case 'chat': {
				$this->games->rewind();
				/** @var Game $game */
				$game = $this->games->current();
				$game->setCurrentPlayerByConnection($from);
				$game->getChat()->say($msg);
				break;
			}
			case 'default': {
				throw new LogicException('Not implemented yet.');
			}
		}

		if (empty($result)) {
			return;
		}

		$numRecv = count($this->clients) - ($sendToAll ? 0 : 1);
		echo 'Connection ' . $this->getConnectionId($from) . ' sending message "' . var_export($result, true) .
			'" to ' . $numRecv . ' other connection(s)' . "\n";

		foreach ($this->clients as $client) {
			if ($from !== $client || $sendToAll) {
				// The sender is not the receiver, send to each client connected
				$client->send($result);
			}
		}
	}

	private function findPlayerByConnection(ConnectionInterface $connection)
	{
		/** @var Game $game */
		foreach ($this->games as $game) {
			foreach ($game->getPlayers() as $player) {
				if ($player->getConnection() == $connection) {
					return $player;
				}
			}
		}

		foreach ($this->lobby as $player) {
			if ($player->getConnection() == $connection) {
				return $player;
			}
		}

		return null;
	}

	private function findGameByConnection(ConnectionInterface $connection)
	{
		/** @var Game $game */
		foreach ($this->games as $game) {
			foreach ($game->getPlayers() as $player) {
				if ($player->getConnection() == $connection) {
					return $game;
				}
			}
		}

		return null;
	}

	public function onClose(ConnectionInterface $conn)
	{
		$game = $this->findGameByConnection($conn);
		if ($game) {
			$game->endGame();
			$this->games->detach($game);
		}

		$player = $this->findPlayerByConnection($conn);
		if ($player) {
			$this->lobby->detach($player);
		}

		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);

		echo "Connection " . $this->getConnectionId($conn) . " has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}
}