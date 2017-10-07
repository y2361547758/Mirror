<?php
require_once './data/config.php';

//-------------Start------------------
	//Root URL
	$rootUrl = (($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
	//URL
	$url = $config['host'] . $_SERVER['REQUEST_URI'];
	//Extension Name
	$thisExt = pathinfo($_SERVER['ORIG_PATH_INFO'],PATHINFO_EXTENSION);
	$bIsCache = in_array($thisExt, explode("|", $config['exts']));
	//Static
	if($bIsCache){
		$sFilePath = dirname($_SERVER['DOCUMENT_ROOT']) . $_SERVER['ORIG_PATH_INFO'];
		//File Found
		if(is_file($sFilePath)){
			if($nginx){
				//header("HTTP/1.1 301 Moved Permanently");
				header('X-Accel-Redirect: ' . $sFilePath);
				header('Last-Modified: ' . date('r'));
				header('Content-Type: ' . $ContentTypeMap[$thisExt]);
				header('Expires: ' . date('r', time() + 86400 * 7));
			}else echo file_get_contents($sFilePath);
			exit();
		}
	}

//-------------Set Agent---------------
	switch($config['agent']){
		case 1:		//No Agent
			//$_SERVER['HTTP_USER_AGENT'] = '';
			unset($_SERVER['HTTP_USER_AGENT']);
			break;
		case 2:		//User Define Agent
			$_SERVER['HTTP_USER_AGENT'] = $config['usrua'];
			break;
		default:	//Client Agent
			//$_SERVER['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
			break;
	}

//-------------Set referer-------------
	switch($config['referer']){
		case 1:		//User Define Referer
			$_SERVER['HTTP_REFERER'] = $config['usrrf'];;
			break;
		default:	//Auto Referer
			if(!empty($_SERVER['HTTP_REFERER'])){
				$_SERVER['HTTP_REFERER'] = str_ireplace($rootUrl, $config['host'], $_SERVER['HTTP_REFERER']);
			}
			break;
	}

//-------------cURL Init--------------
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);				//Set Request URL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		//Get Respon Stream
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);		//Get Respon Stream in Binary
	curl_setopt($ch, CURLOPT_HEADER, true);				//Get Respen Stream Header
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		//Do Not Verifypeer
	//curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');	//Set Develop Proxy

	$header = array();									//Copy Request Headers
	foreach($_SERVER as $key => $item){
		if(stripos($key, 'HTTP_') === 0 /* && $key != 'HTTP_CONTENT_LENGTH' */)	//May not Follow Content Length
			array_push($header, substr($key, 5) . ': ' . $item);
	}

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		curl_setopt($ch, CURLOPT_POST, true);			//Set Request Method, Default is 'GET'
		$body = file_get_contents("php://input");		//Set Request Raw Body
		//array_push($header, 'Content-Length: ' . strlen($body));	//Auto Set Content Length if need
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	}

	//--------Set Other Headers---------
	if(!empty($config['usrhd'])){
		foreach($config['usrhd'] as $item)array_push($header, $item['key'] . ': ' . $item['value']);
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//---------------Request----------------
	//$r=gzdecode(curl_exec($ch));						//Option, decode
	$r = curl_exec($ch);
	$iHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$rHeader = substr($r, 0, $iHeaderSize);
	$rBody = substr($r, $iHeaderSize);
	curl_close($ch);

//--------------Work on Response---------
	//----------New Static File----------
	if($bIsCache){
		//$sFilePath = dirname($_SERVER['DOCUMENT_ROOT']) . $_SERVER['ORIG_PATH_INFO']);
		if(is_writable($sFilePath))file_put_contents($sFilePath, $rBody, LOCK_EX);	//Check Write Able and Write, May Lock
	}
	/*
	//-----------Replace Domain----------
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('','php','html'))){
			$rBody = str_replace($config['host'], $rootUrl, $rBody);	//Replace Domain
		}
	}
	//--------Replace Relative HTML------
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('', 'php', 'html'))){
			$rBody = str_replace('="/', '="' . siteUri(), $rBody);
			$rBody = str_replace('=\'/', '=\'' . siteUri(), $rBody);
			$rBody = preg_replace('/<base href=.*?\/>/', '', $rBody);
		}
	}
	//--------Replace Relative CSS-------
	if(empty($config['relativeCSS'])){
		if(in_array($thisExt,array('css'))){
			$rBody = str_replace('url("/', 'url("' . siteUri(), $rBody);
		}
	}
	*/
//----------------Output-----------------
	echo $rBody;