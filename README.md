# php-database-library

PHP library for easy access to MySQL databases and the file system - or at least that's what it used to be!
It has become sort of a melting pot of everything now... ;)

###WARNING: This is still a work in progress, so be carefull when updating!!

This project was begun by Justin Eldracher in 2015.  Feel free to do whatever you want to this code,
just please let me know what you changed/added so I don't miss out on anything awesome! ;)
If you find any errors or would like to make comments, please leave a message at:
http://blindwarrior.16mb.com/writemsg.php

##Changes:
- **6/20/15:** Added method "cookie" along with the variable `$_cookiedie_`
- **6/25/15:** Added method "gtables" which returns an array of all the tables in a database.  Subset of `getall()`.
- **6/30/15:** Revised `del()` method to delete directories and set a default on `getfiles()` to list the current
directory if none is specified.  Also added a third parameter to getfiles() which you can set to false if you don't want
the array of files (and directories) to be sorted.
- **7/3/15:** Added ability to read zip archives and docx files to the `read()` function, and modified `getfiles()` to return the directories
at the beginning of the array, just like a file manager.  Also added the `$_useauto_` variable for `read()`
- **7/7/15:** Added the ability to process basic formatting in docx files, but it's not perfect yet.  Added the `fcopy()` method which can
copy both files and directories.
- **7/9/15:** Added the `getpath()` and `escregx()` methods.
- **8/29/15:** Added the `filename()` method to convert a string into a "safe" filename.
- **9/10/15:** Revised the `del()` method to accept a regular expression and added an optional parameter for the directory to delete from
 when using the regular expression feature.
- **11/10/15:** Revised `search()` method to accept a fourth parameter, the match type.  ("LIKE" or "REGEXP" for example)
- **1/13/16:** Modified `reset()` function to be able to reset any column instead of just the primary key.
- **2/14/16:** Improved the `datestring()` function to return the day of the week, as well as the superscript after the day.  Added the `$_time_zone_` variable
to set a default timezone for all of PHP's date & time functions.  FINALLY added a public `$_version_` variable too. ;)
- **2/29/16:** Version 1.0.1, added the `trim()` function to the results of the `get()` method to clean out extra spaces before and after.
- **3/2/16:** Version 1.0.2, added `destroy()` method to close the database connection, and set this function to be automatically called on script shutdown.

## Method List:

####init([default table]);
Initializes the database connection and selects a database.  Sets the table also, if one is given as a parameter.
```php
$db = new DB("my_table");
```

####getall();
Returns an associative matrix of the databases and their tables on the current MySQL connection.
```php
print_r($db->getall());
```

####gtables([db_name]);
Returns an array of the tables in the given database or the active one, if no parameter is given.
```php
print_r($db->gtables());
```

####setdb(new_database_name);
Change the active database.
```php
$db->setdb("new_database");
```

####getdb();
Returns the name of the active database.
```php
print $db->getdb();
```

####stable(new_table_name);
Change the active table.
```php
$db->stable("different_table");
```

####gtable();
Returns the name of the active table.
```php
print $db->gtable();
```

####setprime(new_primary_key);
Changes the default Primary Key.
```php
$db->setprime("colA");
```

####getprime();
Returns the current default Primary Key.
```php
print $db->getprime();
```

####columns([custom table]);
Returns an array of the column names in a table, default to active table of course.
```php
print_r($db->columns());
```

####all([column to sort by], [sort order], [custom table]);
Returns either the entire active table or a custom table as a matrix.
```php
print_r($db->all());
print_r($db->all("date", "desc", "posts"));
```

####select([columns to select, either string or array], [assoc array for WHERE], [column to sort by], ["ASC" or "DESC"], ["=", "<="...], ["AND", "OR", "NOT"] [custom table]);
Returns a matrix for a SELECT statement, default is the entire table.  Has quite a bit of power... ;)
```php
$db->select("firstname, lastname", array("custype" => "new"), "firstname", "asc", "customers");
$db->select(array("firstname", "lastname", "dob"), array("custype" => "new", "custype" => "old"), "firstname", "desc", "", "OR", "customers");
// results in: "SELECT firstname, lastname, dob FROM customers WHERE custype = 'new' OR custype = 'old' ORDER BY firstname DESC";
```

####search(text_to_find, [column(s) to look in, either string or array], [custom table], [match type]);
Returns a matrix of the matches found for the first variable.  The second paramter can be a string of one column name, or an array of column names to look in.  The default is to search in all the columns of the table.
```php
print_r($db->search("Hello!"));
$db->search("Hello!", "greeting");
$db->search("Hello!", array("greeting", "firstname"));
$db->search("^Hello!.*", array("greeting", "firstname"), "info", "REGEXP");
```

####toarray(mysql_result);
Returns a matrix of a db result.
```php
print_r($db->toarray($db->execute()));
```

####insert(array_or_matrix_of_values, [custom table]);
Inserts an array or matrix into the active or custom table.
Array length must match the number of columns in the table.
```php
$db->insert(array(1, "Hello!", "Goodbye!"));
$db->insert($db->read("test.csv", "csv"));
```

