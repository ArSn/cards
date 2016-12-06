<?php

namespace CardTests;

use Cards\Chat;
use Cards\Game;
use Cards\Player;
use PHPUnit\Framework\TestCase;

class ChatUnitTest extends TestCase
{
	public function testSaySendsChatMessageToAllPlayers()
	{
		$playerName = 'freakyPlayerNameHere';
		$message = 'some msg to be sent here#+ä#ä348738§$';

		$mockBuilder = $this->getMockBuilder(Player::class);
		$mockBuilder->disableOriginalConstructor();
		$player = $mockBuilder->getMock();
		$player->expects($this->once())
			->method('getName')
			->will($this->returnValue($playerName));

		$mockBuilder = $this->getMockBuilder(Game::class);
		$mockBuilder->disableOriginalConstructor();

		$game = $mockBuilder->getMock();
		$game->expects($this->once())
			->method('getSendingPlayer')
			->will($this->returnValue($player));

		$game->expects($this->once())
			->method('sendToAllPlayers')
			->with($this->equalTo('chat;' . $playerName . ': ' . $message));

		$chat = new Chat($game);
		$chat->say($message);
	}
}
