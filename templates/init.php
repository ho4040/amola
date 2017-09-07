<?php

	define("MYSQL_HOST", "localhost" );
	define("PORT", 3306);
	define("MYSQL_ID", "root");
	define("MYSQL_PASSWORD", "");
	define("USER_DB", "");
	define("DEV_HOST", "test.example.com");

	mysqli_report(MYSQLI_REPORT_OFF);

	function getDb() {
		$conn = new mysqli(MYSQL_HOST, MYSQL_ID, MYSQL_PASSWORD, USER_DB, PORT);
		$conn->set_charset("utf8mb4");
		if ($conn->connect_error) {
			die("Connection failed: ".$conn->connect_error);
		}
		return $conn;
	}

	function closeDb($conn) {
		$conn->close();
	}

	function real_escape_string($text){
		global $conn;
		return $conn->real_escape_string($text);
	}

	function insert($conn, $query) {
		$input = func_get_args();
		$num = func_num_args();
		$stmt = $conn->prepare($query);

		if($stmt) {

			$refArr = array();
			foreach($input as $index=>&$param) {
				if($index == 2 && strlen($param) > 0)   $refArr[] = &$param;
				else if($index > 2)						 $refArr[] = &$param;
			}
			if(count($refArr) > 0)
				call_user_func_array(array($stmt, 'bind_param'), $refArr);  //가변인자를 전달.

			$stmt->execute();
			$affectedRows = $stmt->affected_rows;
			$stmt->close();		 
			return $affectedRows;
		}
		else
		{
			if($_SERVER["HTTP_HOST"] == DEV_HOST)
				throw new Exception("insert query error : ".$query);
			else
				throw new Exception("insert query failed");
		}
	}

	function update($conn, $query) {

		$input = func_get_args();
		$num = func_num_args();
		$stmt = $conn->prepare($query);
		if($stmt) {

			$refArr = array();
			foreach($input as $index=>&$param) {
				if($index == 2 && strlen($param) > 0)   $refArr[] = &$param;
				else if($index > 2)						 $refArr[] = &$param;
			}
			if(count($refArr) > 0)
				call_user_func_array(array($stmt, 'bind_param'), $refArr);  //가변인자를 전달.


			$stmt->execute();
			$affectedRows = $stmt->affected_rows;
			$stmt->close();
			return $affectedRows;
		}
		else
		{
			if($_SERVER["HTTP_HOST"] == DEV_HOST)
				throw new Exception("update query error : ".$query);
			else
				throw new Exception("update query failed");
		}

	}

	function delete($conn, $query) {

		$input = func_get_args();
		$num = func_num_args();
		$stmt = $conn->prepare($query);

		if($stmt) {

			$refArr = array();
			foreach($input as $index=>&$param) {
				if($index == 2 && strlen($param) > 0)   $refArr[] = &$param;
				else if($index > 2)						 $refArr[] = &$param;
			}
			if(count($refArr) > 0)
				call_user_func_array(array($stmt, 'bind_param'), $refArr);  //가변인자를 전달.

			$stmt->execute();
			$affectedRows = $stmt->affected_rows;
			$stmt->close();
			return $affectedRows;
		}
		else
		{
			if($_SERVER["HTTP_HOST"] == DEV_HOST)
				throw new Exception("delete query error : ".$query);
			else
				throw new Exception("delete query failed");
		}
	}

	function select($conn, $query) {
		$input = func_get_args();

		$stmt = $conn->prepare($query);

		if($stmt == null){
			if($_SERVER["HTTP_HOST"] == DEV_HOST)
				throw new Exception("query error : ".$query);
			else
				throw new Exception("query error");
		}

		if(count($input)>2) {
			$refArr = array();
			foreach($input as $index=>&$param) {
				if($index >= 2)
					$refArr[] = &$param;
			}

			if(!call_user_func_array(array($stmt, 'bind_param'), $refArr)){
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				var_dump($refArr);
			}

		}

		if (!$stmt->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			echo "<hr>";
		}

		$stmt->store_result();
		$meta = $stmt->result_metadata();
		$results = array();
		$row = array();
		$columns = array();
		while ($columnName = $meta->fetch_field()) {
			$columns[] = &$row[$columnName->name];
		}
		if(!call_user_func_array(array($stmt, 'bind_result'), $columns)){
			echo "bind_result failed: (" . $stmt->errno . ") " . $stmt->error;
			echo "<hr>";	
		}
		while($stmt->fetch()){
			$newArr = array();
			foreach($row as $key=>$value){
				$newArr[$key] = $value;
			}
			$results[] = $newArr;
		}

		$stmt->close();
		return $results;
	}

	function selectOne($conn, $query) {

		$input = func_get_args();
		$results = getStmtResult($conn, $input, $query);

		if(count($results) > 0)
			return $results[0];

		return null;
	}

	function selectNum($conn, $query){

		$input = func_get_args();
		$results = getStmtResult($conn, $input, $query);

		return $results[0][num];
	}

	function selectValues($conn, $query){
		
		$input = func_get_args();
		$results = getStmtResult($conn, $input, $query);

		if(count($results)>0){
			$arr = array();
			foreach($results as $row){
				$arr []= $row["value"];
			}
			return $arr;
		}
		return null;

	}

	function getStmtResult($conn, $input, $query){

		$stmt = $conn->prepare($query);
		if($stmt == null){
		if($_SERVER["HTTP_HOST"] == DEV_HOST)
		throw new Exception("query error : ".$query);
		else
		throw new Exception("query error");
	}

	if(count($input)>2) {
		$refArr = array();
		foreach($input as $index=>&$param) {
		if($index >= 2)
			$refArr[] = &$param;
		}

		if(!call_user_func_array(array($stmt, 'bind_param'), $refArr)){
		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		var_dump($refArr);
		}

	}

	if (!$stmt->execute()) {
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		echo "<hr>";
	}

	$stmt->store_result();
	$meta = $stmt->result_metadata();
	$results = array();
	$row = array();
	$columns = array();

	while ($columnName = $meta->fetch_field()) {
		$columns[] = &$row[$columnName->name];
	}

	if(!call_user_func_array(array($stmt, 'bind_result'), $columns)){
		echo "bind_result failed: (" . $stmt->errno . ") " . $stmt->error;
		echo "<hr>";
	}

	while($stmt->fetch()){
		$newArr = array();
		foreach($row as $key=>$value){
		$newArr[$key] = $value;
		}
		$results[] = $newArr;
	}

	$stmt->close();
	return $results;
	}

	function getUpdateString($paramName, $type){

		$temp = "";

		if(isset($_REQUEST[$paramName."__op"]))
			$op = $_REQUEST[$paramName."__op"];
		else
			$op = "assign";
		
		$value = real_escape_string($_REQUEST[$paramName]);


		switch( strtolower($op) )
		{
			case "clear":   
				$temp = "`$paramName`=null";
			break;

			default:
				switch($type)
				{
					case "int":
						$temp = "`$paramName`=$value";
						break;
					case "boolean":
						$temp = "`$paramName`=".($value?"1":"0");
						break;
					case "string":
						$temp = "`$paramName`='$value'";
						break;
					case "datetime":
						$temp = "`$paramName`=TIMESTAMP('$value')";
						break;
					case "now":
						$temp = "`$paramName`=now()";
						break;
					case "password":
						$temp = "`$paramName`=PASSWORD('$value')";
						break;
				}	   
			break;
		}

		return $temp;
	}

	function getConditionString($paramName, $type){

		$temp = "`$paramName` #OP# #VALUE#";

		if(isset($_REQUEST[$paramName."__op"]))
			$op = $_REQUEST[$paramName."__op"];
		else
			$op = "eq";
		
		$value = $_REQUEST[$paramName];

		switch(strtolower($op)){
			case "gt":	  $temp = str_replace("#OP#", ">", $temp);		 break;
			case "gte":	 $temp = str_replace("#OP#", ">=", $temp);		 break;
			case "lt":	  $temp = str_replace("#OP#", "<", $temp);		 break;
			case "lte":	 $temp = str_replace("#OP#", "<=", $temp);		 break;
			case "like":	$temp = str_replace("#OP#", "LIKE", $temp);		 break;
			case "btw":	 $temp = str_replace("#OP#", "BETWEEN", $temp);   break;
			case "in":	  $temp = str_replace("#OP#", "IN", $temp);		 break;
			case "isnot":   $temp = str_replace("#OP#", "IS NOT", $temp);	break;
			case "is":	  $temp = str_replace("#OP#", "IS", $temp);		 break;
			case "neq":	 $temp = str_replace("#OP#", "!=", $temp);		 break;
			default:		$temp = str_replace("#OP#", "=", $temp);		 break;
		}

		switch($type){
			
			case "int":	 
				if(strtolower($op) === "btw"){
					$tokens = explode(":", trim($value));
					if(count($tokens) !== 2)
						throw new Exception("btw operation needs param like A:B");
					$temp = str_replace("#VALUE#", $tokens[0]." AND ".$tokens[1], $temp);
				
					}else if(strtolower($op) === "isnot" || strtolower($op) === "is")
						$temp = str_replace("#VALUE#", "$value", $temp);
					else
						$temp = str_replace("#VALUE#", $value, $temp);
				break;
			
			case "boolean":
				$temp = str_replace("#VALUE#", $value==='true'?1:0, $temp);
				break;
			
			case "string":
				if(strtolower($op) === "like")  
					$temp = str_replace("#VALUE#", "'%$value%'", $temp);
				else if(strtolower($op) === "isnot" || strtolower($op) === "is")
					$temp = str_replace("#VALUE#", "$value", $temp);
				else
					$temp = str_replace("#VALUE#", "'$value'", $temp);
				break;
			
			case "datetime":	
				if(strtolower($op) === "btw") {
					$tokens = explode("~", trim($value));
					if(count($tokens) !== 2)
						throw new Exception("btw operation needs param like 'YYYY-MM-dd HH:mm:ss~YYYY-MM-dd HH:mm:ss'");
					$start = $tokens[0];
					$end = $tokens[1];
					$temp = str_replace("#VALUE#", "TIMESTAMP('$start') AND TIMESTAMP('$end')", $temp);
				}else if(strtolower($op) === "isnot" || strtolower($op) === "is")
					$temp = str_replace("#VALUE#", "$value", $temp);
				else
					$temp = str_replace("#VALUE#", "TIMESTAMP('$value')", $temp);
				break;

			case "password":
				if(strtolower($op) === "isnot" || strtolower($op) === "is")
					$temp = str_replace("#VALUE#", "$value", $temp);
				else
					$temp = str_replace("#VALUE#", "PASSWORD('$value')", $temp);
				break;
			
			default:
				$temp = str_replace("#VALUE#", $value, $temp);
				break;
		}
		
		return $temp;
	}
	
	function compParams()
	{
		$params = [];

		for ( $arg = 0; $arg < func_num_args(); $arg++ )
			$params []= func_get_arg($arg);

		return compParamArray($params);
	}

	function compParamArray($params)
	{
		return "(".implode(",", $params).")";
	}
?>