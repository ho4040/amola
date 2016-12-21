<?php

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CAUTION 
	//	This file is automatically generated via AMOLA CLI
	//	If you want to change somthing about this file, please change it via AMOLA CLI
	//	If you don't need automated process anymore, please remove specification about this file from 'spec.amola' file
	//  And also remove this comment for your coworkers
	//	About AMOLA CLI - https://github.com/ho4040/amola
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	session_start();

	header('Content-type: application/json');
	require_once '../common/init.php';

	$authRequired = #AUTH_REQUIRED#;
	
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

		//유효성검사 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_VALIDATE_CHECK#

		//Escape ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_ESCAPE#

		//타입 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_SET_TYPE#

		//WHERE 절 조건 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$conditionList = array();
		#SET_CONDITION_LIST#		
		if(count($conditionList)>0)
			$conditionQuery = " WHERE ".implode(" AND ", $conditionList);
		else
			throw new Exception("delete need condtions", ERROR_CONDITION_PARAM_REQUIRED);


		//기존데이터 확인 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$selectQuery = "#SELECT_QUERY#".$conditionQuery;
		$originalData = selectOne($conn, $selectQuery);

		//삭제수행  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$query = "#DELETE_QUERY#".$conditionQuery;
		$removed = delete($conn, $query);

		$resp = array();
		$resp["success"] = true;
		$resp["removed"] = $removed;
		echo json_encode($resp);

	}catch(Exception $e){
		$resp = array();		
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
	insert($conn, "INSERT INTO `crm_log` (`userId`, `target`, `log`, `originalData`, `result`, `regdate`) VALUES (?, '#TABLE#',?,?,?,now())", "isss", $userId, $query, json_encode($originalData), json_encode($resp));
	*/

	if(empty($conn) == false)
		closeDb($conn);
	
?>
