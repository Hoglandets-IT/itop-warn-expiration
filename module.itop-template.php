<?php

SetupWebPage::AddModule(
	__FILE__,
	'itop-template/1.0.0',
	array(
		'label' => 'iTop Extension Template',
		'category' => 'business',

		'dependencies' => array(
			'itop-structure/3.0.0',
		),
		'mandatory' => false,
		'visible' => true,
		'datamodel' => ['main.itop-template.php', '...other-php-includes'],
		'webservice' => [],
		'data.struct' => [],
		'data.sample' => [],
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 
		'settings' => array(
			'enabled' => true,
			'some-setting' => "yes"
		),
	)
);


?>
