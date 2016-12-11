
var os = require('os');
var fs = require('fs');
var contents = fs.readFileSync('api.txt').toString();

var lines = contents.split(os.EOL);

var curTable = "";
var curGrade = "";
var dbConnectCode = "";
var auth = "false";
var showError = "false";
var onSuccess = "";
var comment = "";
lines = lines.map(function(e)
{ 

	if(e.indexOf("#") == 0)
	{
		comment = e.replace("#", "");
		return null;
	}
	if(e.indexOf("table:") != -1)
	{		
		curTable = e.split(":")[1];
		return null;
	}
	if(e.indexOf("auth:") != -1)
	{		
		auth = e.split(":")[1];
		return null;
	}
	if(e.indexOf("showError:") != -1)
	{		
		showError = e.split(":")[1];
		return null;
	}
	if(e.indexOf("grade:") != -1)
	{
		curGrade = e.split(":")[1];
		return null;
	}
	if(e.indexOf("onSuccess:") != -1)
	{
		onSuccess = e.split(":")[1];
		return null;
	}
	if(e.indexOf("connect:") != -1)
	{	
		dbConnectCode = e.split(":")[1];
		return null;
	}
	else 
	{
		var tokens = e.split("?");


		if(!tokens[0])
			return null;

		
		return {
			dbConnectCode:dbConnectCode,
			table:curTable,
			auth:auth,
			showError:showError,
			grade:curGrade,
			onSuccess:onSuccess,
			phpFileName:tokens[0],
			paramStr:tokens[1],
			comment:comment
		};	
	}
})

//console.log("File loaded");

lines = lines.filter(function(e){
	return e != null;
})

lines = lines.map(function(e){ 
	var tokens = e.phpFileName.split(".");
	e.apiName = tokens[0];
	e.ext = tokens[1];
	return e;
})



lines = lines.map(function(e){ 
	var tokens = e.apiName.split("_");
	switch(tokens[0]) {
		case "get": e.crudType = 'r'; break;
		case "update": e.crudType = 'u'; break;
		case "remove": e.crudType = 'd'; break;
		case "add": e.crudType = 'c'; break;
	}
	return e;
});

//console.log("API type read complete.");

lines = lines.map(function(e){

	//console.log(e);
	//console.log("e.paramStr >>>", e.paramStr);
	if(!!e.paramStr){
		e.params = e.paramStr.split("&");
		e.params = e.params.map(function(param) {
			var tokens = param.split("=");
			var name = tokens[0];
			var type = tokens[1];
			//console.log(tokens);

			var required = type.indexOf("*") != -1;
			type = type.replace("*", "");

			var isCondition = type.indexOf("@") != -1;
			type = type.replace("@", "");			

			var isSessionVar = type.indexOf("$") != -1;
			type = type.replace("$", "");

			return { name : name, type: type, required:required, isCondition:isCondition, isSessionVar:isSessionVar }
		})
	}else{
		e.params = null;
	}

	return e;
})

console.log("API read complete.");


fs.writeFileSync("../api/intermediate.json", JSON.stringify(lines, null, 4));

var templates = {};
templates["c"] = fs.readFileSync('templates/c.php').toString();
templates["r"] = fs.readFileSync('templates/r.php').toString();
templates["u"] = fs.readFileSync('templates/u.php').toString();
templates["d"] = fs.readFileSync('templates/d.php').toString();

