<?
KiCONST::add('DBCFG', (object)[ 
	'HOST'	=>	'127.0.0.1',
	'USER'	=>	'',
	'PASS'	=>	'',
	'NAME'	=>	''
], false);


KiCONST::add('MAILCFG', (object)[ 
	'PASS'	=>	'',
	'USER'	=>	'',
	'SMTP'	=>	'',
	'KEY'	=>	''
], false);


KiCONST::add('SOCIAL', (object)[ 
        'VKID' => 0,
        'VKKEY' => '',
        'VKSCOPE' => '',
        'MRID' => 0,
        'MRKEY' => '',
        'MRSCOPE' => '',
        'FBID' => 0,
        'FBKEY' => '',
        'FBSCOPE' => '',
        'GITID' => '',
        'GITKEY' => '',
        'GITSCOPE' => '',
        'TWID' => '',
        'TWKEY' => ''
], false);


KiCONST::add('URI_ALLOW', (object)[ 
        'LOGONCB'=> 'api'
], false);

?>
