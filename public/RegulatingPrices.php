<?php

header('content-type: application/json', true, 200);

function parse($json)
{
	$divider = 100;
	$table = ['down' => [], 'up' => []];
	$total = ['down' => 0.0, 'up' => 0.0];

	foreach ($json->data->Rows as $columns)
	{
		switch ($columns->Name)
		{
			case 'Avg':
			case 'Max':
			case 'Min':
				continue 2;
				break;
		}
	
		$row = array_filter($columns->Columns, function ($column)
		{
			if ($column->GroupHeader === 'SE4')
			{
				return true;
			}
			return false;
		});

		$row = array_map(function ($column) use ($divider, &$table, &$total)
		{
			$column->Value = str_replace([" ", ","], ["", "."], $column->Value);

			if (empty($column->Value) || $column->Value === "-")
			{
				return 0;
			}

			if ($column->Name === 'Down')
			{
				$table['down'][] = round((float)($column->Value) / $divider, 3);
				$total['down'] = round($total['down'] + ((float)($column->Value) / $divider), 3);
			}

			if ($column->Name === 'Up')
			{
				$table['up'][] = round((float)($column->Value) / $divider, 3);
				$total['up'] = round($total['up'] + ((float)($column->Value) / $divider), 3);
			}

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
// $DateTime->setDate(2024, 1, 19);

/** Regulation prices, hourly. */
$address = [
	'protocol' => 'https',
	'domain' => 'www.nordpoolgroup.com',
	'uri' => '/api/marketdata/page/386',
	'query' => [
		'currency' => ',,SEK,EUR',
		// 'entityName' => 'SE4',
		// 'dd'=>'SE4',
		// 'view' => 'table',
	],
];
// $date = $DateTime->format('d-m-Y');
$filename = $DateTime->format('Y-m-d') . '-RegulatingPrices.json';
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

echo(json_encode([$table, $total]));
