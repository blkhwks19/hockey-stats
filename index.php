<?php
//require('local.php');
require('live.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>Jeff's Hockey Stats</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!--
TODO:
    - move success/error pages into a popup on the main page
    - overall theme/colors
    - add pages for adding/removing seasons, game types, locations, etc
    - re-layout stat display?
-->

<link rel="stylesheet" href="css/jquery.mobile-1.4.5.min.css"/>
<style>
.collapsible_li { padding-right:15px !important; padding-top:0 !important; padding-bottom:0 !important; }
.collapsible_li h1 { margin-top:0 !important; margin-bottom:0 !important; }

.statTable { width:100%; text-align:center; }
.statTable th { width:12.5%; background-color:#222; color:#fff; font-weight:normal; }
.statTable td { width:12.5%; }

#addPage form {	padding-bottom:20px; }
</style>
<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/jquery.mobile-1.4.5.min.js"></script>
<script>
$(document).ready(function(e) {
	
	setDateTime();
	
	$('#backBtn, #cancelBtn').click(function(e) {
        // resetForm() will execute before navigating to the href
		resetForm();
    });
	
    $('#submitBtn').click(function(e) {
        $.ajax({
			url:'insert.php',
			data:{
				season:$('#season').val(),
				gameType:$('#gameType').val(),
				date:$('#date').val(),
				time:$('#time').val(),
				location:$('#location').val(),
				home:$('#home').val(),
				opponent:$('#opponent').val(),
				us:$('#us').val(),
				them:$('#them').val(),
				result:$('#result').val(),
				g:$('#g').val(),
				a:$('#a').val(),
				pim:$('#pim').val(),
				ppg:$('#ppg').val(),
				shg:$('#shg').val()
			},
			type:'post',
			success:function(result){
				//console.log('success - ' + result);
				var r = JSON.parse(result);
				//console.log(r);
				//console.log(r.status);
				//console.log(r.data.gameType);
				if(r.status){
					resetForm();
					$.mobile.changePage("#successPage");
				} else {
					$.mobile.changePage("#errorPage");
				}
			},
			error:function(request, error){
				console.log('error - ' + error);
				$.mobile.changePage("#errorPage");
			}
		});  
		
		
		
    });
});

function setDateTime()
{
	var d = new Date();
	var month = ((d.getMonth()+1) < 10) ? "0" + (d.getMonth()+1) : (d.getMonth()+1);
	var day = (d.getDate() < 10) ? "0" + d.getDate() : d.getDate();
	var date = d.getFullYear() + '-' + month + '-' + day;
	$('#date').val(date);
	
	d.setHours(21);
	d.setMinutes(0);
	var hour = (d.getHours() < 10) ? "0" + d.getHours() : d.getHours();
	var minute = (d.getMinutes() < 10) ? "0" + d.getMinutes() : d.getMinutes();
	var time = hour + ':' + minute;
	$('#time').val(time);
}

function resetForm()
{
	$('#season').val($('#season option:first').val()).selectmenu('refresh');
	$('#gameType').val($('#gameType option:first').val()).selectmenu('refresh');
	setDateTime();
	$('#location').val($('#location option:first').val()).selectmenu('refresh');
	$('#home').val($('#home option:first').val()).selectmenu('refresh');
	$('#opponent').val($('#opponent option:first').val()).selectmenu('refresh');
	$('#us').val(0);
	$('#them').val(0);
	$('#result').val($('#result option:first').val()).selectmenu('refresh');
	$('#g').val(0).slider('refresh');
	$('#a').val(0).slider('refresh');
	$('#pim').val(0).slider('refresh');
	$('#ppg').val(0).slider('refresh');
	$('#shg').val(0).slider('refresh');
}
</script>
</head>
<body>
	
	<div data-role="page" id="mainPage">
        <div data-role="header">
            <h1>Jeff's Hockey Stats</h1>
        </div>
        <div data-role="content">
        	
            
        
            <ul data-role="listview" data-inset="true" data-divider-theme="b" data-count-theme="b">
                <li data-role="list-divider">Active</li>
                <?php
				$query = $db->prepare("SELECT * FROM seasons WHERE active = 1 ORDER BY description");
				$query->execute();
				if (count($query)) {
					foreach($query->fetchAll() as $row) {
						$query2 = $db->prepare("SELECT COUNT(id) as gp, COALESCE(SUM(g),0) as g, COALESCE(SUM(a),0) as a, COALESCE(SUM(g+a),0) as p, COALESCE(SUM(pim),0) as pim, COALESCE(SUM(ppg),0) as ppg, COALESCE(SUM(shg),0) as shg FROM games WHERE season = " . $row['id']);
						$query2->execute();
						foreach($query2->fetchAll() as $row2) {
							$ptsPG = ($row2['gp'] == 0) ? 0 : $row2['p'] / $row2['gp'];
							echo '<li class="collapsible_li"><div data-role="collapsible" data-inset="false">';
							echo '<h1>' . $row['description'] . '</h1>';
							echo '<table class="statTable ui-body-a ui-shadow table-stroke">';
							echo '<thead><tr><th>GP</th><th>G</th><th>A</th><th>P</th><th>P/G</th><th>PIM</th><th>PPG</th><th>SHG</th></tr></thead>';
							echo '<tbody><tr><td>' . $row2['gp'] . '</td><td>' . $row2['g'] . '</td><td>' . $row2['a'] . '</td><td>' . $row2['p'] . '</td><td>' . number_format($ptsPG, 1) . '</td>';
							echo '<td>' . $row2['pim'] . '</td><td>' . $row2['ppg'] . '</td><td>' . $row2['shg'] . '</td></tr></tbody>';
							echo '</table></div></li>';
						}
					}
				} else {
					echo '<li><em>No active seasons</em></li>';
				}
				?>
                
                <li data-role="list-divider">Inactive</li>
                <?php
				$query = $db->prepare("SELECT * FROM seasons WHERE active = 0 ORDER BY description");
				$query->execute();
				if ($query->rowCount() > 0) {
					foreach($query->fetchAll() as $row) {
						$query2 = $db->prepare("SELECT COUNT(id) as gp, COALESCE(SUM(g),0) as g, COALESCE(SUM(a),0) as a, COALESCE(SUM(g+a),0) as p, COALESCE(SUM(pim),0) as pim, COALESCE(SUM(ppg),0) as ppg, COALESCE(SUM(shg),0) as shg FROM games WHERE season = " . $row['id']);
						$query2->execute();
						foreach($query2->fetchAll() as $row2) {
							$ptsPG = ($row2['gp'] == 0) ? 0 : $row2['p'] / $row2['gp'];
							echo '<li class="collapsible_li"><div data-role="collapsible" data-inset="false">';
							echo '<h1>' . $row['description'] . '</h1>';
							echo '<table class="statTable ui-body-a ui-shadow table-stroke">';
							echo '<thead><tr><th>GP</th><th>G</th><th>A</th><th>P</th><th>P/G</th><th>PIM</th><th>PPG</th><th>SHG</th></tr></thead>';
							echo '<tbody><tr><td>' . $row2['gp'] . '</td><td>' . $row2['g'] . '</td><td>' . $row2['a'] . '</td><td>' . $row2['p'] . '</td><td>' . number_format($ptsPG, 1) . '</td>';
							echo '<td>' . $row2['pim'] . '</td><td>' . $row2['ppg'] . '</td><td>' . $row2['shg'] . '</td></tr></tbody>';
							echo '</table></div></li>';
						}
					}
				} else {
					echo '<li><em>No inactive seasons</em></li>';
				}
				?>
                
                <li data-role="list-divider">Career (as of 10/2015)</li>
                <li class="collapsible_li">
                	<div data-role="collapsible" data-inset="false">
						<?php
                        $query = $db->prepare("SELECT * FROM game_type WHERE id = 1");
                        $query->execute();
                        foreach($query->fetchAll() as $row) {
                            $query2 = $db->prepare("SELECT COUNT(id) as gp, COALESCE(SUM(g),0) as g, COALESCE(SUM(a),0) as a, COALESCE(SUM(g+a),0) as p, COALESCE(SUM(pim),0) as pim, COALESCE(SUM(ppg),0) as ppg, COALESCE(SUM(shg),0) as shg FROM games WHERE game_type = " . $row['id']);
                            $query2->execute();
                            foreach($query2->fetchAll() as $row2) {
								$ptsPG = ($row2['gp'] == 0) ? 0 : $row2['p'] / $row2['gp'];
                                echo '<h1>' . $row['type'] . '</h1>';
                                echo '<table class="statTable ui-body-a ui-shadow table-stroke">';
                                echo '<thead><tr><th>GP</th><th>G</th><th>A</th><th>P</th><th>P/G</th><th>PIM</th><th>PPG</th><th>SHG</th></tr></thead>';
								echo '<tbody><tr><td>' . $row2['gp'] . '</td><td>' . $row2['g'] . '</td><td>' . $row2['a'] . '</td><td>' . $row2['p'] . '</td><td>' . number_format($ptsPG, 1) . '</td>';
                                echo '<td>' . $row2['pim'] . '</td><td>' . $row2['ppg'] . '</td><td>' . $row2['shg'] . '</td></tr></tbody>';
                                echo '</table>';
                            }
                        }
                        ?>
                    </div>
                </li>
                
                <li class="collapsible_li">
                	<div data-role="collapsible" data-inset="false">
						<?php
                        $query = $db->prepare("SELECT * FROM game_type WHERE id = 2");
                        $query->execute();
                        foreach($query->fetchAll() as $row) {
                            $query2 = $db->prepare("SELECT COUNT(id) as gp, COALESCE(SUM(g),0) as g, COALESCE(SUM(a),0) as a, COALESCE(SUM(g+a),0) as p, COALESCE(SUM(pim),0) as pim, COALESCE(SUM(ppg),0) as ppg, COALESCE(SUM(shg),0) as shg FROM games WHERE game_type = " . $row['id']);
                            $query2->execute();
                            foreach($query2->fetchAll() as $row2) {
								$ptsPG = ($row2['gp'] == 0) ? 0 : $row2['p'] / $row2['gp'];
                                echo '<h1>' . $row['type'] . '</h1>';
                                echo '<table class="statTable ui-body-a ui-shadow table-stroke">';
                                echo '<thead><tr><th>GP</th><th>G</th><th>A</th><th>P</th><th>P/G</th><th>PIM</th><th>PPG</th><th>SHG</th></tr></thead>';
								echo '<tbody><tr><td>' . $row2['gp'] . '</td><td>' . $row2['g'] . '</td><td>' . $row2['a'] . '</td><td>' . $row2['p'] . '</td><td>' . number_format($ptsPG, 1) . '</td>';
                                echo '<td>' . $row2['pim'] . '</td><td>' . $row2['ppg'] . '</td><td>' . $row2['shg'] . '</td></tr></tbody>';
                                echo '</table>';
                            }
                        }
                        ?>
                    </div>
                </li>
                
                <li class="collapsible_li">
                	<div data-role="collapsible" data-inset="false">
                    	<h1>All-Time</h1>
						<?php
                        $query = $db->prepare("SELECT COUNT(id) as gp, COALESCE(SUM(g),0) as g, COALESCE(SUM(a),0) as a, COALESCE(SUM(g+a),0) as p, COALESCE(SUM(pim),0) as pim, COALESCE(SUM(ppg),0) as ppg, COALESCE(SUM(shg),0) as shg FROM games");
						$query->execute();
						foreach($query->fetchAll() as $row) {
							$ptsPG = ($row['gp'] == 0) ? 0 : $row['p'] / $row['gp'];
							echo '<table class="statTable ui-body-a ui-shadow table-stroke">';
							echo '<thead><tr><th>GP</th><th>G</th><th>A</th><th>P</th><th>P/G</th><th>PIM</th><th>PPG</th><th>SHG</th></tr></thead>';
							echo '<tbody><tr><td>' . $row['gp'] . '</td><td>' . $row['g'] . '</td><td>' . $row['a'] . '</td><td>' . $row['p'] . '</td><td>' . number_format($ptsPG, 1) . '</td>';
							echo '<td>' . $row['pim'] . '</td><td>' . $row['ppg'] . '</td><td>' . $row['shg'] . '</td></tr></tbody>';
							echo '</table>';
						}
                        ?>
                    </div>
                </li>
            </ul>
            <a href="#addPage" class="ui-btn ui-corner-all ui-btn-b ui-icon-plus ui-btn-icon-right">Add Game Stats</a>
        </div>
    </div>
    


    <div data-role="page" id="addPage">
        <div data-role="header">
            <a id="backBtn" href="#mainPage" class="ui-btn ui-corner-all ui-btn-b ui-icon-carat-l ui-btn-icon-left">Back</a>
            <h1>New Game Stats</h1>
        </div>
        <div id="addContent" data-role="content">
        	<form id="form">
            	<ul data-role="listview">
                	<li class="ui-field-contain">
                        <label for="season">Season</label>
                        <select id="season" name="season" data-native-menu="false" required>
                            <?php 
                            $query = $db->prepare("SELECT * FROM seasons WHERE active = 1 ORDER BY description");
                            $query->execute();
                            foreach($query->fetchAll() as $row) {
                                echo '<option value="' . $row['id'] . '">' . $row['description'] . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                        <label for="gameType">Game Type</label>
                        <select id="gameType" name="gameType" data-native-menu="false" required>
                            <?php 
                            $query = $db->prepare("SELECT * FROM game_type ORDER BY id");
                            $query->execute();
                            foreach($query->fetchAll() as $row) {
                                echo '<option value="' . $row['id'] . '">' . $row['type'] . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                        <label for="date">Date</label>
                        <input id="date" name="date" type="date" required>
                    </li>
                    <li class="ui-field-contain">
                        <label for="time">Time</label>
                        <input id="time" name="time" type="time" required>
                    </li>
                    <li class="ui-field-contain">
                    	<label for="location">Location</label>
                        <select id="location" name="location" data-native-menu="false" required>
                            <?php 
                            $query = $db->prepare("SELECT * FROM locations ORDER BY id");
                            $query->execute();
                            foreach($query->fetchAll() as $row) {
                                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                    	<label for="home">Home/Away</label>
                        <select id="home" name="home" data-native-menu="false" required>
                            <option value="1">Home</option>
                            <option value="0">Away</option>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                    	<label for="opponent">Opponent</label>
                    	<select id="opponent" name="opponent" data-native-menu="false" required>
                            <?php
                            $query = $db->prepare("SELECT * FROM opponents ORDER BY name");
                            $query->execute();
                            foreach($query->fetchAll() as $row) {
                                echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                    	<label for="us">Us</label>
                        <input type="range" id="us" name="us" value="0" min="0" max="10" step="1" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="them">Them</label>
                        <input type="range" id="them" name="them" value="0" min="0" max="10" step="1" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="result">Result</label>
                        <select id="result" name="result" data-native-menu="false" required>
                        	 <?php 
							$query = $db->prepare("SELECT * FROM result_type ORDER BY id");
							$query->execute();
							foreach($query->fetchAll() as $row) {
								echo '<option value="' . $row['id'] . '">' . $row['type'] . '</option>';
							}
							?>
                        </select>
                    </li>
                    <li class="ui-field-contain">
                    	<label for="g">G</label>
                        <input type="range" id="g" name="g" value="0" min="0" max="5" step="1" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="a">A</label>
                        <input type="range" id="a" name="a" value="0" min="0" max="5" step="1" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="pim">PIM</label>
                        <input type="range" id="pim" name="pim" value="0" min="0" max="10" step="2" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="ppg">PPG</label>
                    	<input type="range" id="ppg" name="ppg" value="0" min="0" max="5" step="1" data-highlight="true" />
                    </li>
                    <li class="ui-field-contain">
                    	<label for="shg">SHG</label>
                    	<input type="range" id="shg" name="shg" value="0" min="0" max="5" step="1" data-highlight="true" />
                    </li>
                </ul>
            </form>
            
            <div class="ui-grid-a">
                <div class="ui-block-a"><a id="cancelBtn" href="#mainPage" class="ui-btn ui-corner-all ui-icon-delete ui-btn-icon-right">Cancel</a></div>
                <div class="ui-block-b"><a id="submitBtn" class="ui-btn ui-corner-all ui-btn-b ui-icon-check ui-btn-icon-right">Submit</a></div>
            </div>
        </div>
    </div>
    
    <div data-role="page" data-dialog="true" id="successPage">
    	<div data-role="header">
        	<h1>Success!</h1>
        </div>
        <div data-role="content">
        	<p>Game data entered successfully!</p>
            <a href="index.php" class="ui-btn ui-corner-all ui-btn-b">OK</a>
        </div>
    </div>
    
    <div data-role="page" data-dialog="true" id="errorPage">
    	<div data-role="header">
        	<h1>Error!</h1>
        </div>
        <div data-role="content">
        	<p>There was an error submitting the data. Please try again.</p>
            <a data-rel="back" class="ui-btn ui-corner-all ui-btn-b">OK</a>
        </div>
    </div>


</body>
</html>
<?php $db = null; ?>