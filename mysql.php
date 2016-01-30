<?php
	#access database here.
	$db = new mysqli('localhost','root','dbpass','recipes');
	#if error in connecting
	if($db->connect_errno > 0) {
		die('Unable to connect to database [' . $db->connect_error . ']');
	}	
	

	#grab a row from the database based on name and return its title,id,and category number.
	function select($db,$id) {
		
		#Prepare sql statement.
		$query = $db->prepare("SELECT * FROM recipestbl WHERE id = ?");
		#Bind data to placeholder in statement.
		$query->bind_param("i",$id);
		#execute prepared statement.
		$query->execute();
		#bind results
		$query->bind_result($recipeid,$title,$ingredients,$instructions,$category);
		
		#fetch values
		while($query->fetch()) {
			$row_arr = array(
				'title'=>$title,
				'ingredients'=>$ingredients,
				'instructions'=>$instructions,
				'category'=>$category,
				'id'=>$recipeid
			);
			#return the title,id,and category for display on the webpage.
			#If your wondering why I didn't just grab the field values I wanted to send in the $row_arr above,it's because the mysqli_result::fetch() method needs to grab
			#all values returned from a query.
			$final_array = array(
				'title'=>$row_arr['title'],
				'id'=>$row_arr['id'],
				'category'=>$row_arr['category']
			);
			echo json_encode($final_array);
		}		
	}	
	
	
	#insert a new row in the recipestbl.
	function insert_data($db,$message) {
		#gather variables we'll need.
		$safe_title = $db->real_escape_string($message["title"]);
		$safe_ingredients = $db->real_escape_string($message["ingredients"]);
		$safe_instructions = $db->real_escape_string($message["instructions"]);
		$cat_id = "nothing";
		#determine which cat_id(table column)to assign to the new recipe row.
		switch($message["category"]) {
			case "meat":
				$cat_id = 4;
				break;
			case "veggies":
				$cat_id = 5;
				break;
			case "bread":
				$cat_id = 6;
				break;	
			default:
				$db->close();
				die("Invalid category value.");
				break;
		}
		
		#now let's use a prepared statement to use dynamic data in an INSERT INTO query.
		$sql = $db->prepare("INSERT INTO recipestbl VALUES(NULL,?,?,?,?)");
		$sql->bind_param("sssi",$safe_title,$safe_ingredients,$safe_instructions,$cat_id);
		$sql->execute();
		$recent_insert = $db->insert_id;
		select($db,$recent_insert);
		$sql->free_result();
		
	}
	
	#Get the information about a specific recipe.
	function get_recipe($db,$message) {
		$safe_id = $db->real_escape_string($message["id"]);
		$recipe_id = intval($safe_id);
		
		#Prepare sql statement.
		$query = $db->prepare("SELECT * FROM recipestbl WHERE id = ?");
		
		#Bind data to placeholder in statement.
		$query->bind_param("i",$recipe_id);
		
		#execute prepared statement.
		$query->execute();
		
		#bind results
		$query->bind_result($recipeid,$title,$ingredients,$instructions,$category);
		
		#fetch values
		while($query->fetch()) {
			$row_arr = array(
				'title'=>$title,
				'ingredients'=>$ingredients,
				'instructions'=>$instructions,
				'action'=>'getRecipe'
			);
			echo json_encode($row_arr);
		}		
	}
	

	
	/*DIVIDE*/

	#parse contents of json object and determine what function to call.
	$request = file_get_contents("php://input");
	$message = json_decode($request,true);
	switch($message["action"]) {
		case "insert":	#create a new row in the recipestbl table from form data.
			insert_data($db,$message);
			break;
		case "getRecipe":	#get the ingredients and instructions of a specific recipe when the user clicks on it.
			get_recipe($db,$message);
			break;
		default:
			//do nothing
			//break;			
	}
?>