<?php

namespace CardTests;

use Cards\Game;
use Cards\Player;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;

class PlayerUnitTest extends TestCase
{
	private function getConnectionMock()
	{
		$mockBuilder = $this->getMockBuilder(ConnectionInterface::class);
		$mockBuilder->disableOriginalConstructor();

		return $mockBuilder->getMock();
	}

	/**
	 * @return Player
	 */
	private function getPlayerWithMockConnection()
	{
		return new Player($this->getConnectionMock());
	}

	public function testMutatorsForName()
	{
		$playerName = 'John hubert the 4th';

		$player = $this->getPlayerWithMockConnection();
		$this->assertSame('', $player->getName(), 'Player is not an empty name after creation.');

		$player->setName($playerName);
		$this->assertSame($playerName, $player->getName(), 'Player name differs from previously set name.');
	}

	public function testDependencySettingOfGame()
	{
		$mockBuilder = $this->getMockBuilder(Game::class);
		$mockBuilder->disableOriginalConstructor();
		$game = $mockBuilder->getMock();

		$player = $this->getPlayerWithMockConnection();
		$this->assertNull($player->getGame(), 'Player game is not null after creation.');

		$player->setGame($game);
		$this->assertSame($game, $player->getGame(), 'Game differs from previously set game dependency.');
	}



	public function testPlayerReturnsConnectionPassedToConstructor()
	{
		$connection = $this->getConnectionMock();

		$player = new Player($connection);
		$this->assertSame($connection, $player->getConnection(), 'Connection differs from previously set one.');
	}

	public function testPlayerSendProxiesConnectionSend()
	{
		$payload = 'some random payload in here üöääößß3837';
		$return = 'and some return as well;';

		$connection = $this->getConnectionMock();

		$connection->expects($this->once())
			->method('send')
			->with($this->equalTo($payload))
			->will($this->returnValue($return));

		$player = new Player($connection);
		$this->assertSame($return, $player->send($payload), 'Sending payload to player did not return expected value.');
	}

	public function testPlayerCloseProxiesConnectionClose()
	{
		$connection = $this->getConnectionMock();

		$connection->expects($this->once())
			->method('close');

		$player = new Player($connection);
		$player->close();
	}
}
