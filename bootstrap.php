<?php
Autoloader::add_core_namespace('Markup');
Autoloader::add_classes(
	array(
		'Markup\\Markup' => __DIR__.'/classes/markup.php',
	)
);

\Config::load('markup', true);
