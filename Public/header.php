<?php

header('content-type: text/html', true, 200);

require_once(__DIR__ . '/../App/Functions/Functions.php');

call_user_func('SetDate');
