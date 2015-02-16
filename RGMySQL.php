<html>
<?php

ini_set('display_errors', 'On');

$mysqli = new mysqli("oniddb.cws.oregonstate.edu","guyerr-db","p0qhxwuK81XS0f5r", "guyerr-db");
if($mysqli->connect_errno){
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
else{
//	echo "Connection worked!<br>";
}

$create_table = "CREATE TABLE IF NOT EXISTS videos(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							name VARCHAR(255) UNIQUE,
							category VARCHAR(255),
							length INT,
							rented BOOL)";

$create_tbl = $mysqli->query($create_table);

//Used to delete a row
if(isset($_POST['remove'])){
	$postid = $_POST['deleteid'];
	$DeleteRow = "Delete FROM videos WHERE id = '$postid'";
	mysqli_query($mysqli, $DeleteRow);
};

//used to delete all the movide in the table
if(isset($_POST['deleteall'])){
	$DeleteRow = "Delete FROM videos WHERE 1";
	mysqli_query($mysqli, $DeleteRow);
};

//used to checkin/out
if(isset($_POST['checkinout'])){
	$postid = $_POST['deleteid'];
	$isrented = $_POST['deleterented'];
	if($isrented == 'Checked In'){
	$Check = "Update videos SET rented =0 WHERE id = '$postid'";
//	echo"Changed to False"; used to debug
//	echo"$isrented";
	}
	else{
	$Check = "Update videos SET rented =1 WHERE id = '$postid'";
//	echo "Changed to True"; used to debug
//	echo"$isrented";
	}

	mysqli_query($mysqli, $Check);
};


?>

<head>
  <meta charset = 'utf-8'>
  <title>Videos</title>
</head>

<body>
  <form action ="RGMySQL.php" method="post">
    <table border = '0'>
	<tr>
	   <td>Movie Title</td>
	      <td align = "center">
		<input type = 'text' name = "name"/>
	      </td>
	</tr>

	<tr>
	   <td>Movie Category</td>
	      <td align = "center">
		<input type = 'text' name = "category"/>
	      </td>
	</tr> 

 	<tr>
	   <td>Length (in Minutes)</td>
	      <td align = "center">
		<input type = 'number' name = "length"/>
	      </td>
	</tr>

 	<tr>
	   <td>
		<input type = 'Submit' name = "Add Movie" value = "Add Movie"/>
	   </td>
	</tr>
     </table>
  </form> 


<?php

if(isset($_POST['name'])){
	if(empty($_POST['name'])){
		echo "ERROR: You must enter something in the Movie Title Field<br></br>";
	}

	//I made it so the user had to enter something in the category field but the testing sheet
	//said that this is incorrect. I commented out the field but left the code in in case I want to revert back.
//	if(empty($_POST['category'])){
//		echo "ERROR: You must enter something in the Movie Category Field<br></br>";
//	}

	if(empty($_POST['length'])){
		echo "ERROR: You must enter something in the Length Field<br></br>";
	}

	if (!empty($_POST['length'])){
		$lengthcheck = $_POST['length'];
		if($lengthcheck <= 0){
			echo "ERROR: Movie length must be greater than zero";
		}
	}
}

//if all these posts are set and length is greater than zero then perform the following action.
if (isset($_POST['name'])&&!empty($_POST['name'])&&!empty($_POST['length'])&&($_POST['length'])>0){

	$name = $_POST['name'];
	$category = $_POST['category'];

	//I have set it so that if the user does not enter a category then the default is "Unknown".
	//The assignment instructions were not clear about the entry for this field but was claer that it should
	//not be empty
	if($category == NULL){
		$category = "Unknown";
	}

	$length = $_POST['length'];
	$rented =1; //default is checkedIn

	if(!($stmt = $mysqli->prepare("INSERT INTO videos (name, category, length, rented)  VALUES (?,?,?,?)"))){
		//echo "Prepare failed";
	}

	if(!($stmt->bind_param('ssii', $name, $category, $length, $rented))){
		//echo "Binding parameters failed";
	} 

	if(!($stmt->execute())){
		//echo "Execute failed";
	}
}

?>


<p>Here is where you can filter the videos: </p>

<?php
  $nameselection = $mysqli->query("SELECT * FROM videos");
	
	echo"<form action =RGMySQL.php method=post>";
  $nameselection = $mysqli->query("SELECT category FROM videos GROUP BY category");
	echo'<tr>';
	  echo '<td>Movie Category</td>';
	     echo '<td align = "center">';
		echo'<select name = "searchcategory">';
			echo "<option value = all>All</option>";
		while($record = mysqli_fetch_array($nameselection)){
			echo '<option value = "'. $record['category'] . '">' . $record['category'] . '</option>';
		}
		echo '</select>';

	      echo '</td>';
	echo '</tr>';

	echo'<tr>';
	   echo'<td>';
		echo"<input type = Submit name = Search value = 'Search Movies'/>";
	   echo'</td>';
	echo'</tr>';

	echo'</table>';
	echo'</form>';

if(isset($_POST['Search'])){
$categorytype = $_POST['searchcategory'];

echo "<br></br><br></br>";

	echo "<table style = 'width: 600px;'>";
	echo"<tr style = 'outline: thin solid;'>";
	  echo'<th>Movie Name</th>';
	  echo'<th>Movie Category</th>';
	  echo'<th>Movie Length</th>';
	  echo'<th>Status</th>';
	echo'</tr>';

if($categorytype != 'all'){
	$sql = "SELECT id, name, category, length, rented FROM videos WHERE category = '$categorytype'";
}
else{
	$sql = "SELECT id, name, category, length, rented FROM videos";
}
$mydata = mysqli_query($mysqli, $sql);
$result = $mysqli->query($sql);


//The below method for deleting rows using a form was found at the following:
//PHP Lesson 40 - Adding, Deleting, Updating, and Displaying Records.
//it basically creates a form for each entry and then uses a post to change or delete the data.
while($row = mysqli_fetch_array($mydata)){
	echo "<form action=RGMySQL.php method=post>"; 
	echo "<tr>";
	echo "<td>" . '<input type=text name=deletename value="' . $row['name'] . '" </td>';
	echo "<td>" . '<input type=text name=deletecat value="' . $row['category'] . '" </td>';
	echo "<td>" . "<input type=text name=deletelength value=" . $row['length'] . " </td>";

//	echo "<td>" . "<input type=text name=deleterented value=" . $row['rented'] . " </td>";  //Used to Debug
	if($row['rented'] ==1){
		echo "<td>" . "<input type=text name=deleterented value= 'Checked In' </td>";
	}
	else if ($row['rented'] ==0){
		echo "<td>" . "<input type=text name=deleterented value= 'Checked Out' </td>";
	}

	echo "<td>" . "<input type=hidden name=deleteid value=" . $row['id'] . " </td>";
	echo "<td>" . "<input type=submit name=remove value=Delete" . " </td>";
	echo "<td>" . "<input type=submit name=checkinout value='Check In/Out'" . " </td>";
	echo "<tr>";
	echo "</form>";
}

}

	echo'</table>';

echo "<form action = RGMySQL.php method=post>";
echo "<tr>";
echo "<td>" . "<input type=submit name=deleteall value='Delete All'" . " </td>";
echo "</tr>";
echo "</form>"; 

?>


</body>
</html>
