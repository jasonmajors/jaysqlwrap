# Jay SQL Wrap
A simple PHP class for creating and executing basic SQL prepared statements as object methods. This provides an easy way to interact with a database while staying safe from SQL injection.

## Installation with Composer
```shell
require jmajors/jaysqlwrap
```
See https://getcomposer.org/download/ for how to setup Composer.

## Usage
Add composer's autoloader to your project:
```php
require __DIR__ . '/vendor/autoload.php';
```
The wrapper will make a database connection upon instantiation using the database information in an application's .env file. If you don't have an .env file, you'll need to create one. See https://github.com/vlucas/phpdotenv. Note that this has been included with the wrapper, you'll just need to create the .env file and load it in your application:
```php
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
```

Create a Jaywrap instance:
```php
$db = new Jaywrap\Jaywrap();
```
### INSERT
The insert method has 2 parameters: The name of the table as a string, and an array of the data to be inserted. The keys for the data array need to match the column names of the table. For example:
```php
$data = array('username' => 'jasonmajors', 'password' => 'somehashedpassword', 'age' => 28, 'language' => 'php');
$success = $db->insert('users', $data);
```
### SELECT
The select method has 2 parameters: The name of the table as a string, and an array of the WHERE conditions (optional). 

Select all the items in a table:
```php
$results = $db->select('sometable');
```
The results will be returned as an array.
```php
print_r($results);

/*
 *	Array ( 
 *		[0] => Array ( 
 *				[columnX] => someValue 
 *				[0] => someValue 
 * 				[columnY] => someOtherValue
 *				[1] => someOtherValue
 *		) 
 *		[1] => Array (
 *			 	[columnX] => someOtherValueTwo 
 * 			 	[0] => someOtherValueTwo
 *			 	[columnY] => someOtherValueThree
 *			 	[1] => someOtherValueThree
 * 		) 
 *	)
 */
```

#### SELECT with conditions:
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

### UPDATE
The update method has 3 parameters: The name of the table as a string, an array of the update data, and an array of the WHERE conditions.
```php
$updates = array('language' => 'Python');
$conditions = array('username' => 'jasonmajors');
$success = $db->update('users', $updates, $conditions);
```

### DELETE
The delete method has 2 parameters: The name of the table as a string, and an array of the WHERE conditions.
```php
$delete = array('username' => 'jasonmajors');
$success = $db->delete('users', $updates, $conditions);
```