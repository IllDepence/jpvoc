<?php

include 'functions.php';

$vocs_obj = get_vocs('object');
$vocs_all = array_merge($vocs_obj->enabled, $vocs_obj->disabled);
usort($vocs_all, 'cmp_vocs_by_ratio');

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf8">
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div id="main">
	<p style="text-align: center;"><a href="index.php">back</a> / <a href="edit.php">edit</a></p>
		<table id="voc_list">
			<tr>
				<th>kana</th>
				<th>kanji</th>
				<th>german</th>
				<th>info</th>
				<th>right</th>
				<th>de -&gt; jp</th>
				<th>jp -&gt; de</th>
			</tr>
	<?php
		foreach($vocs_all as $voc) {
			echo '<tr>'.
				'<td>'.$voc->jp.'</td>'.
				'<td>'.$voc->kanji.'</td>'.
				'<td>'.$voc->ger.'</td>'.
				'<td>'.$voc->i.'</td>'.
				'<td>'.round(get_voc_ratio($voc)*100).'%</td>'.
				'<td><span class="green">'.$voc->rt.'</span> / <span class="red">'.$voc->wt.'</span></td>'.
				'<td><span class="green">'.$voc->rf.'</span> / <span class="red">'.$voc->wf.'</span></td>';
				'</tr>';
			}
	?>
		</table>
<div>
</body>
</html>
