<?php

function Prerequisites($filename, $storage)
{
    $status = true;

    if (!file_exists($storage) && !mkdir($storage, 0755, true))
    {
        $status = false;
        // die('Unable to create storage directory.' . PHP_EOL);
    }

    if (!file_exists($storage . $filename) && !touch($storage . $filename))
    {
        $status = false;
        // die('Unable to create data file: ' . $filename . PHP_EOL);
    }

    return $status;
}
