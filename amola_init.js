//amola_init.js
module.exports = function( options, targetPath ) {
	var os = require('os');
	var fse = require('fs-extra');
	const path = require('path');

	try {

		targetPath = path.resolve(targetPath);
		
		if(!options.spec)
			options.spec = './spec.amola';
		
		if(!options.template)
			options.template = './templates';

		if(!options.output)
			options.output = './gen';
		

		var configPath = path.resolve(targetPath, "./.amola");
		var specPath = path.resolve(targetPath, options.spec);
		var templatePath = path.resolve(targetPath, options.template );
		var outputPath = path.resolve(targetPath, options.output);

		
		console.log("Target path : ", targetPath);
		console.log("Specification file path : ", specPath);
		console.log("Template directory path : ", templatePath);
		console.log("Ouput directory path : ", outputPath);
		
		if(fse.existsSync(targetPath) === false){
			fse.mkdirSync(targetPath);
		}
		
		if(fse.existsSync(configPath) === false){
			fse.writeFileSync(configPath, JSON.stringify(options, null, 4));
		}

		if(fse.existsSync(templatePath) === false){
			var defaultTemplatePath = path.resolve(__dirname, './templates');
			fse.copySync(defaultTemplatePath, templatePath);
		}
		
		if(fse.existsSync(outputPath) === false){
			fse.mkdirSync(outputPath);
		}
		
		if(fse.existsSync(specPath) === false) {
			fse.copySync(path.resolve(__dirname, 'spec.amola'), specPath);
		}

		console.log("\nSuccessfully done.");

	}catch(error){
		console.error(error)
	}
}