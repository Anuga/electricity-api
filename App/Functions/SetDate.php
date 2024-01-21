<?php

function SetDate(): void
{
	if (isset($_GET['date'], $_GET['month'], $_GET['year']))
	{
		define('DATE', $_GET['date']);
		define('MONTH', $_GET['month']);
		define('YEAR', $_GET['year']);
	}
	else if (isset($_GET['date']))
	{
		if (preg_match("/\./", $_GET['date'], $match))
		{
			list($month, $date, $year) = explode($match[0], $_GET['date']);
		}
		if (preg_match("/-/", $_GET['date'], $match))
		{
			list($year, $month, $date) = explode($match[0], $_GET['date']);
		}
	
		if (isset($year, $month, $date))
		{
			define('DATE', $date);
			define('MONTH', $month);
			define('YEAR', $year);
		}
	}
}
