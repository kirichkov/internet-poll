<?php
  session_start();  // Start session

  function getPostVar($postVar) {
    $returnPostVar = "";
    if (isset($_POST[$postVar])) {
      $returnPostVar = htmlspecialchars($_POST[$postVar]);
      $returnPostVar = trim($returnPostVar);
    }

    return $returnPostVar;
  }
	
	function writeLog($action, $content) {
		$log  = date("Ymd H:i:s")." [".$_SERVER['REMOTE_ADDR']."] @".$action." -> ".$content.PHP_EOL;

    file_put_contents('log_'.date("Ymd").'.txt', $log, FILE_APPEND);
	}


  //echo("Ses: " . $_SESSION['token'] . "<br>");
  if (session_status() == PHP_SESSION_NONE OR !isset($_SESSION['token'])) {
    // Unset to make sure no rubbish exists
    session_unset();

    $token = md5(uniqid(rand(), true));
    $_SESSION['token'] = $token;
  //echo("New Token:" . $token);
  }
  
  function getDBMarkerToJSON() {
		try {
			$db = new SQLite3('survey.sqlite', SQLITE3_OPEN_READONLY);
			$results = $db->query("SELECT id, long, lat, status, street, housenumber FROM survey WHERE status != 'deleted'") or die ("Failed to run SELECT");

			$arr_data = array();
			
			while ($row = $results->fetchArray()) {
				 $formdata = array(
						'id'=> $row['id'],
						'latlong'=> array( $row['lat'], $row['long']),
						'status'=> $row['status'],
						'street'=> $row['street'],
						'housenumber'=> $row['housenumber'],		  
				 );		
				array_push($arr_data,$formdata);
			}
			
			$jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);	   
				
				$db->close();	
			
			//write json data into file
			if (file_put_contents("points.json", $jsondata)) {

			}else{
				$failed = true;
				$failedMsg = "Could not save JSON data";
			}

		}catch (Exception $e) {
			echo $e;
		}	  
  }


  // update
	function updateDBMarker() {
				
		$inId = getPostVar("inId");
		$inStreet = getPostVar("inStreet");
		$inHousenumber = getPostVar("inHousenumber");
		$inStatus = getPostVar("inStatus");

		try {
			$db = new SQLite3('survey.sqlite', SQLITE3_OPEN_READWRITE);
			// Please see: https://xkcd.com/327/
			$query = $db->exec("UPDATE survey SET status='" . $db->escapeString($inStatus) . "', street='" . $db->escapeString($inStreet) . "', housenumber='" . $db->escapeString($inHousenumber) . "' WHERE id='" . $db->escapeString($inId) . "'");

			$db->close();	
			
			writeLog("update", $inId);
		}catch (Exception $e) {
			echo $e;
		}	
		
		getDBMarkerToJSON();
	}
	
	// delete
	function deleteDBMarker() {
				
		$inId = getPostVar("inId");

		try {
			$db = new SQLite3('survey.sqlite', SQLITE3_OPEN_READWRITE);
			// Please see: https://xkcd.com/327/
			$query = $db->exec("UPDATE survey SET status='deleted' WHERE id='" . $db->escapeString($inId) . "'");

				$db->close();	
				writeLog("delete", $inId);
		}catch (Exception $e) {
			echo $e;
		}	

		getDBMarkerToJSON();
	}

	function insertDBMarker() {
		$inStreet = getPostVar("inStreet");
		$inHousenumber = getPostVar("inHousenumber");
		$inStatus = getPostVar("inStatus");
		$inLatLong = getPostVar("inLatLng");
		$lat = preg_split("/,/", $inLatLong)[0];
		$long = preg_split("/,/", $inLatLong)[1];

		try {
			$db = new SQLite3('survey.sqlite', SQLITE3_OPEN_READWRITE);
			// Please see: https://xkcd.com/327/
			$query = $db->exec("INSERT INTO survey (long, lat, status, street, housenumber) VALUES(\"" . $db->escapeString($long) . "\", \"" . $db->escapeString($lat) . "\", \"" . $db->escapeString($inStatus) . "\", \"" . $db->escapeString($inStreet) . "\", \"" . $db->escapeString($inHousenumber) . "\");");
			print($db->lastInsertRowID());
			$db->close();	
		} catch (Exception $e) {
			echo $e;
		}	
	}

  $failed = false;
  $failedMsg = "";


  if ($_SERVER['REQUEST_METHOD']=='POST') {
    $failed = true;
  
  $inType = getPostVar("inType");
  
  switch ($inType) {
    case "update":
      updateDBMarker();
      break;
    case "insert":
      insertDBMarker();
      break;
    case "delete":
      deleteDBMarker();
      break;
  }

    //Check session existing
    if (isset($_SESSION['token'])) {
      //echo("We got a Token" . "<br>");

      // Validate submit token
      if (isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
        //echo("We got a valid session". "<br>");

      }
    }

    // Ensure not to resubmit a form
    if ($failed == false) {
      header('Location: '.$_SERVER['PHP_SELF'], true, 303);
      exit;
    }
  }
?>

<?php 
//echo($_SESSION['token']);
	if (!file_exists("points.json")) getDBMarkerToJSON();
 ?>