<?php
// LOCAL
$dsn = 'mysql:host=localhost;port=3306;dbname=hockey;';
$username = 'root';
$password = '';
try {
    $db = new PDO($dsn, $username, $password);//, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}

// LIVE
/*require('constants.php');
$dsn = 'mysql:host=' . HOST . ';port=' . PORT . ';dbname=' . DBNAME . ';';
try {
    $db = new PDO($dsn, USER, PASS);
} catch(PDOException $e) {
    die('Could not connect to the database:<br/>' . $e);
}*/
?>
<!DOCTYPE html>
<html>
<head>
<title>Jeff's Hockey Stats</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="css/jquery.mobile-1.4.5.min.css"/>
<style>
.collapsible_li { padding-right:15px !important; padding-top:0 !important; padding-bottom:0 !important; }
.collapsible_li h1 { margin-top:0 !important; margin-bottom:0 !important; }

.statTable { width:100%; text-align:center; }
.statTable th { width:12.5%; background-color:#222; color:#fff; font-weight:normal; }
.statTable td { width:12.5%; }

#addPage .addHeader { float:left; }
#addPage .addContent { float:right; font-weight:normal; color:#aaaaaa; }
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
	
	$('#addPage li a.selectItem').click(function(e) {
        var key = $(this).data('key');
		var val = $(this).data('value');
		var desc = $(this).html();
		switch(key){
			case 'season':
				$('.addSeason .addContent').data('value', val).html(desc);
				break;
		}
		$(this).parent().parent().parent().popup('close');
    });
	
    $('#submitBtn').click(function(e) {
        $.ajax({
			url:'insert.php',
			data:{
				season:$('#season').val(),
				gameType:$('input[name=gameType]:checked').val(),
				date:$('#date').val(),
				time:$('#time').val(),
				location:$('#location').val(),
				home:$('#home').val(),
				opponent:$('#opponent').val(),
				result:$('#result').val(),
				us:$('#us').val(),
				them:$('#them').val(),
				g:$('#g').val(),
				a:$('#a').val(),
				pim:$('#pim').val(),
				ppg:$('#ppg').val(),
				shg:$('#shg').val()
			},
			type:'post',
			beforeSend:function(){
				// This callback function will trigger before data is sent
				$.mobile.loading('show'); // This will show ajax spinner
			},
			complete:function(){
				// This callback function will trigger on data sent/received complete
				$.mobile.loading('hide'); // This will hide ajax spinner
			},
			success:function(result){
				console.log('success - ' + result);
				var r = JSON.parse(result);
				console.log(r);
				console.log(r.status);
				console.log(r.data.gameType);
				if(r.status){
					resetForm();
					$.mobile.changePage("#successPage");
				} else {
					$.mobile.changePage("#errorPage");
				}
			},
			error:function(request, error){
				// This callback function will trigger on unsuccessful action  
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
	$('#season').val([]).selectmenu('refresh');
	$('#gt1').prop('checked', true).checkboxradio('refresh');
	$('#gt2').prop('checked', false).checkboxradio('refresh');
	setDateTime();
	$('#location').val([]).selectmenu('refresh');
	$('#home').val(1).slider('refresh');
	$('#opponent').val([]).selectmenu('refresh');
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
            <h1>Add Game Stats</h1>
        </div>
        <div data-role="content">
        	<form>
                <ul data-role="listview">
                	<!-- season -->
                    <li class="addSeason" data-icon="false"><a href="#seasonPopup" data-rel="popup"><span class="addHeader">Season</span><span class="addContent">Choose a season...</span></a></li>
                    
                </ul>
            </form>
        </div>
        
        <div data-role="popup" id="seasonPopup">
            <ul data-role="listview">
                <li data-role="list-divider">Choose a season...</li>
                <?php 
                    $query = $db->prepare("SELECT * FROM seasons WHERE active = 1 ORDER BY description");
                    $query->execute();
                    foreach($query->fetchAll() as $row) {
                        echo '<li><a class="selectItem" data-key="season" data-value="' . $row['id'] . '">' . $row['description'] . '</a></li>';
                    }
                ?>
            </ul>
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