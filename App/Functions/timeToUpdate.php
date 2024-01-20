<?php

function timeToUpdate($then)
{
	$now = new DateTime();
	$past = new DateTime();
	$past->setTimestamp(strtotime($then));

	// echo($now->format('Y-m-d H-i-s') . PHP_EOL);
	// echo($past->format('Y-m-d H-i-s') . PHP_EOL);

	if ($now->format('Y') !== $past->format('Y'))
	{
		// echo('Year is not the same.' . PHP_EOL);
		return true;
	}
	if ($now->format('m') !== $past->format('m'))
	{
		// echo('Month is not the same.' . PHP_EOL);
		return true;
	}
	if ($now->format('d') !== $past->format('d'))
	{
		// echo('Day is not the same.' . PHP_EOL);
		return true;
	}
	if ($now->format('H') !== $past->format('H'))
	{
		// echo('Hour is not the same.' . PHP_EOL);
		return true;
	}

	// echo('No update needed.' . PHP_EOL);
	return false;
}
