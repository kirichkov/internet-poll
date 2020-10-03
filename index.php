<?php

  // Start session
  session_start();
	//echo("Ses: " . $_SESSION['token'] . "<br>");
  if (session_status() == PHP_SESSION_NONE OR !isset($_SESSION['token'])) {
    // Unset to make sure no rubbish exists
    session_unset();

    $token = md5(uniqid(rand(), true));
    $_SESSION['token'] = $token;
  }
 
  $ort = htmlspecialchars($_GET["ort"]);

  switch ($ort) {
	case "strebendorf": 
		$location = "[50.6873405, 9.2432580]";
		$zoom = "17";
		break;
	case "zell": 
		$location = "[50.7305300, 9.1962877]";
		$zoom = "16";
		break;
	case "romrod": 
		$location = "[50.7141677, 9.2207196]";
		$zoom = "16";
		break;
	case "ober-breidenbach": 
		$location = "[50.6784249, 9.2301273]";
		$zoom = "16";
		break;
	case "nieder-breidenbach": 
		$location = "[50.6933456, 9.2238629]";
		$zoom = "17";
		break;
	default:
	  $location = "[50.7141677, 9.2207196]";
	  $zoom = "14"; 	
  }
 
?>


<!DOCTYPE html>
<html>
  <head>
    <title>Glasfaser für Romrod</title>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'>
    <meta content='width=device-width, initial-scale=1, user-scalable=no' name='viewport'>
		<meta content='IE=edge' http-equiv='X-UA-Compatible'>
		<meta name="csrf-token" content="<?php echo $_SESSION['token']; ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
   integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
   crossorigin=""/>
   <style>
    body {
      height: 95vh;
    }
    #mapid {
      height: 100%;
    }
   </style>
  </head>
  <body data-location="<?php echo $location ?>" data-zoom="<?php echo $zoom ?>">

    <div id="mapid"></div>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="./script.js"></script>
		<div><p style="font: 12px/1.5 'Helvetica Neue', Arial, Helvetica, sans-serif;">Informationen zur persönlichen Nutzung - geschützter Bereich. Widerrechtlicher und nicht genehmigter Zugriff ist strafbar. Zugriffsprotokolliert.</p></div> 
  </body>
</html>