####sqlstr(mixed_var);
Adds single quotes around a string variable if it doesn't have them already.
All variables used in SQL queries are run through this function automatically.
```php
$db->sqlstr("Justin Eldracher"); //returns "'Justin Eldracher'"
```

####update(assoc_array_values_to_change, assoc_array_for_WHERE_statement, [custom table]);
Updates a custom table or the active one using two associative arrays.
```php
$db->update(array("firstname" => "Justin", "greeting" => "Hi!"), array("parting" => "Goodbye!"));
```

####delete([identifier column, default to _prime_ variable], value_to_match, [boolean, default true]);
Deletes a row based on the value of the first parameter.
```php
$db->delete("id", 1, true); // runs $db->reset() afterwards if 3rd parameter is true
```

####authorize(user_name, password);
Checks if the given username and password are in the users table and unique.
If so, returns an associative array of that row of the users table, otherwise returns false.
```php
$db->authorize("justin", "super_secret");
```

####addauthuser(array_of_values, [custom index for password, default 1]);
Adds an array of values to the users table.  First parameter MUST NOT include a Primary Key as the first item.
Second parameter is the index of the password in the array of values.
```php
$db->addauthuser(array("Justin", "super_secret", "my_email", "http://blindwarrior.16mb.com"));
```

####updateuser(assoc_array_values_to_change, assoc_array_for_WHERE_statement);
Updates a record in the users table.
```php
$db->updateuser(array("user" => "justin", "pass" => "not-so-secret"), array("id" => 1));
```

####chgpass(current_user, current_password, new_user, new_password);
Updates only the username and password, if given username and password already exist.
```php
$db->chgpass("justin", "not-so-secret", "justin-eldracher", "extra-super-duper-secret");
```

####deluser(numeric_id);
Deletes a user.
```php
$db->deluser(1);
```

####addsetting(array_of_values);
Inserts a row into the settings table.
```php
$db->addsetting(array(1, "font-family", "Times New Roman", "Page Font:", "*"));
```

####getsettings(["array" or "json", default "array"]);
Returns either an associative array of the settings from the settings table or a json string.
```php
print_r($db->getsettings());
print $db->getsettings("json");
```

####savesetting(id, new_value);
Changes the value of a setting by it's id.
```php
$db->savesetting(1, "Georgia");
```

####tocss(output_file_name, [matrix of values, default is settings table])
Takes a matrix and turn it into a css file, written to specified file path.
```php
$db->tocss("css/settings.css", $db->getsettings());
```

####shift([starting id, default 1], [custom table]);
Shifts all rows of a table down, starting at the primary key specified.
```php
$db->shift(1, "customers"); // now we could $db->insert(array(1, ...), "customers")
```

####reset([column], [custom table]);
Resets a certain column of the specified table, by default the primary key and the active table.
Only works for numerical fields, obviously.  Ideal for keeping the highest primary key equal to the number of records
in the database.

For example:

| id | name |
|:--:|:----:|
|1|Justin|
|2|Frank|
|5|Michael|
|7|Joseph|

After the `reset()` method is called becomes this:

| id | name |
|:--:|:----:|
|1|Justin|
|2|Frank|
|3|Michael|
|4|Joseph|

```php
$db->reset();
```

####execute([sql query], [custom table]);
Executes a given SQL query or a select all by default.
```php
$db->execute("SELECT * FROM customers ORDER BY id DESC");
```

####rows([sql query], [custom table]);
Returns the number of rows in an sql query, default is the entire table.
```php
$db->rows("SELECT * FROM users");
$db->rows("", "customers");
```

###File System Functions:

####read(file_name, [return type], [custom delimeter for type "csv"]);
Reads a file and return contents based on value of the second parameter, which can be:
"string", "array", "xml", "doc", "pdf", "csv", "zip" or "docx".  Default is "string".
If the `$_useauto_` variable is set to true, though, you don't usually need to give a second parameter
Note: requires Antiword for reading Word documents and XPDF for those documents.
When reading zip archives, the method returns the name of the folder that was created with the
contents of the archive.
```php
print $db->read("test.txt");
print $db->read("test.doc", "doc");
print_r($db->read("test.csv", "csv", ",");
print_r($db->getfiles($db->read("test.zip", "zip")));
```

####write(file_name, file_contents, [custom delimeter for csv files]);
Writes content to a file. If content is an array, file will be saved in delimited format.
```php
$db->write("demo.txt", $db->all(), "\t");
```

####ren(old_name, new_name);
Renames a file.
```php
$db->ren("demo.txt", "demo1.txt");
```

####del(file_name, [directory]);
Deletes a file or directory. file_name can also be a regular expression, and when it is the method will delete all the files
in the current directory or the one specified in the second parameter that match the regular expression.
```php
$db->del("demo1.txt");
$db->del("demo/");
```

####fcopy(source, destination);
Copies a file or folder.
```php
$db->fcopy("demo1.txt", "demo2.txt");
$db->fcopy("demo/", "demo2/");
```

