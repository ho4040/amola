<?php	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 주의 
	//	이 코드는 toApi.js 스크립트에 의해 자동으로 생성된 코드입니다.
	//	스팩을 수정하려면 api.txt 파일을 에서 명새를 수정하고 toApi.js 파일을 다시 실행하면 됩니다.
	//	자동생성을 더 이상 사용하지 않고자 할 경우에는 api.txt 파일에서 본 API를 제외하고 이 주석을 지워주세요.
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	session_start();

	header('Content-type: application/json');
	require_once '../common/init.php';

	$authRequired = #AUTH_REQUIRED#;
	$resp = array();		
	
	try{

		//접속 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#DB_CONNECT#

		//accessToken 검사///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if( $authRequired ) {
			if(isset($_REQUEST["accessToken"]))
				auth($conn, $_REQUEST["accessToken"]);
			else
				throw new Exception("accessToken required!", ERROR_AUTH_FAILED);
		}

		//인자 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#GET_PARAMS#

		
		//유효성검사 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_VALIDATE_CHECK#

		//Escape ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_ESCAPE#

		//타입 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_SET_TYPE#

		$names = array();
		$values = array();

		//VALUE 지정/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#SET_VALUE_NAME_AND_VALUE_ARRAY#

		
		//인서트/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$query = "INSERT INTO `#TABLE#` (".implode(",", $names).") VALUES(".implode(",", $values).")";
		$num = insert($conn, $query);

		if($conn->errno == 1062)
			throw new Exception("duplicated value contained", ERROR_DUPLICATED_KEY);
		
		if($num <= 0)
			throw new Exception("insert failed", ERROR_INSERT_FAILED);
		
		///추가된 아이템 가져온다 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$insertId = $conn->insert_id;
		$resp['dataList'] = array();
		$fieldName = autoIncColumnName($conn, '#TABLE#');
		if($fieldName != null){
			$newItem = selectOne($conn, "SELECT * FROM `#TABLE#` WHERE ".$fieldName."=?", "i", $insertId);
			array_push($resp['dataList'], $newItem);
			$resp["fieldName"] = $fieldName;
			$resp["insertId"] = $insertId;
		
		}else{
			$conds = array();
			for($i=0;$i<count($names);$i++){
				$conds[] = $names[$i]."=".$values[$i];
			}
			$newItem = selectOne($conn, "SELECT * FROM `#TABLE#` WHERE ".implode(" AND ", $conds));
			array_push($resp['dataList'], $newItem);
		}

		
		

		//성공시 수행할 명령어///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#ON_SUCCESS#

		
		$resp["success"] = true;
		$resp["inserted"] = $num;
		echo json_encode($resp);

	}catch(Exception $e){
		$resp["success"] = false;
		$resp["error"] = $e->getMessage();
		$resp["errorCode"] = $e->getCode();
		echo json_encode($resp);
	}

	// INSERT LOG //
	/*
	if(isset($_SESSION["user"]))
		$userId = $_SESSION["user"]["userId"];
	else
		$userId = -1;
	insert($conn, "INSERT INTO `crm_log` (`userId`, `target`, `log`, `result`, `regdate`) VALUES (?, '#TABLE#',?,?,now())", "iss", $userId, $query, json_encode($resp));*/

	if(empty($conn) == false)
		closeDb($conn);
	
?>
