<?php
if (class_exists('db'))	return;

class db
{
	var $query = "";
	var $db = "";
        var $connect_start = "";
	
	function db($host, $dbname, $dbpass, $dbuser)
	{
		global $glob;
		
		$this->db = new mysqli($host, $dbuser, $dbpass, $dbname) or trigger_error("Error: " . print_r(error_get_last()));
                $this->connect_start = microtime(true);
		if ($this->db->connect_errno > 0) die ($this->debug(true));
		
//		$selectdb = @mysql_select_db($dbname, $this->db);
//		if (!$selectdb) die ($this->debug());
	
	} // end constructor
	
	
	function select($query, $maxRows=0, $pageNum=0)
	{
		$this->query = $query;
		
		// start limit if $maxRows is greater than 0
		if($maxRows>0)
		{
			$startRow = $pageNum * $maxRows;
			$query = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);
		}	
		
		$result = $this->db->query($query);
		
		if ($this->error()) die ($this->debug());
		
		$output=false;
		
		for ($n=0; $n < $result->num_rows; $n++)
		{
			$row = $result->fetch_assoc();
			$output[$n] = $row;
		}
	
		return $output;
		
	} // end select
	
	function misc($query) {
	
		$this->query = $query;
		$result = $this->db->query($query);
		
		if ($this->error()) die ($this->debug());
		
		if($result == TRUE){
		
			return TRUE;
			
		} else {
		
			return FALSE;
			
		}
		
	}
	
	function numrows($query) {
		$this->query = $query;
		$result = $this->db->query($query);
		return $result->num_rows;
	}
	
//	function paginate($numRows, $maxRows, $pageNum=0, $pageVar="page", $class="txtLink")
//	{
//	global $lang;
//	$navigation = "";
//	
//	// get total pages
//	$totalPages = ceil($numRows/$maxRows);
//	
//	// develop query string minus page vars
//	$queryString = "";
//		if (!empty($_SERVER['QUERY_STRING'])) {
//			$params = explode("&", $_SERVER['QUERY_STRING']);
//			$newParams = array();
//				foreach ($params as $param) {
//					if (stristr($param, $pageVar) == false) {
//						array_push($newParams, $param);
//					}
//				}
//			if (count($newParams) != 0) {
//				$queryString = "&" . htmlentities(implode("&", $newParams));
//			}
//		}
//		
//	// get current page	
//	$currentPage = $_SERVER['PHP_SELF'];
//	
//	// build page navigation
//	if($totalPages> 1){
//	$navigation = $totalPages.$lang['misc']['pages']; 
//	
//	$upper_limit = $pageNum + 3;
//	$lower_limit = $pageNum - 3;
//	
//		if ($pageNum > 0) { // Show if not first page
//			
//			if(($pageNum - 2)>0){
//			$first = sprintf("%s?".$pageVar."=%d%s", $currentPage, 0, $queryString);
//			$navigation .= "<a href='".$first."' class='".$class."'>&laquo;</a> ";}
//			
//			$prev = sprintf("%s?".$pageVar."=%d%s", $currentPage, max(0, $pageNum - 1), $queryString);
//			$navigation .= "<a href='".$prev."' class='".$class."'>&lt;</a> ";
//		} // Show if not first page
//		
//		// get in between pages
//		for($i = 0; $i < $totalPages; $i++){
//		
//			$pageNo = $i+1;
//			
//			if($i==$pageNum){
//				$navigation .= "&nbsp;<strong>[".$pageNo."]</strong>&nbsp;";
//			} elseif($i!==$pageNum && $i<$upper_limit && $i>$lower_limit){
//				$noLink = sprintf("%s?".$pageVar."=%d%s", $currentPage, $i, $queryString);
//				$navigation .= "&nbsp;<a href='".$noLink."' class='".$class."'>".$pageNo."</a>&nbsp;";
//			} elseif(($i - $lower_limit)==0){
//				$navigation .=  "&hellip;";
//			} 
//		}
//		  
//		if (($pageNum+1) < $totalPages) { // Show if not last page
//			$next = sprintf("%s?".$pageVar."=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString);
//			$navigation .= "<a href='".$next."' class='".$class."'>&gt;</a> ";
//			if(($pageNum + 3)<$totalPages){
//			$last = sprintf("%s?".$pageVar."=%d%s", $currentPage, $totalPages-1, $queryString);
//			$navigation .= "<a href='".$last."' class='".$class."'>&raquo;</a>";}
//		} // Show if not last page 
//		
//		} // end if total pages is greater than one
//		
//		return $navigation;
//	
//	}
	
	function insert ($tablename, $record)
	{
		if(!is_array($record)) die ($this->debug("array", "Insert", $tablename));
		
		$count = 0;
		foreach ($record as $key => $val)
		{
			if ($count==0) {$fields = "`".$key."`"; $values = $val;}
			else {$fields .= ", "."`".$key."`"; $values .= ", ".$val;}
			$count++;
		}	
		
		$query = "INSERT INTO ".$tablename." (".$fields.") VALUES (".$values.")";
		echo $query . '<br />';
		$this->query = $query;
		$this->db->query($query);
		
		if ($this->error()) die ($this->debug());
		
		if ($this->affected() > 0) return true; else return false;
		
	} // end insert
	
	
	function update ($tablename, $record, $where)
	{
		if(!is_array($record)) die ($this->debug("array", "Update", $tablename));
	
		$count = 0;
		
		foreach ($record as $key => $val)
		{
			if ($count==0) $set = "`".$key."`"."=".$val;
			else $set .= ", " . "`".$key."`". "= ".$val;
			$count++;
		}	
	
		$query = "UPDATE ".$tablename." SET ".$set." WHERE ".$where;
		
		$this->query = $query;
		$this->db->query($query);
		if ($this->error()) die ($this->debug());
		
		if ($this->affected() > 0) return true; else return false;
		
	} // end update
	
