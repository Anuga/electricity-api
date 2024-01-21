<?php

require_once(__DIR__ . '/head.php');

$DateTime = new DateTime('tomorrow');

if (defined('YEAR') && defined('MONTH') && defined('DATE'))
{
	$DateTime->setDate(YEAR, MONTH, DATE);
	$date = $DateTime->format('d-m-Y');
}

/** Day-ahead prices, hourly. */
$address = [
	'protocol' => 'https',
	'domain' => 'www.nordpoolgroup.com',
	'uri' => '/api/marketdata/page/29',
	'query' => [
		'currency' => ',,SEK,EUR',
	],
];
$force = false;
$storage = __DIR__ . '/../Storage/Data/' . $DateTime->format('Y') . '/' . $DateTime->format('m') . '/DayAhead';
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

list($table, $total) = ParseDayAhead($json);

?>

<body>
	<?php require_once(__DIR__ . '/Menu.php'); ?>
	<pre>
<?= json_encode(['Table' => $table, 'Average' => round($total / 24, 2), 'Total' => $total], JSON_PRETTY_PRINT) ?>
	</pre>
</body>