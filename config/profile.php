<?php

return [
	'fields' => [
		'about' => [
			'label' => 'About (short description max 4 paraghraph)',
			'type' => 'Textarea',
			'rules' => 'required'
		],
		'occupation' => [
			'label' => 'Job Position?',
			'type' => 'TextInput',
			'rules' => 'required'
		]
	],
];
