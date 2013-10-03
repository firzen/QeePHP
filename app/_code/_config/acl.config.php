<?php
return array(
    'default'=>array(
		'allow' => 'ACL_EVERYONE',
    ),
	'users'=>array(
		'allow' => 'ACL_EVERYONE',
		'actions'=>array(
			'changepassword'=>array(
				'allow'=>'ACL_HAS_ROLE'
				)
			)
	),
	'task'=>array(
		'allow' => 'ACL_HAS_ROLE',
	),
	
    
    
);