<?php
namespace Cards;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class Chat implements MessageComponentInterface
{
	private $clients;

	public function __construct()
	{
		$this->clients = new SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn)
	{
		$this->clients->attach($conn);

		echo "New connection! ({$conn->resourceId})\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		var_dump($msg);
		$numRecv = count($this->clients) - 1;
		echo 'Connection ' . $from->resourceId . ' sending message "' . var_export($msg, true) .
			'" to ' . $numRecv . ' other connection(s)' . "\n";

		foreach ($this->clients as $client) {
			if ($from !== $client) {
				// The sender is not the receiver, send to each client connected
				$client->send($msg);
			}
		}
	}

	public function onClose(ConnectionInterface $conn)
	{
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}
}