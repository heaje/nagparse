#!/usr/bin/env php
<?php
	require('lib/nagParse.class.php');
	require('lib/parseFunctions.php');

	/**
	 * -f => The configuration file to parse.  Can be passed multiple times.
	 * -d => A directory with configuration files to parse.  Only *.cfg files are evaluated.
	 */
	$options = getopt('f:d:h');

	if(isset($options['h'])){
		echo "This command will parse Nagios configuration files and then output various bits of information.\n";
		echo "By default it will parse the nagios.cfg file, but it is possible to change that by using the options below.\n\n";
		echo "-f => The configuration file to parse.  Can be passed multiple times.\n";
		echo "-d => A configuration file directory to parse.  Can be passed multiple times. Only *.cfg files are evaluated\n";
		exit(1);
	}

	if(isset($options['f'])){
		$configFiles = (is_string($options['f'])) ? array($options['f']) : $options['f'];
	}

	// Search each directory given for any *.cfg files.  Directories are NOT searched recursively.
	if(isset($options['d'])){
		$configDirs = (is_string($options['d'])) ? array($options['d']) : $options['d'];

		foreach($configDirs as $curDir){
			if(!is_dir($curDir)){
				throw new RuntimeException('Cannot access '.$curDir);
			}

			foreach(scandir($curDir) as $curFile){
				$fullPath = $curDir.'/'.$curFile;
				if($curDir == "." || $curDir == ".."){
					continue;
				}
				elseif(is_dir($fullPath)){
					continue;
				}
				elseif(!is_file($fullPath)){
					throw new RuntimeException('Cannot access '.$fullPath);
				}

				$fileExt = pathinfo($fullPath, PATHINFO_EXTENSION);
				if($fileExt === 'cfg'){
					$configFiles[] = $fullPath;
				}
			}
		}
	}

	// Default to the nagios.cfg file if no configuration files were provided by the user
	$configFiles = (!isset($configFiles)) ? array('/usr/local/nagios/etc/nagios.cfg') : $configFiles;

	// Create our nagParse object and then pass it all of the configuration files that were found
	$parser = new nagParse();
	foreach($configFiles as $configFile){
		$parser->parseConfigFile($configFile);
	}
	
	// Flatten out the Nagios configuration (evaluate dependencies)
	$flatConfig = $parser->flattenConfig();
	
	// Get the global configuration (from nagios.cfg usually)
	$globalConfig = $parser->getGlobalConfig();
	
	// Parse all the macros in the flattened configuration
	$flatConfig = $parser->parseMacros($flatConfig);

	/*
	 * The following section of this script will find all the various commands that are actually
	 * used by Nagios for services or other things.  It will then compare all of those commands to
	 * all the scripts that reside in /usr/local/nagios/libexec.  Any scripts that do not match
	 * a command are returned as a list of potentially unused scripts.
	 */
	
	$commandsUsed = array();
	$missingConfigs = array();

	/*
	 * Find all of the commands used in 'ocsp_comand', services, hosts, eventhandlers,
	 * or contacts.
	 */
	if(isset($globalConfig["ocsp_command"])){
		$commandsUsed[$globalConfig["ocsp_command"]] = true;
	}

	if(isset($flatConfig["service"])){
		foreach($flatConfig["service"] as $curService){
			$checkCommandParams = $curService->getCommand();
			$eventHandlerParams = $curService->getEventHandler();
			if($checkCommandParams[0] != ""){
				$commandsUsed[$checkCommandParams[0]] = true;
			}
			if($eventHandlerParams[0] != ""){
				$commandsUsed[$eventHandlerParams[0]] = true;
			}
		}
	}
	else{
		$missingConfigs[] = "service";
	}

	if(isset($flatConfig["host"])){
		foreach($flatConfig["host"] as $curHost){
			$checkCommandParams = $curHost->getCommand();
			$eventHandlerParams = $curHost->getEventHandler();

			if($checkCommandParams[0] != ""){
				$commandsUsed[$checkCommandParams[0]] = true;
			}
			if($eventHandlerParams[0] != ""){
				$commandsUsed[$eventHandlerParams[0]] = true;
			}
		}
	}
	else{
		$missingConfigs[] = "host";
	}

	if(isset($flatConfig["contact"])){
		foreach($flatConfig["contact"] as $curContact){
			$checkHostCommandParams = $curContact->getHostCommand();
			$checkServiceCommandParams = $curContact->getServiceCommand();
			if($checkHostCommandParams[0] != ""){
				$commandsUsed[$checkHostCommandParams[0]] = true;
			}
			if($checkServiceCommandParams[0] != ""){
				$commandsUsed[$checkServiceCommandParams[0]] = true;
			}
		}
	}
	else{
		$missingConfigs[] = "contact";
	}

	/*
	 * Parse the list of commands that are specified in the Nagios configuration and then
	 * figure out which ones were used in the previously parsed contacts, hosts, etc.
	 */
	$commandLines = array();
	$potentialUnusedCommandLines = array();
	$potentialUnusedScripts = array();
	foreach($flatConfig["command"] as $curCommand){
		$commandName = $curCommand->getName();
		$commandLine = $curCommand->getParam("command_line");
		$commandLines[] = $commandLine;
		if(!isset($commandsUsed[$commandName])){
			$match = array();
			$spaceIndex = strpos($commandLine, " ");
		}
	}

	/*
	 * It's entirely possible that there are specific directories or files that we know
	 * should not be parsed by this script.  Add them to the $ignoreDirs and $ignoreFiles
	 * arrays.
	 */
	$ignoreDirs = array(
		'/usr/local/nagios/libexec/testdir',
	);
	$ignoreFiles = array(
        '/usr/local/nagios/libexec/testfile',
	);
	$scripts = find_all_files("/usr/local/nagios/libexec", $ignoreDirs, $ignoreFiles);

	/*
	 * Drop all the unused scripts into an array, sort it, and then print it out.
	 */
	foreach($scripts as $curScript){
		if(count(preg_grep("/".preg_quote($curScript, "/")."/", $commandLines)) > 0){
			continue;
		}
		else{
			$potentialUnusedScripts[] = $curScript;
		}
	}

	if(count($potentialUnusedScripts) > 0){
		$sorted = array_values(array_unique($potentialUnusedScripts, SORT_REGULAR));
	}

	foreach($potentialUnusedScripts as $curScript){
		echo $curScript."\n";
	}

	/*
	 * It's possible to pass only sections of the Nagios configuration to the nagParser object.
	 * This is mostly just a warning to let uses know when not all the objects that could have
	 * used commands are found.
	 */
	if(count($missingConfigs) > 0){
		echo "\n\n WARNING: This list is potentially incorrect.  No configuration info for the following objects was found:\n";
		foreach($missingConfigs as $missing){
			echo $missing."\n";
		}
	}
?>