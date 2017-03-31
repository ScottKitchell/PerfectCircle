<?php
	$URL = $_POST['url'];
	
	require_once('OpenGraph.php');
	$graph = OpenGraph::fetch($URL);
	foreach ($graph as $key => $value) {$OGdata[$key] = $value;}
	//Form HTML
	$html = '<div class="articleBox"><div>';
	$html .= '<p class="headline"><a target="_blank" href="'.$URL.'">'.$OGdata['title'].'</a></p>';
	$html .= '<p class="description">'.$OGdata['description'].'</p>';
	$html .= '<a class="img" target="_blank" href="'.$URL.'"><img  src="'.$OGdata['image'].'"></img></a>';
	$html .= '<p class="info">via '.$OGdata['site_name'].'</p>';
	$html .= '</div></div>';
	//return resultant html
	echo $html;
?>