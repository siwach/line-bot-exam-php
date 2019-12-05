<?php 
	/*Get Data From POST Http Request*/
	$datas = file_get_contents('php://input');
	/*Decode Json From LINE Data Body*/
	$deCode = json_decode($datas,true);
	file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
	//$replyToken = $deCode['events'][0]['replyToken'];
	$messages = [];
	//$messages['replyToken'] = $replyToken;
	$messages['messages'][0] = getFormatTextMessage("ขอโทษด้วยที่ฉันยังไม่เข้าใจคำถามของคุณดีนัก แต่คุณสามารถดูข้อมูล assessment ของคุณได้ที่ https://bpi.co.th/gcme");
	//$encodeJson = json_encode($messages);
	$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
	$LINEDatas['url_profile'] = "https://api.line.me/v2/bot/profile/";
	$LINEDatas['url_push'] = "https://api.line.me/v2/bot/message/multicast";
	$LINEDatas['token'] = "o9NZ7KFnqWig2wU0rodJtgQH5I93Wq6W/02r/JyUeptCCJ0mOzH1FONFMFpzK41mUErzxIda5u0LUEAA5vixaRC/XB5owB0HxWoyYeoaPz5yF0FFX4PCHWeL3Nn6TWOSs9NKkReGj6njWyR12R/5jQdB04t89/1O/w1cDnyilFU=";
	

	foreach ($deCode['events'] as $event) {
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			$replyToken = $event['replyToken'];
			$messages['replyToken'] = $replyToken;
			$encodeJson = json_encode($messages);
			$results = sentMessage($encodeJson,$LINEDatas);
		}	
		if ($event['type']=='follow'){
			$uid = $event['source']["userId"]; 
			getLineProfile($LINEDatas, $uid);
		}
	}	  
	
	/*Return HTTP Request 200*/
	
	http_response_code(200);
	function getFormatTextMessage($text)
	{
		$datas = [];
		$datas['type'] = 'text';
		$datas['text'] = $text;
		return $datas;
	}
	function sentMessage($encodeJson,$datas)
	{
		$datasReturn = [];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $datas['url'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encodeJson,
		  CURLOPT_HTTPHEADER => array(
		    "authorization: Bearer ".$datas['token'],
		    "cache-control: no-cache",
		    "content-type: application/json; charset=UTF-8",
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		    $datasReturn['result'] = 'E';
		    $datasReturn['message'] = $err;
		} else {
		    if($response == "{}"){
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		    }else{
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		    }
		}
		return $datasReturn;
	}

	function getLINEProfile($datas, $userId)
	{
	   $datasReturn = [];
	   $curl = curl_init();
	   curl_setopt_array($curl, array(
		 CURLOPT_URL => $datas['url_profile'].$userId,
		 CURLOPT_RETURNTRANSFER => true,
		 CURLOPT_ENCODING => "",
		 CURLOPT_MAXREDIRS => 10,
		 CURLOPT_TIMEOUT => 30,
		 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		 CURLOPT_CUSTOMREQUEST => "GET",
		 CURLOPT_HTTPHEADER => array(
		   "Authorization: Bearer ".$datas['token'],
		   "cache-control: no-cache"
		 ),
	   ));
	   $response = curl_exec($curl);
	   $err = curl_error($curl);
	   curl_close($curl);
	   if($err){
		  $datasReturn['result'] = 'E';
		  $datasReturn['message'] = $err;
	   }else{
		  if($response == "{}"){
			  $datasReturn['result'] = 'S';
			  $datasReturn['message'] = 'Success';
			  $msgText = json_encode($response);
			  $message = [];
			  $message["to"] = array("Uf89ad877a045937f4fcc96c0c1762a10");
 			  $message["messages"][0] = array("type"=>"text", "text"=>$msgText);//"Test message to siwach\nTest new line");
			  $encodeMessage = json_encode($message);
			 
			  pushMessage($datas, $encodeMessage);

		  }else{
			  $datasReturn['result'] = 'E';
			  $datasReturn['message'] = $response;
		  }
	   }
	   return $datasReturn;
	}	
	//=======================
	function pushMessage($datas, $message){
	//======================	

		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $datas['url_push'],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $message,
		CURLOPT_HTTPHEADER => array(
			"authorization: Bearer ".$datas['token'],
			"cache-control: no-cache",
			"content-type: application/json; charset=UTF-8",
		),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		if ($err) {
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $err;
		} else {
			if($response == "{}"){
				$datasReturn['result'] = 'S';
				$datasReturn['message'] = 'Success';
				$datasReturn['data'] = $message;
			}else{
				$datasReturn['result'] = 'E';
				$datasReturn['message'] = $response;
			}
		}

		return json_encode($datasReturn);		

	}	
	
?>