for(var k in lines){

	var info = lines[k];


	var paramGetContext = "";
	var paramCheckContext ="";
	var paramSetTypeContext = "";
	var realEscapeStringTypeContext = "";
	if(!!info.params){
		
		paramGetContext = info.params.map(function(paramInfo){
			if(!paramInfo.isSessionVar)
				return "if(array_key_exists('"+paramInfo.name+"',$_REQUEST))	$"+paramInfo.name+" = $_REQUEST[\""+paramInfo.name+"\"];";
			else
				return "if(array_key_exists('"+paramInfo.name+"',$_SESSION['user']))	$"+paramInfo.name+" = $_REQUEST[\""+paramInfo.name+"\"]" + " = $_SESSION[\'user\'][\""+paramInfo.name+"\"];";
		}).join("\n\t\t");

		paramCheckContext = info.params.filter(function(e){ return e.required; }).map(function(paramInfo){
			var name = paramInfo.name;		
			return "if(isset($"+name+")==false) throw new Exception('invalid param <"+name+">', ERROR_INVALID_PARAM);";
		}).join("\n\t\t");

		realEscapeStringTypeContext = info.params.map(function(paramInfo){
			var name = paramInfo.name;
			return "if(isset($"+name+")) $"+name+" = $conn->real_escape_string($"+name+");";
		}).join("\n\t\t");

		paramSetTypeContext = info.params.map(function(paramInfo){
			var name = paramInfo.name;
			if(paramInfo.type == "<int>")
				return "if(isset($"+name+")) settype( $"+name+" , 'integer' );";
			else if(paramInfo.type == "<datetime>")
				return "if(isset($"+name+")) settype( $"+name+" , 'string' );";
			else if(paramInfo.type == "<boolean>")
				return "if(isset($"+name+")) $"+name+" = (($"+name+"==='true')?1:0);";
			else
				return "if(isset($"+name+")) settype( $"+name+" , 'string' );";
		}).join("\n\t\t");

	}
	
	var crudType = info.crudType;
	var text = templates[info.crudType];
	//console.log(info.crudType);
	text = text.replace("#DB_CONNECT#", info.dbConnectCode);
	text = text.replace("#GET_PARAMS#", paramGetContext);
	text = text.replace("#PARAM_VALIDATE_CHECK#", paramCheckContext);
	text = text.replace("#PARAM_ESCAPE#", realEscapeStringTypeContext);
	text = text.replace("#PARAM_SET_TYPE#", paramSetTypeContext);
	text = text.replace("#ACCESS_GRADE#", info.grade);
	text = text.replace("#ON_SUCCESS#", (!!info.onSuccess?info.onSuccess:"//없음"));
	text = text.replace("#AUTH_REQUIRED#", info.auth);
	text = text.replace("#SHOW_ERROR#", showError);
	text = text.replace(/#TABLE#/g, info.table);
	
	var conds = [];
	if(!!info.params){
		conds = info.params.filter(function(e){ return e.isCondition }).map(function(e){ 
			var paramType = e.type.replace("<","").replace(">","")
			return "if(isset($"+e.name+"))\t$conditionList[]=getConditionString( '"+e.name+"', '"+paramType+"'  );";
		});	
	}
	var setCondtionListStr = conds.join(os.EOL+"\t\t");

	switch(crudType){
		case "c":
			var names_and_value = info.params.map(function(e){ 
				if(e.type =="<int>")
					return "if(isset($"+e.name+")){ $names[]=\"`"+e.name+"`\"; $values[]=\"$"+e.name+"\"; }";
				else if(e.type =="<datetime>")
					return "if(isset($"+e.name+")){ $names[]=\"`"+e.name+"`\"; $values[]=\"TIMESTAMP('$"+e.name+"')\"; }";
				else if(e.type =="<password>")
					return "if(isset($"+e.name+")){ $names[]=\"`"+e.name+"`\"; $values[]=\"PASSWORD('$"+e.name+"')\"; }";
				else if(e.type =="<now>")
					return "$names[]=\"`"+e.name+"`\"; $values[]=\"NOW()\"; ";
				else
					return "if(isset($"+e.name+")){ $names[]=\"`"+e.name+"`\"; $values[]=\"'$"+e.name+"'\"; }";

			});			
			
			text = text.replace("#SET_VALUE_NAME_AND_VALUE_ARRAY#", names_and_value.join("\n\t\t"));
		break;

		case "r":
			var selectQuery = "SELECT SQL_CALC_FOUND_ROWS * FROM `"+info.table+"`";
			var totalCountQuery = "SELECT FOUND_ROWS() as total;";
			text = text.replace("#TOTAL_COUNT_QUERY#", totalCountQuery);
			text = text.replace(/#SELECT_QUERY#/g, selectQuery);
			text = text.replace("#SET_CONDITION_LIST#", setCondtionListStr);
		break;

		case "u":
			var selectQuery = "SELECT * FROM `"+info.table+"`";

			var param_array = info.params.filter(function(e){ return !e.isCondition }).map(function(e){ 
				if(e.type == "<int>")
					return "if(isset($"+e.name+")) $params[] = \"`"+e.name+"`=$"+e.name+"\";"; 
				else if(e.type == "<datetime>")
					return "if(isset($"+e.name+")) $params[] = \"`"+e.name+"`=TIMESTAMP('$"+e.name+"')\";"; 
				else if(e.type == "<password>")
					return "if(isset($"+e.name+")) $params[] = \"`"+e.name+"`=PASSWORD('$"+e.name+"')\";"; 
				else if(e.type == "<now>")
					return "$params[] = \"`"+e.name+"`=NOW()\";"; 
				else
					return "if(isset($"+e.name+")) $params[] = \"`"+e.name+"`='$"+e.name+"'\";"; 
			});

			text = text.replace("#SELECT_QUERY#", selectQuery);
			text = text.replace("#TABLE#", info.table);
			text = text.replace("#PARAM_ARRAY#", param_array.join("\n\t\t"));
			text = text.replace("#SET_CONDITION_LIST#", setCondtionListStr);

		break;
		case "d":
			var selectQuery = "SELECT * FROM `"+info.table+"`";
			var deleteQuery = "DELETE FROM `"+info.table+"`";
			text = text.replace("#SELECT_QUERY#", selectQuery);
			text = text.replace("#DELETE_QUERY#", deleteQuery);
			text = text.replace("#SET_CONDITION_LIST#", setCondtionListStr);			
		break;
	}
	var fileName = "../api/"+info.phpFileName;
	console.log("generated: ", fileName);
	fs.writeFileSync(fileName, text);
}