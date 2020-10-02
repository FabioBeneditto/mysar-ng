<?php
# Program: mysar, File: www/functions.inc.php
# Copyright 2004-2006, Stoilis Giannis <giannis@stoilis.gr>
#
# This file is part of mysar.
#
# mysar is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2 as published by
# the Free Software Foundation.
#
# mysar is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Foobar; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

function bytesToHRF($bytes,$unit='') {
	// convert bytes to a human readable format
		
	$units['T']=1099511627776;
	$units['G']=1073741824;
	$units['M']=1048576;
	$units['K']=1023;
		
	reset($units);
	while(list($key,$value)=each($units)) {
		if($key==$unit) {
			$bytes=sprintf('%01.2f',$bytes/$value);
			return $bytes.$key;
		}
		if($bytes>$value && empty($unit)) {
			$bytes=sprintf('%01.2f',$bytes/$value);
			return $bytes.$key;
		}
	}
	return $bytes;
}


function date_Ymd2dmY_seperator($date,$seperator) {
	$dateArray=explode('-',$date);
	
	return $dateArray['2'].$seperator.$dateArray['1'].$seperator.$dateArray['0'];
}


function date_timestampFromDbDate($date,$seperator) {
	// returns the timestamp of a db date
		
	$dateArray=explode('-',$date);
	return mktime(0,0,0,$dateArray['1'],$dateArray['2'],$dateArray['0']);
}


function db_delete($link, $query) {

	debug('('.$query.') ',50);
	$recordSet=mysqli_query($link, $query);
	if(!$recordSet) {
		db_error($link, $query);
	}
		
	$affectedRows=mysqli_affected_rows($link);	
	debug('Affected rows: '.$affectedRows,50,__FILE__,__LINE__);
		
	return($affectedRows);
	}


function db_select_all($link, $query) {
// select all rows
	debug('('.$query.') ',40);

	$result=mysqli_query($link, $query);

	if(!$result) {
		debug('SQL Error',20,__FILE__,__LINE__);
		db_error($link, $query);
		debug('Exiting...',20,__FILE__,__LINE__);
		die(1);
	}

	$allRecords=array();
	while($row=mysqli_fetch_array($result)) {
		$allRecords[]=$row;
	}

	return $allRecords;
}


function db_error($link, $query='') {
	// handles the database errors

	debug('ERROR on SQL query',20,__FILE__,__LINE__);
	if($query!='') {
		debug('SQL query: '.$query,20,__FILE__,__LINE__);
	}
	debug('Database error number: '.mysqli_errno($link),20,__FILE__,__LINE__);
	debug('Database error message: '.mysqli_error($link),20,__FILE__,__LINE__);
}


function db_update($link, $query,$minimumAffectedRows=0) {

	debug('('.$query.')',40,__FILE__,__LINE__);
	$result=mysqli_query($link, $query);
	if(!$result) {
		debug('SQL Update Error',20,__FILE__,__LINE__);
		db_error($link, $query);
		debug('Exiting...',20,__FILE__,__LINE__);
		die(1);
	}
		
	$affectedRows=mysqli_affected_rows($link);
	if($affectedRows<$minimumAffectedRows) {
		debug('SQL Update Error: Less affected rows than expected',20,__FILE__,__LINE__);
		db_error($link, $query);
	}
	
	debug('Affected rows: '.$affectedRows,40,__FILE__,__LINE__);
	
	return($affectedRows);
}


function db_query($link, $query) {
	debug('('.$query.')',40,__FILE__,__LINE__);
	return mysqli_query($link, $query);
}

function debug($message,$debugLevel='',$file='',$line='') {
// Prints the debugging messages, depending on the debug level

	global $DEBUG_LEVEL;
	global $DEBUG_MODE;
	
	$newLine='';

	if($DEBUG_LEVEL>=$debugLevel) {
		if($line!='') {
			if($DEBUG_MODE=='cmd') {
				$newLine="\n";
			} else {
				$newLine="</center><br>";
			}
			if($debugLevel>=50) {
				$newLine.=$file.'('.$line.') -> ';
			}
		}
		echo $newLine.$message;
	}
}

