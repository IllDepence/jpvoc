<?php

include 'functions.php';

# delete word
if(isset($_GET['del']) && isset($_GET['state'])) {
	remove_voc($_GET['del'], $_GET['state']);
	}

# en/disable word
if(isset($_GET['cs']) && isset($_GET['state'])) {
	change_voc_state($_GET['cs'], $_GET['state']);
	}

$vocs_obj = get_vocs('object');

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf8">
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div id="main">
	<p style="text-align: center;"><a href="index.php">back</a></p>
	<?php
		foreach(array('enabled', 'disabled') as $state) {
			echo '<h3>'.$state.'</h3>';
			if($vocs_obj->$state) {
				$change = ($state == 'enabled' ? 'disable' : 'enable');
				$symbol = ($state == 'enabled' ? '&darr;' : '&uarr;');
				echo '<table id="voc_list">';
				echo '<tr><th>index</th><th>japanese</th><th>german</th><th>info</th><th>'.$change.'</th><th>delete</th></tr>';
				foreach($vocs_obj->$state as $idx => $voc_obj) {
					echo '<tr>'.
						'<td>'.$idx.'</td>'.
						'<td>'.$voc_obj->jp.'</td>'.
						'<td>'.$voc_obj->ger.'</td>'.
						'<td>'.$voc_obj->i.'</td>'.
						'<td><a href="?cs='.$idx.'&state='.$state.'">'.$symbol.'</a></td>'.
						'<td><a href="?del='.$idx.'&state='.$state.'">X</a></td>'.
						'</tr>';
					}
				echo '</table>';
				}
			else {
				echo '<p><em>empty</em></p>';
				}
			}
	?>
<div>
</body>
</html>
