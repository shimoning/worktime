#!/usr/bin/env php
<?php

namespace Shimoning\Worktime;

require_once __DIR__ . '/vendor/autoload.php';

echo __NAMESPACE__ . " shell\n";
echo "-----\nexample:\n";
echo "var_dump(Basement::diffInMinutes('2024-01-01 09:00:31', '2024-01-01 17:00:00'));\n-----\n\n";

$sh = new \Psy\Shell();

$sh->addCode(sprintf("namespace %s;", __NAMESPACE__));

$sh->run();

echo "\n-----\nBye.\n";
