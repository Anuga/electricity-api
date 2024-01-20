<?php

function update($address, $filename)
{
	$json = file_get_contents("{$address['protocol']}://{$address['domain']}{$address['uri']}?" . http_build_query($address['query']));

	if ($json !== false)
	{
		file_put_contents($filename, $json);
	}

	return json_decode($json);
}