function getConfigValue($link, $name) {
	
	$query="SELECT value FROM config WHERE name='$name'";
	$result=db_select_one_row($link, $query);

	return $result['value'];
}

function db_select_one_row($link, $query) {

	debug('('.$query.')',40,__FILE__,__LINE__);
	$result=mysqli_query($link, $query);
	$row=@mysqli_fetch_array($result);

	return $row;
}


function db_select($link, $query) {
	$result=mysqli_query($link, $query);

	if(!$result) {
		db_error($link, $query);
		debug('Exiting...',20,__FILE__,__LINE__);
		die(1);
	}

	return $result;
}

function db_fetch_array($link, $result) {
	if($row=mysqli_fetch_array($result)) {
		return $row;
	} else {
echo mysqli_error($link);
		return FALSE;
	}
}


function setDefaultView() {
// Sets the default view(order method,order by, byte unit) for the current page viewed

	// define the values to change
	$viewParameters=array('OrderBy','OrderMethod','ByteUnit');
	
	reset($viewParameters);
	while(list($key,$value)=each($viewParameters)) {
		$dbName='default'.$_REQUEST['a'].$value;
		$dbValue=$_REQUEST["$value"];

		if($dbValue!='') {
			updateConfig($link, $dbName,$dbValue);
		}

	}
}	


function errorHandler($line,$error) {
	echo "\nError at line: $line\n";
	echo "$error\n";
	exit(1);
}

function getIpFromIpID($link, $ipID,$date) {
	global $s;
	
	$query="SELECT INET6_NTOA(UNHEX(ip)) AS ip FROM resolvedIPs WHERE id='$ipID' AND date='$date'";
	$recordSet=db_select_one_row($link, $query);
	
	return $recordSet['ip'];
}

function getHostnameFromIp($link, $ip) {
	
	$query="SELECT hostname FROM hostnames WHERE ip='$ip'";
	$recordSet=db_select_one_row($link, $query);
	
	return $recordSet['hostname'];
}

function getSiteFromSiteID($link, $sitesID,$date) {
	
	$query="SELECT id,site FROM sites WHERE id='$sitesID' AND date='$date'";
	$recordSet=db_select_one_row($link, $query);
	return $recordSet['site'];
}


function getHostFromIP($link, $ip,$date) {
	
	$query="SELECT id,ip as hostiplong,hostname,description,INET6_NTOA(UNHEX(ip)) AS ip FROM hostnames WHERE ip='$ip'";
	$recordSet=db_select_one_row($link, $query);
	
	return $recordSet;
}


function getUserFromUsersID($link, $usersID,$date) {
	$query="SELECT id,authuser FROM users WHERE id='$usersID' AND date='$date'";
	$recordSet=db_select_one_row($link, $query);
	
	return $recordSet;
}
	
function getActiveUsers($link) {
	global $pageVars,$s;
	
	$time=date('H:i:s',$pageVars['lastTimestamp']-600);
	
	$query="SELECT ";
		$query.='COUNT(DISTINCTROW ip) AS users';
		$query.=' FROM traffic WHERE ';
		$query.="date='".$pageVars['date']."'";
		$query.=" AND ";
		$query.="time>'$time'";
	
	$recordSet=db_select_one_row($link, $query);
	
	return $recordSet['users'];
}

function addParameter($url,$newParameter,$newValue) {
	// forms the url query string, adding or changing the selected parameters
	
	if($url=='') {
		return $newParameter.'='.$newValue;
	}
	$parameters=explode('&',$url);
	reset($parameters);
	while(list($key,$value)=each($parameters)) {
		if(!empty($newQueryString)) {
			$newQueryString=$newQueryString.'&';
		}
		$thisParameter=explode('=',$value);
		if($newParameter==$thisParameter['0']) {
			$newQueryString.=$newParameter.'='.$newValue;
			$foundParameter=TRUE;
		} else {
			$newQueryString.=$thisParameter['0'].'='.$thisParameter['1'];
		}
	}
	if($foundParameter!=TRUE) {
		$newQueryString.='&'.$newParameter.'='.$newValue;
	} else {
	}
	
	return $newQueryString;
}
	

