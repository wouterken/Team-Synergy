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
   curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT
5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0");
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
function clean_content_body(){
	global $extracted_content;
	if(preg_match("/(http:\/\/[^\/]+)\/?/",$extracted_content['url'],$matches)){
	$host_base = $matches[1];
	}else{
		$host_base = $extracted_content['url'];
	}

	
	$extracted_content['body'] = "</h1><span style=\"font-family: 'Georgia';\">".$extracted_content['body'];
if(preg_match_all("/<img.*?src=\s*\"([^(http:)].*?)\"/",$extracted_content['body'],$matches)){
	foreach($matches[1] as $match){
	$extracted_content['body']=	preg_replace("/<img(.*?)src=\s*\"".str_replace("/","\/",$match)."/","<img $1 src=\"".$host_base.$match."\"",$extracted_content['body']);
	}
}
if(preg_match_all("/<a.*?href=\"([^(http:)].*?)\"/",$extracted_content['body'],$matches)){
foreach($matches[1] as $match){

	$extracted_content['body']=	preg_replace("/<a(.*?)href=\"".str_replace("/","\/",$match)."/","<a $1 href=\"".$extracted_content['url'].$match."\"",$extracted_content['body']);
	}
}
if(preg_match_all("/width=\"([0-9]*)\"/",$extracted_content['body'],$matches)){
	foreach($matches[1] as $match){
		if($match > 400){
			$extracted_content['body'] = preg_replace("/width=\"".$match."\"/","width=\"400\"",$extracted_content['body']);
		}
	}
	
}
if(preg_match("/<img(.*?)class=\"(.*?)\"/",$extracted_content['body'])){
$extracted_content['body'] = preg_replace("/<img(.*?)class=\"(.*?)\"/","<img$1class=\"reader_image $2\"",$extracted_content['body']);
}
else{
$extracted_content['body'] = preg_replace("/<img/","<img class=\"reader_image \"",$extracted_content['body']);
}
$extracted_content['body'] .= "</span><br/><br/>\n\n";
}

function print_content(){

global $extracted_content;

if($extracted_content['hasContent'] == false){
	echo "Either the URL: \"".$extracted_content['url']."\" is incorrect or site has no content worth extracting";
	return;
}
echo "<h1 style=\"font-family: 'Georgia';\">";
echo $extracted_content['title']."</h1>";




echo $extracted_content['body'];
}
?>