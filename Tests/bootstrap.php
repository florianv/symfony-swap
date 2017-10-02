<?php

/** @var \Composer\Autoload\ClassLoader $loader */
if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:
    curl -s http://getcomposer.org/installer | php
    php composer.phar install --dev
EOM;
    exit(1);
}

$loader->addPsr4('Florianv\\SwapBundle\\Tests\\', __DIR__ . '/');