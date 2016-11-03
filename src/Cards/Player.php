<?php

namespace Cards;

use Ratchet\ConnectionInterface;

class Player implements ConnectionInterface
{
	private $connection;
	private $game;
	private $name = '';

	public function __construct(ConnectionInterface $connection)
	{
		$this->connection = $connection;
	}

	public function setName(string $name)
	{
		$this->name = $name;
	}

	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Send data to the connection
	 *
	 * @param  string $data
	 *
	 * @return \Ratchet\ConnectionInterface
	 */
	function send($data)
	{
		return $this->connection->send($data);
	}

	/**
	 * Close the connection
	 */
	function close()
	{
		$this->connection->close();
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public function setGame(Game $game)
	{
		$this->game = $game;
	}

	public function getGame()
	{
		return $this->game;
	}
}