function db_insert($link, $query) {

	debug('('.$query.') ',50);
	$result=mysqli_query($link, $query);
	if(!$result) {
		db_error($link, $query);
		debug('Exiting...',20,__FILE__,__LINE__);
		die(1);
	}
		
	$insertID=mysqli_insert_id($link);
	debug('Insert ID: '.$insertID,40,__FILE__,__LINE__);
		
	return $insertID;
}

function updateLastTimestamp($timestamp) {
	
	$query="UPDATE config SET value='$timestamp' WHERE name='lastTimestamp'";
	debug('Updating lastTimestamp...',40,__FILE__,__LINE__);
	db_update($link, $query);
}

function updateConfig($link, $name,$value) {
	
	debug("Updating config value $name to $value...",40,__FILE__,__LINE__);

	$query='SELECT ';
	$query.='name';
	$query.=' FROM ';
	$query.='config';
	$query.=' WHERE ';
	$query.="name='".$name."'";
	$result=mysqli_query($link, $query);
	if($result) {
		$numrows=mysqli_num_rows($result);
	}
	if($numrows>0) {
		$query="UPDATE config SET value='".$value."' WHERE name='".$name."'";
		db_update($link, $query);
	} else {
		$query='INSERT INTO ';
		$query.='config';
		$query.=' (';
		$query.='name';
		$query.=',';
		$query.='value';
		$query.=') VALUES (';
		$query.="'".$name."'";
		$query.=',';
		$query.="'".$value."'";
		$query.=')';
		db_insert($link, $query);
	}
}


function url_addParameter($url,$newParameter,$newValue) {
// forms the url query string, adding or changing the selected parameters
		
	if($url=='') {
		return $newParameter.'='.$newValue;
	}
	$parameters=explode('&',$url);
	reset($parameters);
	$newQueryString="";
	//while(list($key,$value)=each($parameters)) {
	foreach ($parameters as $key => $value) {
		if(!empty($newQueryString)) {
			$newQueryString=$newQueryString.'&';
		}
		$thisParameter=explode('=',$value);
		if($newParameter==$thisParameter['0']) {
			$newQueryString.=$newParameter.'='.$newValue;
			$foundParameter=TRUE;
		} else {
			$newQueryString.=$thisParameter['0'].'='.$thisParameter['1'];
		}
	}
	if(!isset($foundParameter)) {
		$newQueryString.='&'.$newParameter.'='.$newValue;
	}
		
	return $newQueryString;
}


function url_createSortParameters($url,$validParameters) {
// modifies the given url, to include ASC and DESC parameters for the parameters given
		
	reset($validParameters);
	//while(list($key,$value)=each($validParameters)) {
	foreach ($validParameters as $key => $value) {
		$tmpUrl=url_addParameter($url,'OrderBy',$value);
		$newUrl[$value.'ASC']=url_addParameter($tmpUrl,'OrderMethod','ASC');
		$newUrl[$value.'DESC']=url_addParameter($tmpUrl,'OrderMethod','DESC');
	}
	
	return $newUrl;
}



function my_exit($link, $errorCode) {
	global $lastImportedRecordsNumber,$record,$s;
	
	debug($lastImportedRecordsNumber.' records processed',30,__FILE__,__LINE__);
	updateConfig($link, 'lastTimeStamp',$record[0]);
	updateConfig($link, 'lastImportedRecordsNumber',$lastImportedRecordsNumber);
	debug("\n",30,__FILE__,__LINE__);

	exit($errorCode);
}

function string_trim($string,$maxLength,$substitute='') {
// if $string exceeds length $maxLength, and append $substitute, if it is set
		
	if(strlen($string)>$maxLength) {
		$newString=substr($string,0,$maxLength);
			
		return $newString.$substitute;
	}
		
	return $string;
}



?>
