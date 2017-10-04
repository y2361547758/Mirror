<?php
//-------------------------config-------------------------------------
	$ContentTypeMap = [
		'.html'	=> 'text/html',
		'.css'	=> 'text/css',
		'.js'	=> 'application/x-javascript',
		'.png'	=> 'image/png',
		'.jpg'	=> 'image/jpeg',	//May Not Used
		'.gif'	=> 'image/gif'		//May Not Used
	];
	//$jsonConfig = '';
	//$config = json_decode($jsonConfig);
	$config = [
		'host'	=> 'https://android.magi-reco.com',	//Target URI
		'exts'	=> 'html|css|js|png|jpg',				//Static File
		'agent'	=> '2',									//Set Agent
		'usrua'	=> '',	//User Define Agent, When agent is 2
		'referer'	=> '',								//Set Referer
		'usrrf'		=> '',								//User Define Referer, Default is No Referer, When referer is 1
		'usrhd'		=>	[								//Set User Addition Headers
			[
				'key'	=> 'USER-ID-FBA9X88MAE',
				'value'	=> '00000000-0000-0000-0000-000000000000'
			],
			[
				'key'	=> 'F4S-CLIENT-VER',
				'value'	=> '1.1.3'
			],
		]
	];