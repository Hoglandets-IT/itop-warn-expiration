<?php

SetupWebPage::AddModule(
	__FILE__,
	'itop-warn-expiration/1.0.1',
	array(
		'label' => 'iTop Extension Template',
		'category' => 'business',
		'dependencies' => array(
			'itop-structure/3.0.0',
		),
		'mandatory' => false,
		'visible' => true,
		'datamodel' => ['main.itop-warn-expiration.php'],
		'webservice' => [],
		'data.struct' => [],
		'data.sample' => [],
		'doc.manual_setup' => 'https://github.com/Hoglandets-IT/itop-warn-expiration',
		'doc.more_information' => 'https://github.com/Hoglandets-IT/itop-warn-expiration',
		'settings' => array(),
	)
);


?>
