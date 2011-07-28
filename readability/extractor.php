<?php 

include_once 'Readability.php';





/**
	Extracts Content from a url and puts it into an array
	
**/
function extract_content($url,$curl=false){
if(!(preg_match("/^http:\/\//",$url))){
	$url = "http://".$url;
}

$content = array();
$content['url'] = $url;

if($curl){

  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $html = curl_exec($ch);
  curl_close($ch);

}

else{

$html = file_get_contents($url);

}


if (function_exists('tidy_parse_string')) {
	$tidy = tidy_parse_string($html, array(), 'UTF8');
	$tidy->cleanRepair();
	$html = $tidy->value;
}


$readability = new Readability($html, $url);
$readability->convertLinksToFootnotes = true;

$result = $readability->init();

if ($result) {
	
	$content['hasContent'] = true;
	$content['title'] = $readability->getTitle()->textContent;


	$content['body'] = $readability->getContent()->innerHTML;

	// if we've got Tidy, let's clean it up for output
	if (function_exists('tidy_parse_string')) {
		$tidy = tidy_parse_string($content['body'],
			array('indent'=>true, 'show-body-only'=>true),
			'UTF8');
		$tidy->cleanRepair();
		$content['body'] = $tidy->value;
	}
	
} else {
	$content['hasContent'] = false;
}
return $content;

}

/**
	Extracts Content from a url and puts it into an array
	
**/
function print_content($content){
if($content['hasContent'] == false){
	echo "Either the URL: \"".$content['url']."\" is incorrect or site has no content worth extracting";
	return;
}
echo "<h1 style=\"font-family: 'Lucidia Grande';\">";
echo $content['title']."<br/><br/>\n\n";
echo "</h1><span style=\"font-family: 'Lucidia Grande';\">";
echo $content['body']."</span><br/><br/>\n\n";

}
?>
