<?php
/*
This project was begun by Justin Eldracher in 2015.  Feel free to do whatever you want to this code,
just please let me know what you changed/added so I don't miss out on anything awesome! ;)
If you find any errors or would like to make comments, please leave a message at:
http://blindwarrior.16mb.com/writemsg.php

Please see: https://github.com/JustinEldracher/php-database-library/blob/master/README.md
for more documentation.
*/

Class DB {
	// Version:
	public $_version_ = "1.0.1";
	
	// Database configuration:
	protected $_dbconn_ = null;
	protected $_host_ = "localhost";
	protected $_user_ = "root";
	protected $_pass_ = "";
	protected $_db_ = "mysql";
	protected $_authtable_ = "users";
	protected $_settings_ = "settings";
	protected $_table_ = "";
	protected $_prime_ = "id";
	protected $_debug_ = false;
	protected $_showqueries_ = false;
	
	// File System configuration:
	protected $_useauto_ = true;
	protected $_csvdelim_ = ",";
	protected $_antiword_ = "C:/antiword/antiword.exe";
	protected $_xpdf_ = "C:/xpdf/bin32/pdftotext.exe";
	protected $_dir_ = "";
	
	// Cookie expiration
	protected $_cookiedie_ = 10000000;
	
	// Time Zone  See: http://php.net/manual/en/timezones.php
	protected $_time_zone_ = "America/Detroit";
	
	public function __construct($table = "") {
		$this->init($table);
	}
	
	public function init($table = "") {
		$this->_dbconn_ = @mysql_connect($this->_host_, $this->_user_, $this->_pass_);
		@mysql_select_db($this->_db_);
		$this->stable($table);
		ini_set("date.timezone", $this->_time_zone_);
	}
	
	public function getall() {
		$dbs = mysql_list_dbs($this->_dbconn_);
		$db_array = array();
		$d = 0;
		while ($d < mysql_num_rows($dbs)) {
			$thisdb = mysql_tablename($dbs, $d);
			$db_array[$thisdb] = $this->gtables($thisdb);
			$d++;
		}
		$this->setdb($this->_db_);
		return $db_array;
	}
	
	public function gtables($db = "") {
		if ($db == "") { $db = $this->_db_;}
		$dbtables = array();
		$tbs = mysql_list_tables($db);
		$t = 0;
		while ($t < mysql_num_rows($tbs)) {
			array_push($dbtables, mysql_tablename($tbs, $t));
			$t++;
		}
		return $dbtables;
	}
	
	public function setdb($name) {
		if ($name != "") {
			$this->_db_ = $name;
			mysql_select_db($name);
		} else {
			return 0;
		}
	}
	
	public function getdb() {
		return $this->_db_;
	}
	
	public function stable($name) {
		if ($name != "") {
			$this->_table_ = $name;
		} else {
			return 0;
		}
	}
	
	public function gtable() {
		return $this->_table_;
	}
	
	public function setprime($prime) {
		if ($prime != "") {
			$this->_prime_ = $prime;
			return 1;
		} else {
			return 0;
		}
	}
	
	public function getprime() {
		return $this->_prime_;
	}
	
	public function columns($table = "") {
		if ($table == "") { $table = $this->_table_;}
		$r = $this->toarray($this->execute("SHOW columns FROM $table"));
		$cols = array();
		for ($i = 0; $i < count($r); $i++) {
			array_push($cols, $r[$i]["Field"]);
		}
		return $cols;
	}
	
	public function all($col = "", $dir = "ASC", $table = "") {
		return $this->select("*", null, $col, $dir, "", "", $table);
	}
	
	public function select($cols = "*", $cond = null, $sorter = "", $dir = "ASC", $op = "=", $sep = "AND", $table = "") {
		$dir = strtoupper($dir);
		if ($table == "") { $table = $this->_table_;}
		if ($sorter == "") { $sorter = $this->_prime_;}
		if ($cols == "") { $cols = "*";}
		if ($dir == "") { $dir = "ASC";}
		if ($op == "") { $op = "=";}
		if ($sep == "") { $sep = "AND";}
		$columns = "";
		if (is_array($cols)) {
			foreach ($cols as $item) {
				$columns .= "$item, ";
			}
			$columns = substr($columns, 0, strlen($columns) - 2);
		} else {
			$columns = $cols;
		}
		$sql = "SELECT $columns FROM $table ";
		if ($cond != null) {
			$sql .= "WHERE ";
			foreach ($cond as $col => $val) {
				$sql .= "$col $op {$this->sqlstr($val)} $sep ";
			}
			$sql = substr($sql, 0, strlen($sql) - (strlen($sep) + 1));
		}
		$sql .= "ORDER BY $sorter $dir";
		return $this->toarray($this->execute($sql));
	}
	
	public function search($value, $col = "", $table = "", $qtype = "LIKE") {
		if ($table == "") { $table = $this->_table_;}
		if ($qtype == "LIKE") {
			$wild = "%";
		} else {
			$wild = "";
		}
		if ($col == "") {
			$columns = array();
			$cols = $this->columns($table);
			for($i = 0; $i < count($cols); $i++) {
				$columns[$cols[$i]] = "$wild$value$wild";
			}
		} else {
			if (is_array($col)) {
				$columns = array();
				for($i = 0; $i < count($col); $i++) {
					$columns[$col[$i]] = "$wild$value$wild";
				}
				$col = $col[0];
			} else {
				$columns = array($col => "$wild$value$wild");
			}
		}
		return $this->select("*", $columns, $col, "asc", $qtype, "OR");
	}
	
	public function toarray($dbresult) {
		$tablerows = array();
		$index = 0;
		if (@mysql_num_rows($dbresult) != 0) {
			while ($row = mysql_fetch_assoc($dbresult)) {
				$tablerows[$index] = $row;
				$index++;
			}
			return $tablerows;
		} else {
			return 0;
		}
	}
	
	public function insert($array, $table = "") {
		if ($table == "") {$table = $this->_table_;}
		if (is_array($array[0])) {
			$result = 0;
			for ($i = 0; $i < count($array); $i++) {
				$resutl = $this->sqlinsert($array[$i], $table);
			}
			return $result;
		} else {
			return $this->sqlinsert($array, $table);
		}
	}
	
	protected function sqlinsert($array, $table) {
		$sql = "INSERT INTO $table VALUES (";
		for ($i = 0; $i < count($array); $i++) {
			if ($i == count($array) - 1) {
				$sql .= $this->sqlstr($array[$i]) . ")";
			} else {
				$sql .= $this->sqlstr($array[$i]) . ", ";
			}
		}
		return $this->execute($sql);
	}
	
	public function sqlstr($var) {
		if (is_string($var)) {
			if (substr($var, 0, 1) != "'" && substr($var, 0, 10) != "password('") {
				$var = "'$var'";
				return $var;
			} else {
				return $var;
			}
		} else {
			return $var;
		}
	}
	
	public function update($array, $condition, $table = "") {
		if ($table == "") {$table = $this->_table_;}
		$sql = "UPDATE $table SET ";
		foreach ($array as $col => $val) {
			$sql .= "$col = {$this->sqlstr($val)}, ";
		}
		$sql = substr($sql, 0, strlen($sql) - 2) . " WHERE ";
		foreach ($condition as $col => $val) {
			$sql .= "$col = {$this->sqlstr($val)} AND ";
		}
		$sql = substr($sql, 0, strlen($sql) - 5);
		return $this->execute($sql);
	}
	
	public function delete($col = "", $value, $reorder = true) {
		if ($col == "") {$col = $this->_prime_;}
		$r = $this->execute("DELETE FROM " . $this->_table_ . " WHERE $col = $value");
			if ($reorder == true) {
				$this->reset();
			}
		return $r;
	}
	
	public function authorize($user, $pass) {
		$r = $this->execute("SELECT * FROM " . $this->_authtable_ . " WHERE user = '$user' AND pass = password('$pass')");
		if (mysql_num_rows($r) == 1) {
			return mysql_fetch_assoc($r);
		} else {
			return 0;
		}
	}
	
	public function addauthuser($array, $pindex = 1) {
		$array[$pindex] = "password({$this->sqlstr($array[$pindex])})";
		$r = $this->insert(array_merge(array($this->rows("SELECT * FROM " . $this->_authtable_) + 1), $array), "users");
		if ($r == 1) {
			return 1;
		} else {
			return 0;
		}
	}
	
	public function updateuser($array, $condition) {
		return $this->update($array, $condition, $this->_authtable_);
	}
	
	public function chgpass($oldusr, $oldpass, $newusr, $newpass) {
		$user = $this->authorize($oldusr, $oldpass);
		if ($user != false) {
			return $this->update(
				array("user" => $newusr, "pass" => "password({$this->sqlstr($newpass)})"),
				array($this->_prime_ => $user[$this->_prime_]),
				$this->_authtable_);
		} else {
			return 0;
		}
	}
	
	public function deluser($id) {
		$r = $this->execute("DELETE FROM " . $this->_authtable_ . " WHERE " . $this->_prime_ . " = $id");
		$this->reset($this->_authtable_);
		return $r;
	}
	
	public function addsetting($array) {
		return $this->insert($array, $this->_settings_);
	}
	
	public function getsettings($type = "array") {
		$type = strtolower($type);
		$assoc = $this->all("", "", $this->_settings_);
		if ($assoc != false) {
			if ($type == "array") {
				return $assoc;
			} else {
				return json_encode($assoc);
			}
		} else {
			return 0;
		}
	}
	
	public function savesetting($id, $val) {
		return $this->update(array("value" => $val), array("id" => $id), $this->_settings_);
	}
	
	public function tocss($file, $css = null) {
		if ($css == null) { $css = $this->getsettings();}
		$cssfile = "";
		$l = "{";
		$r = "}";
		for ($i = 0; $i < count($css); $i++) {
			$sel = $css[$i]["selector"];
			if ($sel != "") {
				$name = $css[$i]["name"];
				$val = $css[$i]["value"];
				$cssfile .= <<<HERE
$sel $l
	$name: $val !important;
$r

HERE;
			}
		}
		$this->write($file, $cssfile);
	}
	
	public function shift($start = 1, $table = "") {
		$this->reset($table);
		$r = $this->all("", $table);
		for ($i = count($r) + 1; $i > $start; $i--) {
			$this->update(array($this->_prime_ => $i), array($this->_prime_ => $i - 1), $table);
		}
	}
	
	public function reset($col = "", $table = "") {
		if ($table == "") {$table = $this->_table_;}
		if ($col == "") {$col = $this->_prime_;}
		$r = $this->all($col, "asc", $table);
		$idarray = array();
		for ($i = 0; $i < count($r); $i++) {
			array_push($idarray, $r[$i][$col]);
		}
		$lastid = 0;
		for ($a = 0; $a < count($idarray); $a++) {
			$old = $idarray[$lastid];
			$new = $a + 1;
			if ($old != $new) {
				$this->update(array($col => $new), array($col => $old), $table);
			}
			$lastid++;
		}
	}
	
	public function execute($sql = "", $table = "") {
		if ($table == "") {$table = $this->_table_;}
		if ($sql == "") {$sql = "SELECT * FROM $table";}
		if ($this->_showqueries_ == true) {
			print $sql;
		}
		if ($this->_debug_ == true) {
			$r = mysql_query($sql, $this->_dbconn_);
			print mysql_error();
			return $r;
		} else {
			return mysql_query($sql, $this->_dbconn_);
		}
	}
	
	public function rows($sql = "", $table = "") {
		return mysql_num_rows($this->execute($sql, $table));
	}
	
	/* File System functions */
	
	public function read($file, $type = "", $csvdelim = "") {
		if ($csvdelim == "") { $csvdelim = $this->_csvdelim_;}
		if ($this->_useauto_ == true && $type == "") {
			$ext = substr($file, strlen($file) - 3);
			if ($ext == "xml") $type = $ext;
			elseif ($ext == "doc") $type = $ext;
			elseif ($ext == "pdf") $type = $ext;
			elseif ($ext == "csv") $type = $ext;
			elseif ($ext == "zip") $type = $ext;
			elseif ($ext == "ocx") $type = "docx";
			elseif ($ext == "slx") $type = "xslx";
		}
		$type = strtolower($type);
		if ($type == "") { $type = "string";}
		$fcontents = "";
		if ($this->is($file)) {
			$f = file($file);
			if ($type == "array") {
				$fcontents = $f;
			} elseif ($type == "string") {
				foreach ($f as $line) {
					$fcontents .= $line;
				}
			} elseif ($type == "xml") {
				$fcontents = simplexml_load_file($file);
			} elseif ($type == "doc") {
				if ($this->is($this->_antiword_)) {
					$fcontents = shell_exec("{$this->_antiword_} " . $file . " -t");
				} else {
					return 0;
				}
			} elseif ($type == "pdf") {
				if ($this->is($this->_xpdf_)) {
					$fcontents = shell_exec("{$this->_xpdf_} " . $file . " -");
					// remove extra characters at the end of the file:
					$fcontents = substr($fcontents, 0, strlen($fcontents) - 2);
				} else {
					return 0;
				}
			} elseif ($type == "csv") {
				$f = fopen($file, "r");
				$i = 0;
				while ($row = fgetcsv($f, filesize($file), $csvdelim)) {
					$fcontents[$i] = $row;
					$i++;
				}
				fclose($f);
			} elseif ($type == "zip") {
				$zip = new ZipArchive;
				$r = $zip->open($file);
				if ($r == true) {
					$name = uniqid();
					$name = str_replace(".", "", $name);
					@mkdir($name);
					$zip->extractTo($name);
					$zip->close();
					$fcontents = $name;
				} else {
					$fcontents = 0;
				}
			} elseif ($type == "docx") {
				$d = $this->read($file, "zip");
				if ($d != 0) {
					$fcontents = $this->read($d . "/word/document.xml", "string");
					//print "<pre>" . str_replace("<", "&lt;", str_replace(">", ">\r\n", $fcontents)) . "</pre>";
					// format paragraphs
					$fcontents = preg_replace("/<w:p\b.*?>/", "<p>\r\n", $fcontents);
					$fcontents = str_replace("</w:p>", "\r\n</p>\r\n", $fcontents);
					$fcontents = str_replace("<p>\r\n<p>", "<p>", $fcontents);
					// add bold, italic, and underline formatting
					$fs = array("b", "i", "u");
					foreach ($fs as $f) {
						$regx = array("/<w:r w:rsidRPr=\".{8}\"><w:rPr><w:$f.*?\/><\/w:rPr><w:t>.*?<\/w:t><\/w:r>/",
								"/<w:r><w:rPr><w:$f.*?\/><\/w:rPr><w:t>.*?<\/w:t><\/w:r>/");
						foreach ($regx as $reg) {
							preg_match($reg, $fcontents, $reps);
							//print_r($reps);
							foreach ($reps as $match) {
								$fcontents = str_replace($match, "<$f>" . strip_tags($match) . "</$f>", $fcontents);
							}
						}	
					}
					// add images
					if (count($this->getfiles("$d/word/media/")) != 0) {
						$imgs = "docx_images/$d/";
						if ($this->is("docx_images") == false) {
							mkdir("docx_images");
						}
						mkdir($imgs);
						$this->fcopy("$d/word/media/", $imgs);
						$is = $this->getfiles($imgs);
						for ($i = 0; $i < count($is); $i++) {
							$r = $i + 1;
							$fcontents = str_replace("<wp:docPr id=\"$r\" name=\"Picture $r\"/>", "<img src=\"$imgs$is[$i]\" />", $fcontents);
						}
						$fcontents = preg_replace("/<wp(14)*:(posOffset|pctHeight|pctWidth)>\d+<\/wp(14)*:(posOffset|pctHeight|pctWidth)>/", "", $fcontents);
						//$fcontents = str_replace("<wp14:pctHeight>0</wp14:pctHeight>", "", $fcontents);
						//$fcontents = str_replace("<wp14:pctWidth>0</wp14:pctWidth>", "", $fcontents);
					}
					$fcontents = strip_tags($fcontents, "<p></p><b></b><i></i><u></u><img>");
					$this->del($d);
				} else {
					return 0;
				}
			}
			return $fcontents;
		} else {
			return 0;
		}
	}
	
	public function write($file, $contents, $csvdelim = "") {
		if ($csvdelim == "") { $csvdelim = $this->_csvdelim_;}
		$f = fopen($file, "w+");
		if (is_array($contents)) {
			foreach ($contents as $line) {
				fputcsv($f, $line, $csvdelim);
			}
		} else {
			fputs($f, $contents);
		}
		fclose($f);
	}
	
	public function ren($file1, $file2) {
		if ($this->is($file1)) {
			return rename($file1, $file2);
		} else {
			return 0;
		}
	}	
	
	public function del($file, $dir = "") {
		if (substr($file, 0, 1) == "/" && substr($file, strlen($file) - 1) == "/") {
			$files = $this->getfiles($dir, $file);
			for ($i = 0; $i < count($files); $i++) {
				$this->delfile($dir . $files[$i]);
			}
		} else {
			return $this->delfile($file);
		}
	}
	
	protected function delfile($file) {
		if (is_dir($file)) {
			if ($this->_dir_ == "") {
				$this->_dir_ = $file;
			} else {
				if (strpos($file, $this->_dir_) == false) {
					$this->_dir_ .= $file;
				}
			}
			$f = $this->getfiles($file);
			for ($i = 0; $i < count($f); $i++) {
				$sep = "/";
				if (substr($file, strlen($file) - 1) == "/" || substr($file, strlen($file) - 1) == "\\") {
					$sep = "";
				}
				$this->del($file . $sep . $f[$i]);
			}
			$this->_dir_ = substr($this->_dir_, 0, (strlen($this->_dir_) - strlen($file)));
			rmdir($file);
		} else {
			return unlink($file);
		}
	}
	
	public function fcopy($source, $dest) {
		if ((substr($dest, 0, 2) == ".." || strpos($dest, ".") == false)  && $this->is($dest) == false) {
			@mkdir($dest);
		}
		if ((is_dir($source) && is_dir($dest))) {
			$ftc = $this->getfiles($source);
			for ($i = 0; $i < count($ftc); $i++) {
				if (substr($source, strlen($source) - 1) != "/") {
					$source .= "/";
				}
				if (substr($dest, strlen($dest) - 1) != "/") {
					$dest .= "/";
				}
				$this->fcopy($source . $ftc[$i], $dest . $ftc[$i]);
			}
		} else {
			return copy($source, $dest);
		}
	}
	
	public function is($file) {
		return file_exists($file);
	}
	
	public function getfiles($dir = "./", $regexp = "", $sorted = true) {
		if ($dir == "") { $dir = "./";}
		$dirs = array();
		$files = array();
		$d = @opendir($dir);
		if ($d != false) {
			while ($file = @readdir($d)) {
				if ($file != "." && $file != "..") {
					if (strpos($file, ".") == false) {
						array_push($dirs, $file);
					} else {
						array_push($files, $file);
					}
				}
			}
			@closedir($d);
			if ($sorted) {
				sort($dirs);
				sort($files);
			}
			$files = array_merge($dirs, $files);
			if ($regexp != "") {
				$files = preg_grep($regexp, $files);
				sort($files);
			}
			if (count($files) > 0) {
				return $files;
			} else {
				return -1;
			}
		} else {
			return 0;
		}
	}
	
	public function getpath($filename, $seg = "") {
		if (is_array($filename)) {
			return implode("/", $filename);
		} else {
			$filename = str_replace("\\", "/", $filename);
			$fp = explode("/", $filename);
			if ($seg == "") {
				return $fp;
			} elseif ($seg < 0) {
				return $fp[(count($fp) - 1) + $seg];
			} else {
				return $fp[$seg];
			}
		}	
	}
	
	public function filename($string) {
		$string = strtolower($string);
		$string = str_replace(" ", "-", $string);
		$string = str_replace('"', "'", $string);
		$string = str_replace("/", "-", $string);
		$string = str_replace("\\", "-", $string);
		$string = str_replace("?", "-", $string);
		$string = str_replace("<", "-", $string);
		$string = str_replace(">", "-", $string);
		$string = str_replace(":", "-", $string);
		$string = str_replace("|", "-", $string);
		$string = str_replace("*", "-", $string);
		$string = str_replace("--", "-", $string);
		return $string;
	}
	
	/* Miscellaneous function to make life easier. ;) */
	
	public function get($var, $type = "post") {
		$type = strtolower($type);
		if ($type == "post") {
			return trim(mysql_real_escape_string(filter_input(INPUT_POST, $var)));
		} else {
			return trim(mysql_real_escape_string(filter_input(INPUT_GET, $var)));
		}
	}
	
	public function datestring($date) {
		$a = explode("/", $date);
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		
		$dayofweek = date('l', strtotime($date));
		
		$suffix = "th";
		if ($a[1] == "1") {
			$suffix = "st";
		} elseif ($a[1] == "2") {
			$suffix = "nd";
		} elseif ($a[1] == "3") {
			$suffix = "rd";
		}
		return $dayofweek . ", " . $months[$a[0] - 1] . " " .  $a[1] . "<sup>$suffix</sup>, 20" . $a[2];
	}
	
	public function cookie($name, $val = "g") {
		if ($val == "g") {
			if (isset($_COOKIE[$name])) {
				return $_COOKIE[$name];
			} else {
				return "";
			}
		} elseif ($val == "d" || $val == "") {
			setcookie($name, "", time() - 10, "/", "", 0);
			$_COOKIE[$name] = "";
			return true;
		} else {
			setcookie($name, $val, time() + $this->_cookiedie_, "/", "", 0);
			$_COOKIE[$name] = $val;
			return true;
		}
	}
	
	public function escregx($string, $mods = "") {
		if (substr($string, 0, 1) != "/" && substr($string, strlen($string) - 1) != "/") {
			$string = str_replace("\\", "\\\\", $string);
			$string = str_replace("?", "\?", $string);
			$string = str_replace("/", "\/", $string);
			$string = str_replace("}", "\}", $string);
			$string = str_replace("{", "\{", $string);
			$string = str_replace("*", "\*", $string);
			$string = str_replace("+", "\+", $string);
			$string = str_replace(".", "\.", $string);
			$string = str_replace("|", "\|", $string);
			$string = str_replace("(", "\(", $string);
			$string = str_replace(")", "\)", $string);
			$string = str_replace("[", "\[", $string);
			$string = str_replace("]", "\]", $string);
			$string = str_replace("^", "\^", $string);
			$string = str_replace("\$", "\\$", $string);
			/*$string = str_replace("]", "\]", $string);
			$string = str_replace("]", "\]", $string);
			$string = str_replace("]", "\]", $string);*/
			$string = "/$string/$mods";
		}
		return $string;
	}
}
?>