<?php
namespace Cards;

class MoveHandler implements MessageProcessorInterface
{
	public function process(string $message) : string
	{
		$parts = explode(';', $message);
		$top = $parts[3];
		$top -= 100;
		$top = abs($top);
		$parts[3] = $top;
		return implode(';', $parts);
	}
}