####is(file_name);
Simplify file exists.
```php
$db->is("demo1.txt"); // = false, now... ;)
```

####getfiles([directory, defaults to root], [regular expression for desired files], [boolean for sorted, default true]);
Returns an array of all the files and directories in a directory based on a regular expression for desired files.
If no parameters are given, will return the contents of the directory containing your script.
```php
$db->getfiles("media/", "/\.mp3$|\.wav$/");
```

####getpath(file_name, [desired segment]);
Parses a file's path into segments by directory and returns an array, by default.
If an array is given, will convert it into a file path.
The second parameter is the index number of the desired fragment, can be negative to start at the end.
```php
print_r($db->getpath("C:/xampp/htdocs/DB/"));
print $db->getpath("C:/xampp/htdocs/DB/", 1); // returns "xampp"
print $db->getpath("C:/xampp/htdocs/DB/", -1); // returns "DB"
```

####filename(file_name);
Remove unwanted characters from a string to make a safe file name.
```php
print $db->filename("FILE <1> 8/29/15.TXT"); // file-1-8-29-15.txt
```

###Miscellaneous Functions:

####get(input_variable, [send method: "post" or "get", default "post"]);
Returns a variable sent through either POST or GET.
```php
$db->get("user-name");
$db->get("search-query", "get");
```

####datestring(date_string);
Returns: "Friday, April 3&lt;sup&gt;rd&lt;/sup&gt;, 2015" for "5/3/15".
```php
print $db->datestring("5/3/15");
```

####cookie(name, [value]);
Multipurpose function for dealing with cookies.  The three possible uses are listed below.
This method uses a "clever hack" ;) to get around a cookie's default behavior of not being available
until after the next http request.  Running the three statements below would print out the value as expected.
```php
$db->cookie("name", "Justin"); // Creates a cookie.
print $db->cookie("name"); // Returns the value of the name cookie.
$db->cookie("name", ""); // Deletes the name cookie.
```

####escregx(string, [modifiers, eg. "i"]);
Escapes all regular expression special characters with a backslash and wraps in forward slashes.
The the string is already wrapped in forward slashes, nothing happens.
```php
print $db->escregx("Do you (or anyone else) have $5.00 to lend me?", "i"); // Returns "/Do you \(or anyone else\) have \$5\.00 to lend me\?/i"
```

## Table Structure:

All tables are assumed to have a Primary Key column named "id".

The users table must have the first column for the Primary Key, the second column for the username (named "user"), and the third column for the password (named "pass").  Other columns may be added after the first three.
	
The Settings table is something I have found convenient for customizing cms systems.

| id | name | value | alias | selector |
|:--:|:----:|:-----:|:-----:|:--------:|
| 1 | color | #ff0000 | Text Color: | body |
| ... | ... | ... | ... | ... |... |

Coupled with `$db->tocss("settings.css")`, it provides an easy way for users to customize a web page.
A simple loop can print out all the settings in a form, and then a foreach loop through the imput vars saves them.
	
##Configuration:

```php
	$_dbconn_ // Stores MySQL connection:  DON'T TOUCH!! ;)
	$_host_ // MySQL hostname, obviously. ;)
	$_user_ // MySQL username, obviously. ;)
	$_pass_ // MySQL guess what? ;)
	$_db_ // Default database for queries.
	$_authtable_ // Table for storing user info.
	$_settings_ // Table for storing user profile settings.
	$_table_ // Current table for queries.
	$_prime_ // Name of column used as Primary Key in all tables.
	$_debug_ // Boolean whether or not to print mysql_error for queries.
	$_showqueries_ // Boolean whether or not to print out SQL queries, useful only when debugging. ;)
	
	$_useauto_ // Determines whether or not to try and detect file type from extension.  Used mostly in read();
	$_csvdelim_ // Default delimiter for CSV and other delimited formats.
	$_antiword_ // Path to Antiword executible, needed for reading Microsoft Word documents.
	$_xpdf_ // Path to XPDF executible, needed for reading PDF documents.
	$_dir_ // Variable used when recursively digging through a folder.  Don't remove it. ;)
	
	$_cookiedie_ // Milliseconds after current time for cookies to expire.  Default to 10 billion, or about 4 months.
	
	$_time_zone_ // Default time zone, mine is "America/Detroit"
```

For ease of updating and using on local and remote servers, save the following code
(with whatever additional configuration changes you want) as a new class.

```php
<?php
include "DB.php";

Class your_custom_name extends DB {
	public function __construct($table = "") {
		$this->_host_ = "localhost";
		$this->_user_ = "my_user_name";
		$this->_pass_ = "my_password";
		$this->_db_ = "default_db";
		$this->_debug_ = true;
		$this->_showqueries_ = true;
		$this->_time_zone_ = "Wherever/city";
		$this->_antiword_ = "C:/antiword/antiword.exe";
		$this->_xpdf_ = "C:/xpdf/bin32/pdftotext.exe";
		
		// Don't remove this line!
		$this->init($table);
	}
}
?>
```