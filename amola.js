var os = require('os');
var argv = require( './argv' );

argv.version('v1.0.0');
argv.info([
	"Simple CRUD code generator for PHP & MySQL",
	"Usage: amola <init|run> [options]"
	].join(os.EOL));

argv.mod({
	mod:'run',
	description:[
		"Generate CRUD codes via settings on .amola file.",
		"Example:",
		"\t amola run",
		"\t amola run /target/project/path"
	].join(os.EOL),
	options:[]
});

argv.mod({
	mod:'init',
	description:[
		"Initialize default settings to target path.",
		"'.amola' file will be generated. to target path.",
		"Example:",
		"\t amola init",
		"\t amola init -t './templates' -o './gen' -s './spec.ama' /path/to/initialize"
	].join(os.EOL),
	options:[
		{
			name:"spec",
			short:"s",
			type:"path",
			description:"the specification file path. Default: './spec.amola'",
			example:"'amola init --spec=VALUE' or 'amola -s VALUE'"
		},{
			name:"template",
			short:"t",	
			type:"path",
			description:"The path of the template files located. Default: './templates'",
			example:"'amola init --template=VALUE' or 'amola -t VALUE'"
		},{
			name:"output",
			short:"o",
			type:"path",
			description:"The path of the files to be generated. Default: './gen'",
			example:"'amola init --output=VALUE' or 'amola -o VALUE'"
		},{
			name:"intermediate",
			short:"im",
			type:"path",
			description:"The path of the intermediate file to be generated. Default: './gen/intermediate.json'",
			example:"'amola init --intermediate=VALUE' or 'amola -im VALUE'"
		}
	]
})

var args = argv.run();

//console.log(args);
if(args.targets.length == 0)
	args.targets.push(".");

switch(args.mod){
	case "init":
		var amola_init = require("./amola_init");
		for(var k in args.targets){
			amola_init(args.options, args.targets[k]);
		}

	break;
	case "run":
		var amola_run = require("./amola_run");
		for(var k in args.targets){
			amola_run(args.targets[k]);
		}
	break;
	default:
		console.log("mode required. check out amola -h");
	break;
}

