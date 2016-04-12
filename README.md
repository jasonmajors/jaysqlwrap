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
SELECT queries
-------------
The select method takes 2 arguements: The name of the table as a string, and an array of the conditions. The results will be returned as an array.
For example:
```php
$conditions = array('username' => 'jasonmajors', 'language' => 'php');
$results = $db->select('table', $conditions);
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
