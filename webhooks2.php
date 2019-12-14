<?php 
	/*Get Data From POST Http Request*/
	$datas = file_get_contents('php://input');
	/*Decode Json From LINE Data Body*/
	$deCode = json_decode($datas,true);
	file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
	//$replyToken = $deCode['events'][0]['replyToken'];
	$weblink1 = "https://www.qagcme.com/qaonline";
	$weblink2 = "https://www.qagcme.com/qaonline/index.php/manage/lineregist?";
	$message1 = "Please wait for QA's answer or you can see assessment information at ".$weblink1;
	$message2 = "เพื่อเชื่อม Line ของคุณเข้ากับระบบ QA GCME Online กรุณา login เข้าระบบผ่านทาง link นี้ ".$weblink2;
	
	$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
	$LINEDatas['url_profile'] = "https://api.line.me/v2/bot/profile/";
	$LINEDatas['url_push'] = "https://api.line.me/v2/bot/message/multicast";
	$LINEDatas['token'] = "o9NZ7KFnqWig2wU0rodJtgQH5I93Wq6W/02r/JyUeptCCJ0mOzH1FONFMFpzK41mUErzxIda5u0LUEAA5vixaRC/XB5owB0HxWoyYeoaPz5yF0FFX4PCHWeL3Nn6TWOSs9NKkReGj6njWyR12R/5jQdB04t89/1O/w1cDnyilFU=";
	

	foreach ($deCode['events'] as $event) {
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			$replyToken = $event['replyToken'];
			$messages = [];
			$messages['replyToken'] = $replyToken;			
			$messages['messages'][0] = getFormatTextMessage($message1);

			$encodeJson = json_encode($messages);
			$results = sentMessage($encodeJson,$LINEDatas);
		}	
		if ($event['type']=='follow'){
			$uid = $event['source']["userId"]; 
			$result = getLineProfile($LINEDatas, $uid);
			if ($result["result"]=="S"){
				$profileDecode = json_decode($result["profile"],true);
				$displayName = $profileDecode["displayName"];
				$photo = $profileDecode["pictureUrl"];
				$contents = createFlexMessage($weblink2, $uid, $displayName, $photo);

				$xmessage = [];
				$xmessage["to"] = array("Uf89ad877a045937f4fcc96c0c1762a10"); //to 
				//$xmessage["messages"][0] = array("type"=>"text", "text"=>$result["profile"]);
				$xmessage["messages"][0] = array("type"=>"flex", "altText"=>$result["profile"], "contents"=>$contents);
				$encodeMessage1 = json_encode($xmessage);  
				$pushResult = pushMessage($LINEDatas, $encodeMessage1); //send to specify user 
				file_put_contents('log.txt', json_encode($pushResult)  . PHP_EOL, FILE_APPEND);
				

				$ymessage = [];
				$ymessage["to"] = array($uid);
				$txtmessage = $message2."ruid=$uid&rname=$displayName&rphoto=$photo";
				//$ymessage["messages"][0] = array("type"=>"text", "text"=>$txtmessage);
				$ymessage["messages"][0] = array("type"=>"flex", "altText"=>$txtmessage, "contents"=>$contents);
				file_put_contents('log.txt', $txtmessage  . PHP_EOL, FILE_APPEND);
				$encodeMessage2 = json_encode($ymessage); 
				pushMessage($LINEDatas, $encodeMessage2);

			}
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
	function sentMessage($encodeJson,$datas)  //reply message
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

	function getLINEProfile($datas, $userId)  //get user profile
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

	   file_put_contents('log.txt', "##completed get profile##$response" . PHP_EOL, FILE_APPEND);

	   if($err){
		  $datasReturn['result'] = 'E';
		  $datasReturn['message'] = $err;
		  $datasReturn["profile"] = "";
	   }else{

			  $datasReturn['result'] = 'S';
			  $datasReturn['message'] = 'Success';
			  //$returnDecode = json_decode($response,true);
			  $datasReturn["profile"] = $response; // $returnDecode;

			  //$xmessage = [];
			  //$xmessage["to"] = array("Uf89ad877a045937f4fcc96c0c1762a10");
 			  //$xmessage["messages"][0] = array("type"=>"text", "text"=>$response);//"Test message to siwach\nTest new line");
			  //$encodeMessage1 = json_encode($xmessage);
			  //pushMessage($datas, $encodeMessage1); //send to specify user
			  //pushMessage($datas, $encodeMessage2); //replay to followed person
	   }
	   return $datasReturn;
	}	
	//=======================
	function pushMessage($datas, $message){ //push message
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

		return $datasReturn;		

	}	
	//================================
	function createFlexMessage($uri, $uid, $uname, $uphoto){
		$targetUri = $uri."ruid=$uid&rname=$uname&rphoto=$uphoto";
		$message = json_decode('
		{
			"type": "bubble",
			"hero": {
			  "type": "image",
			  "url": "https://www.qagcme.com/qaonline/images/gcme-line.png",
			  "size": "full",
			  "aspectRatio":"187:88",
			  "aspectMode": "fit",
			  "action": {
				"type": "uri",
				"uri": "'.$targetUri.'"
			  }
			},
			"body": {
			  "type": "box",
			  "layout": "vertical",
			  "spacing": "md",
			  "contents": [
				{
				  "type": "text",
				  "text": "QA GCME Online",
				  "wrap": true,
				  "weight": "bold",
				  "gravity": "center",
				  "size": "xl"
				},
				{
				  "type": "button",
				  "style": "primary",
				  "action": {
					"type": "uri",
					"label": "Link with QA GCME Online",
					"uri": "'.$targetUri.'"
				  }
				},
				{
				  "type": "box",
				  "layout": "vertical",
				  "margin": "lg",
				  "spacing": "sm",
				  "contents": [
					{
					  "type": "box",
					  "layout": "baseline",
					  "spacing": "sm",
					  "contents": [
						{
						  "type": "text",
						  "text": "Please click to link with QA GCME Online เพื่อรับข่าวสารและการแจ้งเตือนต่างๆ ผ่านทาง Line",
						  "color": "#ff0000",
						  "size": "sm",
						  "flex": 1,
						  "wrap": true
						}
					  ]
					}
				  ]
				}
			  ]
			}
		  }	
		');

		return $message;

	}
	
?>
