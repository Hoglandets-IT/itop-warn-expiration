<?php

SetupWebPage::AddModule(
	__FILE__,
	'itop-warn-expiration/1.0.0',
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
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 
		'settings' => array(
			// 'enabled' => true,
			// 'default_contact' => 'test@example.com',
			// 'webhook' => true,
			// 'warnings' => [
			// 	[
			// 		'type' => 'ExampleType',
			// 		'field' => 'some_date_field',
			// 		'notify' => 'test@example.com',
			// 		'timediff' => [
			// 			"days" => 30,
			// 			// "years" => 1,
			// 			// "months" => 1,
			// 			// "hours" => 10,
			// 		],
			// 		'subject' => 'Warning: Expiration date close',
			// 		'message' => <<<EOF
			// 			The expiration date for resource {{name}} has almost been reached.
			// 			Please check {{permalink}}
			// 		EOF
			// 	]
			// ]
		),
	)
);


?>
