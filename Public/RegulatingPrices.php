<?php

require_once(__DIR__ . '/head.php');

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
$storage = __DIR__ . '/../Storage/Data/' . $DateTime->format('Y') . '/' . $DateTime->format('m') . '/RegulatingPrices';
$filename = '/' . $DateTime->format('Y-m-d') . '.json';
$url = '';

if (isset($date))
{
  $address['query']['endDate'] = $date;
}

if (!Prerequisites($filename, $storage))
{
	die('Unable to create data file: ' . $filename . PHP_EOL);
}

$filename = $storage . $filename;
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

list($table, $total) = ParseRegulatingPrices($json);

echo(json_encode([$table, $total]));
