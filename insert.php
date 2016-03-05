<?php
//require('local.php');
require('live.php');



// get form data
$season = $_POST['season'];
$gameType = $_POST['gameType'];
$date = $_POST['date'];
$time = $_POST['time'];
$location = $_POST['location'];
$home = $_POST['home'];
$opponent = $_POST['opponent'];
$result = $_POST['result'];
$us = $_POST['us'];
$them = $_POST['them'];
$g = $_POST['g'];
$a = $_POST['a'];
$pim = $_POST['pim'];
$ppg = $_POST['ppg'];
$shg = $_POST['shg'];

$datetime = $date . ' ' . $time;


$query = $db->prepare("INSERT INTO games (season, game_type, date, location, result, us, them, home, opponent, g, a, pim, ppg, shg)
								  VALUES (:season, :game_type, :date, :location, :result, :us, :them, :home, :opponent, :g, :a, :pim, :ppg, :shg)");
$query->bindParam(':season', $season);
$query->bindParam(':game_type', $gameType);
$query->bindParam(':date', $datetime);
$query->bindParam(':location', $location);
$query->bindParam(':result', $result);
$query->bindParam(':us', $us);
$query->bindParam(':them', $them);
$query->bindParam(':home', $home);
$query->bindParam(':opponent', $opponent);
$query->bindParam(':g', $g);
$query->bindParam(':a', $a);
$query->bindParam(':pim', $pim);
$query->bindParam(':ppg', $ppg);
$query->bindParam(':shg', $shg);
$query->execute();

// put form data into array (not really needed but may as well return what was inserted)
$data = array('season' => $season, 'gameType' => $gameType, 'date' => $date, 'time' => $time, 'location' => $location, 'home' => $home, 'opponent' => $opponent, 'result' => $result, 'us' => $us, 'them' => $them, 'g' => $g, 'a' => $a, 'pim' => $pim, 'ppg' => $ppg, 'shg' => $shg);

// create output object to send back, including status and data array
$output = array('status' => true, 'data' => $data);
echo json_encode($output);


$db = null;
?>