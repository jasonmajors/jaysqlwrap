Jay SQL Wrap
============

A simple PHP class for implementing basic MySQL queries as prepared PDO statements.

Installation with Composer
--------------------------
```shell
curl -s http://getcomposer.org/installer | php
php composer.phar require jmajors/jaysqlwrap
```

Usage
-----
Include composer's autoloader:
```php
require __DIR__ . '/vendor/autoload.php';
```
Create a Jaywrap instance:
```php
$db = new Jaywrap\Jaywrap();
```
INSERT queries
--------------
The insert method has 2 parameters: The name of the table as a string, and an array of the data to be inserted. The keys for the data array need to match the column names of the table. For example:
```php
$data = array('username' => 'jasonmajors', 'password' => 'somehashedpassword', 'age' => 28, 'language' => 'php');
$success = $db->insert('users', $data);
```
SELECT queries
-------------
The select method has 2 parameters: The name of the table as a string, and an array of the conditions (optional). The results will be returned as an array.


Select all the items in a table:
```php
$results = $db->select('sometable');

print_r($results);
```
```
Array ( 
	[0] => Array ( 
			[columnX] => someValue 
			[0] => someValue 
	) 
	[1] => Array (
		 	[columnY] => someOtherValue 
		 	[0] => someOtherValue
	) 
)
```

SELECT with conditions:
```php
$conditions = array('username' => 'jasonmajors', 'language' => 'php');
$results = $db->select('users', $conditions);
```
The above would execute a prepared statement of:
```sql
SELECT * FROM table WHERE username = 'jasonmajors' AND language = 'php';
```
You can also pass an array as a value in the conditions array:
```php
$conditions = array('username' => array('jasonmajors', 'johndoe', 'janedoe'));
$results = $db->select('table', $conditions);
```
The above would execute:
```sql
SELECT * FROM table WHERE username IN ('jasonmajors', 'johndoe', 'janedoe');
```

UPDATE
------
The update method has 3 parameters: The name of the table as a string, an array of the update data, and an array of the conditions.
```php
$updates = array('language' => 'Python');
$conditions = array('username' => 'jasonmajors');
$success = $db->update('users', $updates, $conditions);
```