<?php

////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////

/*                                                                                                                */

/*                                           This is not a free script                                            */

/*                              All rightes reserved to traxeed.net and traxeed.com                               */

/*                                                                                                                */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/**

 /////////////////////////////////////////////      Database class      ////////////////////////////////////////////



 It have all functions that relating to database.


 * 1. __construct :  to include the config file.

 * 2. _connect : open a connection to the database.

 * 3. get_count : count all rows of a table.

 * 4. get_count_q : count rows depend on quary.

 * 5. select : select a single value.

 * 6. selectone : select a single row.

 * 7. selectall : select multiple rows.

 * 8. insert : insert into database.

 * 9. update : update rows in the database.

 * 10. bind : bind 's' with parametars to use it in the quary.

 * 11. alter : alter the database to create or drop columns.

 * 12. delete : delete rows from the database.

 * 13. __destruct : close mysqli connection and free results
 */


class traxeed_mysqliDB {



	// Count functins return variable

	private $count;



	// Returning value from Select function

	private $value;



	// Returning row data array from Select One function

	private $data = array ();



	// Returning rows array from Select All function

	private $rows = array ();



	// Returning inserted id from insert function

	private $id;



	// Mysqli connection private variable

	private $mysqli;



	/*---------------------------- Including config ----------------------------

	 * The following function include confing file automatic when this class is requested

	 * - It include the file depend on your location when calling the db class.

	 * -- if you are in root folder of the system it will include in directrly.

	 * -- if you are in the admin folder it will go back and include it.

	 * */



	function __construct() {

		

		$configFile = 'config.php';

		

		$url = $_SERVER['PHP_SELF'];

		$array = explode("/", $url);

		if (in_array('admin', $array)) {

			require_once '../includes/config.php';

			

		}elseif (in_array('plugins', $array)) {

			require_once '../../includes/config.php';

			

		}elseif (in_array('webserviceapi', $array)) {

			require_once '../includes/config.php';

			

		}elseif (in_array('api', $array)) {

			require_once '../includes/config.php';

			

		}

		elseif ($url) {

			require_once 'includes/config.php';

		}

	}



	/*---------------------------- Connect to Database ----------------------------

	 * The following function open a connection to the data base.

	 * This function is private to this class only and cannot be called from outside this class.

	 *

	 * - It depends on the constant defined on config file

	 * -- HOST : Is the server that the system connect to.

	 * -- DBuser : Is the user used to login into the database server.

	 * -- DBpass : Is the password used to authenticate your login to the serer.

	 * -- DBname : It is the database name that the sysetem will connect to.

	 *

	 * This function return the connection object '$mysqli', or die with an error if

	 * the connection failed.

	 * */



	private function _connect() {

		$mysqli = new mysqli(HOST, DBuser, DBpass, DBname);
        //mysqli_set_charset($mysqli, "utf8mb4_general_ci"); 

		if ($mysqli) {

			return $mysqli;

		} elseif ($mysqli -> connect_error) {

			die('Connection failed : (' . $mysqli -> connect_errno . ') ' . $mysqli -> connect_error);

		}

	}



	/*---------------------------- Count rows of any table ----------------------------

	 * The following function return the total rows of any table.

	 * -- It accept one prametar '$table' and it's the table name need to be counted.

	 * -- It returns '$count' variable as an integer.

	 * */





	public function get_count($table = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare("SELECT COUNT(*) FROM " . $table);

		$stmt -> execute();

		$result = $stmt -> get_result();

		$get_total_rows = $result -> fetch_row();

		$count = $get_total_rows['0'];



		return $count;

	}



	/*---------------------------- Count rows depend on quary ----------------------------

	 * The following function return the total rows spacific rows depend on quary

	 * -- It accept the prametars '$query, $s, $params', and they are the quary to be executed

	 * the binded values types, and the binded values.

	 * -- It returns '$count' variable as an integer.

	 * */





	public function get_count_q($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}

		$stmt -> execute();

		$result = $stmt -> get_result();

		$get_total_rows = mysqli_num_rows($result);

		$count = $get_total_rows;



		return $count;

	}



	/*---------------------------- Select one row to an string ----------------------------

	 * The following function select one value from one row depend on quary

	 * -- It accept the prametars '$query, $s, $params', and they are the quary to be executed

	 * the binded values types, and the binded values.

	 * -- It returns '$value' of the requested single variable.

	 * */



	public function select($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		$stmt -> bind_param($s, $params);

		$stmt -> execute();

		$stmt -> store_result();

		if ($stmt -> num_rows == 1) {

			$stmt -> bind_result($value);

			$stmt -> fetch();

			return $value;

		} else

			return;

	}



	//---------------------------- Select one row to an array ----------------------------//



	public function selectone($query = '', $s = '', $params = '') {

		$results = array();

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}

		$stmt -> execute();

		$result = $stmt -> get_result();

		if ($stmt) {

			$data = $result -> fetch_assoc();

			return $data;

		} else

			return;

	}



	//---------------------------- Select All rows ----------------------------//



	public function selectall($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}

		$stmt -> execute();

		$result = $stmt -> get_result();

		if ($stmt) {

			$rows = array();

			while ($row = $result -> fetch_assoc()) {

				$rows[] = $row;

			}

			return $rows;

		} else

			return;

	}



	//---------------------------- Insert ----------------------------//



	public function insert($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}



		if ($stmt -> execute()) {

			$id = $db -> insert_id;

			return $id;

		} else

			return mysqli_error($db);

	}



	//---------------------------- Update ----------------------------//



	public function update($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}

		if ($stmt -> execute()) {

			return TRUE;

		} else

			return;

	}



	//---------------------------- Bind mutli params ----------------------------//



	public function bind($s = '', $params = '') {



		$bind_names[] = $s;

		for ($i = 0; $i < count($params); $i++) {

			$bind_name = 'bind' . $i;

			$$bind_name = $params[$i];

			$bind_names[] = &$$bind_name;

		}

		return $bind_names;

	}



	//---------------------------- Alter ----------------------------//



	public function alter($query) {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($stmt -> execute()) {

			return TRUE;

		} else

			return;

	}



	//---------------------------- Delete ----------------------------//



	public function delete($query = '', $s = '', $params = '') {

		$db = $this -> _connect();

		$stmt = $db -> prepare($query);

		if ($s && $params) {

			$return = call_user_func_array(array($stmt, 'bind_param'), $this -> bind($s, $params));

		}

		if ($stmt -> execute()) {

			return TRUE;

		} else

			return;

	}



	//---------------------------- Close database ----------------------------//



	public function __destruct() {
		return mysqli_close($this->_connect());

		return $result -> free();
	}



}



$traxeed_mysqliDB = new traxeed_mysqliDB();



////////////////////////////////////////////      Traxeed V. 1.0.0      ////////////////////////////////////////////

/*                                                                                                                */

/*                                           This is not a free script                                            */

/*                              All rightes reserved to traxeed.net and traxeed.com                               */

/*                                                                                                                */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

