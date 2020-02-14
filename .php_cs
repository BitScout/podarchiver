<?php

return PhpCsFixer\Config::create()
	->setRiskyAllowed(true)
	->setRules([
		'@Symfony'     => true,
		'@PhpCsFixer'  => true,
		'array_syntax' => ['syntax' => 'short'],
		'binary_operator_spaces' => [
			'align_equals'       => true,
			'align_double_arrow' => true
		],
	])
	->setUsingCache(true)
;
