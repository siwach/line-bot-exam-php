<?php 
	/*Get Data From POST Http Request*/
	$datas = file_get_contents('php://input');
	/*Decode Json From LINE Data Body*/
	$deCode = json_decode($datas,true);
	file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);
	//$replyToken = $deCode['events'][0]['replyToken'];
	$weblink1 = "https://bpi.co.th/gcme";
	$weblink2 = "https://bpi.co.th/gcme/manage/lineregist/";
	$message1 = "ขอโทษด้วยที่ฉันยังไม่เข้าใจคำถามของคุณดีนัก แต่คุณสามารถดูข้อมูล assessment ของคุณได้ที่ ".$weblink1;
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
			//$messages['replyToken'] = $replyToken;
			$messages['messages'][0] = getFormatTextMessage($message1);

			$encodeJson = json_encode($messages);
			$results = sentMessage($encodeJson,$LINEDatas);
		}	
		if ($event['type']=='follow'){
			$uid = $event['source']["userId"]; 
			$result = getLineProfile($LINEDatas, $uid);
			if ($result["result"]=="S"){
				$xmessage = [];
				$xmessage["to"] = array("Uf89ad877a045937f4fcc96c0c1762a10"); //to 
				$xmessage["messages"][0] = array("type"=>"text", "text"=>$result["profile"]);//"message to siwach\nTest new line");
				$encodeMessage1 = json_encode($xmessage);  
				pushMessage($LINEDatas, $encodeMessage1); //send to specify user

				$profileDecode = json_decode($result["profile"],true);
				$displayName = $profileDecode["displayName"];
				$photo = $profileDecode["pictureUrl"];
				$ymessage = [];
				$ymessage["to"] = array($uid);
				$ymessage["messages"][0] = array("type"=>"text", "text"=>($message2.$uid."/".$displayName."/".$photo));
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
	
?>