//	function categoryNos($cat_id, $sign, $amount = 1) {
//		
//		global $glob;
//	
//		if($cat_id > 0) {
//	
//			do {
//		
//				$record['noProducts'] = " noProducts ".$sign.$amount;
//				$where = "cat_id = ".$cat_id; 
//				$this->update($glob['dbprefix']."CubeCart_category", $record, $where, "");
//			
//				$query = "SELECT cat_father_id FROM CubeCart_category WHERE cat_id = ".$cat_id;
//				$cfi = $this->select($query);
//				$cat_id = $cfi['0']['cat_father_id'];
//			
//			} while ($cat_id > 0);
//			
//		} 
//	
//	}
	
	function delete($tablename, $where, $limit="")
	{
		$query = "DELETE from ".$tablename." WHERE ".$where;
		if ($limit!="") $query .= " LIMIT " . $limit;
		$this->query = $query;
		$this->db->query($query);
		
		if ($this->error()) die ($this->debug());
	
		if ($this->affected() > 0){ 
			return TRUE; 
		} else { 
			return FALSE;
		}
	
	} // end delete
	
	//////////////////////////////////
	// Clean SQL Variables (Security Function)
	////////
	function mySQLSafe($value, $quote="'") { 
		
		// strip quotes if already in
		$value = str_replace(array("\'","'"),"&#39;",$value);
		
		// Stripslashes 
		if (get_magic_quotes_gpc()) { 
			$value = stripslashes($value); 
		} 
		// Quote value
		if(version_compare(phpversion(),"4.3.0")=="-1") {
			$value = $this->db->escape_string($value);
		} else {
			$value = $this->db->real_escape_string($value);
		}
		$value = $quote . trim($value) . $quote; 
	 
		return $value; 
	}
	
	// This function has been built to prevent brute force attacks
