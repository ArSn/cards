<?php
namespace Cards;

interface MessageProcessorInterface
{
	public function process(string $message) : string;
}