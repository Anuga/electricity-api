<?php

function Parse($json)
{
	$rows = [];

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
			if ($column->GroupHeader === 'SE4' || $column->Name === 'SE4')
			{
				return true;
			}
			return false;
		});

		$rows[$name] = $row;
	}

	return $rows;
}

function ParseDayAhead($json)
{
	$divider = 100;
	$table = [];
	$total = 0;
	$rows = Parse($json);

	foreach ($rows as $key => $row)
	{
		$row = array_map(function ($column) use ($divider, $key, &$table, &$total)
		{
			$column->Value = str_replace([" ", ","], ["", "."], $column->Value);

			if (empty($column->Value) || $column->Value === "-")
			{
				return 0;
			}

			$table[$key] = round((float)($column->Value) / $divider, 3);
			$total = round($total + ((float)($column->Value) / $divider), 3);
		}, $row);
	}

	return [$table, $total];
}

function ParseRegulatingPrices($json)
{
	$divider = 100;
	$table = [];
	$total = ['down' => 0.0, 'up' => 0.0];
	$rows = Parse($json);

	foreach ($rows as $key => $row)
	{
		$row = array_map(function ($column) use ($divider, $key, &$table, &$total)
		{
			if (empty($column->Value) || $column->Value === "-")
			{
				return 0;
			}

			$column->Value = str_replace([" ", ","], ["", "."], $column->Value);

			if ($column->Name === 'Down')
			{
				$table[$key]['down'] = round((float)($column->Value) / $divider, 3);
				$total['down'] = round($total['down'] + ((float)($column->Value) / $divider), 3);
			}

			if ($column->Name === 'Up')
			{
				$table[$key]['up'] = round((float)($column->Value) / $divider, 3);
				$total['up'] = round($total['up'] + ((float)($column->Value) / $divider), 3);
			}

		}, $row);
	}

	return [$table, $total];
}
