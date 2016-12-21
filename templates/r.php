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

	$bShowError = #SHOW_ERROR#;
	if($bShowError){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

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
		if(array_key_exists('page', $_REQUEST))	$page = $_REQUEST['page'];
		if(array_key_exists('num', $_REQUEST))	$num = $_REQUEST['num'];
		if(array_key_exists('orderBy', $_REQUEST))	$orderBy = $_REQUEST['orderBy'];
		if(array_key_exists('orderByDesc', $_REQUEST))	$orderByDesc = $_REQUEST['orderByDesc'];

		//유효성검사 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_VALIDATE_CHECK#

		//Escape ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_ESCAPE#
		

		//타입 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		#PARAM_SET_TYPE#
		if(empty($page)) $page = 0;
		if(empty($num)) $num = 50;
		$offset = $page * $num;


		//WHERE 절 조건 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$conditionList = array();
		#SET_CONDITION_LIST#		
		
		$conditionQuery = "";
		if(count($conditionList)>0)
			$conditionQuery = " WHERE ".implode(" AND ", $conditionList);

		
		//데이터  조회////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$query = "#SELECT_QUERY#".$conditionQuery;

		if(isset($orderBy))
			$query .= " ORDER BY $orderBy";
		else if(isset($orderByDesc))
			$query .= " ORDER BY $orderByDesc DESC";

		if(isset($num) && $num > -1){
			$query .= " LIMIT $offset,$num";
		}

		$dataList = select($conn, $query);
		if($dataList===null)
			$dataList = array();

		foreach($dataList as &$item){
			unset($item['passwd']);
		}

		//데이터  수량 조회 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$cntQuery = "#TOTAL_COUNT_QUERY#";
		$count = selectOne($conn, $cntQuery);
		$total = $count["total"];

		closeDb($conn);

		$resp = array();
		$resp["success"] = true;
		$resp["dataList"] = $dataList;
		$resp["total"] = $total;
		$resp["page"] = $page;
		$resp["num"] = $num;
		echo json_encode($resp);

	}catch(Exception $e){
		$resp = array();		
		$resp["success"] = false;
		$resp["error"] = $e->getMessage();
		$resp["errorCode"] = $e->getCode();
		echo json_encode($resp);
	}
	
?>