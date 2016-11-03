<?php
namespace Cards;

class MoveHandler implements MessageProcessorInterface
{
	public function process(string $message) : string
	{
		return $message;
	}
}