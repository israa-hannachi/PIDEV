<?php

use App\Kernel;

// Increase max execution time to avoid short CLI/web timeouts during development
@set_time_limit(120);
ini_set('max_execution_time', '120');

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