//	function blocker($user, $level, $time, $login, $loc)
//	{
//		
//		global $glob;
//		
//		$expireTime = time()-($time*5);
//		
//		$this->delete("blocker","lastTime<".$expireTime);
//		
//		$query = "SELECT * FROM blocker WHERE `browser` = ".$this->mySQLSafe($_SERVER['HTTP_USER_AGENT'])." AND `ip` = ".$this->mySQLSafe($_SERVER['REMOTE_ADDR'])." AND `loc`= '".$loc."'";
//		
//		$blackList = $this->select($query);
//		
//		if($blackList==TRUE && $blackList[0]['blockTime']>time())
//		{
//			// do nothing the user is still banned
//			return TRUE;	
//		}
//		elseif($blackList==TRUE && $blackList[0]['blockTime']>0 && $blackList[0]['blockTime']<time() && $blackList[0]['blockLevel']==$level)
//		{
//			// delete the db row as user is no longer banned
//			$this->delete($glob['dbprefix']."blocker","id=".$blackList[0]['id']);
//			
//			return FALSE;
//		
//		}
//		elseif($blackList==TRUE && $login==FALSE && $blackList[0]['blockTime']==0)
//		{
//			
//			$newdata['lastTime'] = time();
//			
//			// If last attempt was more than the time limit ago we need to set the level to one
//			// This stops a consecutive fail weeks later blocking on first attempt
//			$timeAgo = time() - $time;
//			
//			if($blackList[0]['lastTime']<$timeAgo)
//			{
//				$newdata['blockLevel'] = 1;
//			}
//			else
//			{
//				$newdata['blockLevel'] = $blackList[0]['blockLevel']+1;
//			}
//			
//			if($newdata['blockLevel']==$level)
//			{
//				$newdata['blockTime'] = time() + $time;
//				$this->update($glob['dbprefix']."blocker", $newdata, "id=".$blackList[0]['id'],$stripQuotes="");
//				return TRUE;
//			}
//			else
//			{
//				$newdata['blockTime'] = 0;
//				$this->update($glob['dbprefix']."blocker", $newdata, "id=".$blackList[0]['id'],$stripQuotes="");
//				return FALSE;
//			}
//		
//		}
//		elseif($blackList==FALSE && $login==FALSE)
//		{
//			// insert
//			$newdata['blockLevel'] = 1;
//			$newdata['blockTime'] = 0;
//			$newdata['browser'] = $this->mySQLSafe($_SERVER['HTTP_USER_AGENT']);
//			$newdata['ip'] = $this->mySQLSafe($_SERVER['REMOTE_ADDR']);
//			$newdata['username'] = $this->mySQLSafe($user);
//			$newdata['loc'] = "'".$loc."'";
//			$newdata['lastTime'] = time();
//			
//			$this->insert($glob['dbprefix']."blocker", $newdata);
//			
//			return FALSE;
//		
//		}
//		
//		
//	}
	
	function debug($type="", $action="", $tablename="")
	{
		switch ($type)
		{
			case "connect":
				$message = "MySQL Error Occured";
				$result = $this->db->connect_errno . ": " . $this->db->error;
				$query = "";
				$output = "Could not connect to the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
			break;
		
		
			case "array":
				$message = $action." Error Occured";
				$result = "Could not update ".$tablename." as variable supplied must be an array.";
				$query = "";
				$output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
				
			break;
		
			
			default:
				if (mysql_errno($this->db))
				{
					$message = "MySQL Error Occured";
					$result = $this->db->error . ": " . $this->db->error;
					$output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
				}
				else 
				{
					$message = "MySQL Query Executed Succesfully.";
					$result = $this->db->affected_rows . " Rows Affected";
					$output = "view logs for details";
				}
				
				$linebreaks = array("\n", "\r");
				if($this->query != "") $query = "QUERY = " . str_replace($linebreaks, " ", $this->query); else $query = "";
			break;
		}
        $x = debug_backtrace();
        $dta['error_time'] = date('Y-m-d H:i:s');
        $dta['output'] = $message;
        $dta['query'] = $query;
        $dta['result'] = $result;
        $dta['connect_time'] = microtime(true) - $this->connect_start;
        $dta['file'] = $x[1]['file'];
        $dta['line'] = $x[1]['line'];
        include("ini.inc.php");
        $text = '<span style="font-family: Arial, Helvetica, sans-serif; font-size:12px;">
            Error message received from '.$hostsite.' Website:<br><br><table style="border-collapse:collapse; padding-left:20px; font-family: Arial, Helvetica, sans-serif; font-size:12px;">';
        foreach ($dta as $k => $v) {
            $text .= '<tr><td style="padding:5px;border:1px solid black;"><b>'.$k.'</b></td>
                          <td style="padding:5px;border:1px solid black;">'.$dta[$k].'</td></tr>';
        }
        $text .= '</table></span>';

        $cdir = getcwd();
        $rec[] = $admin;
        send_email($rec, $hostsite.' Website SQL Error', $text, $text);
	}
	
	
	function error()    {   if ($this->db->error) return true; else return false;	}
	
	function insertid() {	return $this->db->insert_id;	}
	
	function affected() {	return $this->db->affected_rows;	}
	
	function close() // close conection
	{ $this->db->close(); }

    function selectmo($m = '')
    {
        $mo[] = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $x = '';
        for ($i=1;$i<=12;$i++) {
            if ($i < 10) { $i1 = '0'.$i; }
            else { $i1 = $i; }
            if ($m == $i1) {
                $x .= '<option value="'.$i1.'" selected="selected">'.$mo[0][$i-1].'</option>';
            }
            else {
                $x .= '<option value="'.$i1.'">'.$mo[0][$i-1].'</option>';
            }
        }
        return $x;
    }
    function getrec($a, $fld, $v) {
        $array_rec = filter_by_value($a, $fld, $v);
        $pagekey = array_keys($array_rec);
        if ($pagekey) {
            $pkey = $pagekey[0];
            return $array_rec[$pkey];
        }
        else return array();
    }

} // end of db class
?>