<?php

header('content-type: application/json', true, 200);

function parse($json)
{
	$divider = 100;
	$table = [];
	$total = 0;

	foreach ($json->data->Rows as $columns)
	{
		switch ($columns->Name)
		{
			case 'Average':
			case 'Max':
			case 'Min':
			case 'Peak':
			case 'Off-peak 1':
			case 'Off-peak 2':
							continue 2;
				break;
		}

		$name = preg_replace(["/&nbsp;-&nbsp;/", "/$/"], [":00-", ":00"], $columns->Name);

		$row = array_filter($columns->Columns, function ($column)
		{
			if ($column->Name === 'SE4')
			{
				return true;
			}
			return false;
		});

		$row = array_map(function ($column) use ($divider, $name, &$table, &$total)
		{
			$column->Value = str_replace([" ", ","], ["", "."], $column->Value);

			if (empty($column->Value) || $column->Value === "-")
			{
				return 0;
			}

			$table[$name] = round((float)($column->Value) / $divider, 3);
			$total = round($total + ((float)($column->Value) / $divider), 3);
		}, $row);
	}

	return [$table, $total];
}

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

function update($address, $filename)
{
	$json = file_get_contents("{$address['protocol']}://{$address['domain']}{$address['uri']}?" . http_build_query($address['query']));

	if ($json !== false)
	{
		file_put_contents($filename, $json);
	}

	return json_decode($json);
}

$DateTime = new DateTime();
$DateTime->setDate(2024, 1, 20);

/** Day-ahead prices, hourly. */
$address = [
	'protocol' => 'https',
	'domain' => 'www.nordpoolgroup.com',
	'uri' => '/api/marketdata/page/29',
	'query' => [
		'currency' => ',,SEK,EUR',
	],
];
// $date = $DateTime->format('d-m-Y');
$filename = $DateTime->format('Y-m-d') . '-DayAhead.json';
$filename = __DIR__ . "/$filename";
$url = '';

if (isset($date))
{
  $address['query']['endDate'] = $date;
}

if (!file_exists($filename) && !touch($filename))
{
	die('Unable to create data file: ' . $filename . PHP_EOL);
}

$content = file_get_contents($filename);

if (empty($content))
{
	$json = update($address, $filename);
}

if (!isset($json))
{
	$json = json_decode($content);
}

if (timeToUpdate($json->data->DateUpdated))
{
	$json = update($address, $filename);
}

$modified = gmdate('D, d M Y H:i:s', strtotime($json->data->DateUpdated));

header("Last-Modified:{$modified} GMT");

list($table, $total) = parse($json);

echo(json_encode(['Table' => $table, 'Average' => round($total / 24, 2), 'Total' => $total]));
