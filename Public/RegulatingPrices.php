<?php

require_once(__DIR__ . '/head.php');

$DateTime = new DateTime();

if (defined('YEAR') && defined('MONTH') && defined('DATE'))
{
	$DateTime->setDate(YEAR, MONTH, DATE);
	$date = $DateTime->format('d-m-Y');
}

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
$force = false;
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
	$json = Update($address, $filename);
}

if (!isset($json))
{
	$json = json_decode($content);
}

if ($force || TimeToUpdate($json->data->DateUpdated))
{
	$json = Update($address, $filename);
}

$modified = gmdate('D, d M Y H:i:s', strtotime($json->data->DateUpdated));

header("Last-Modified:{$modified} GMT");

list($table, $total) = ParseRegulatingPrices($json);

?>
<body>
<?php require_once(__DIR__ . '/Menu.php'); ?>
	<pre><?= json_encode([$table, $total], JSON_PRETTY_PRINT) ?></pre>
</body>
