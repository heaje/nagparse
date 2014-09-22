<?php

	function delTree($dir){
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach($files as $file){
			(is_dir("$dir/$file") && !is_link($dir)) ? delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	function hostgroupRecurseSearch($hostgroupName, $hostgroupObjectArray){
		$memberList = array();

		$members = $hostgroupObjectArray[$hostgroupName]->getParam("members");
		$hostgroupMembers = $hostgroupObjectArray[$hostgroupName]->getParam("hostgroup_members");

		if(isset($members)){
			$memberList = array_merge($memberList, $members);
		}
		if(isset($hostgroupMembers)){
			foreach($hostgroupMembers as $curHostgroupMember){
				$memberList = array_merge($memberList, hostgroupRecurseSearch($curHostgroupMember, $hostgroupObjectArray));
			}
		}

		return $memberList;
	}

	function find_all_files($basePath, $ignoreDirList = array(), $ignoreFileList = array()){
		$contents = scandir($basePath);
		$result = array();
		foreach($contents as $value){
			if($value === '.' || $value === '..'){
				continue;
			}

			$fullPath = $basePath . '/' . $value;
			if(is_file($fullPath)){
				if(count(preg_grep("/" . preg_quote($fullPath, "/") . "/", $ignoreFileList)) === 0){
					$result[] = $fullPath;
				}
				#$result[] = $fullPath;
				continue;
			}
			elseif(is_dir($fullPath)){
				if(array_search($fullPath, $ignoreDirList) === false){
					foreach(find_all_files($fullPath, $ignoreDirList) as $recValue){
						if(count(preg_grep("/" . preg_quote($recValue, "/") . "/", $ignoreFileList)) === 0){
							$result[] = $recValue;
						}
					}
				}
				else{
					continue;
				}
			}
		}
		return $result;
	}

?>