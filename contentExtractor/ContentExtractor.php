<?php
/*
Plugin Name: Content Extractor
Plugin URI: http://pico.net.nz
Description: Allows you to extract content from a website.
Version: 0.3.0
Author: Wouter Coppieters
Author URI: http://pico.net.nz
License: GPL2
*/
global $wpdb;

require_once 'Extractor.php';
require_once dirname(__FILE__).'/../register_menus.php';
require_once 'EPub.php';

register_menu("Content Extractor","load_ui");

add_action('wp_loaded','check_post_for_epub');

function check_post_for_epub(){
	global $extracted_content;
	$extract_url = $_POST['url'];
	if($extract_url){
			
			$extracted_content = extract_content($extract_url);
				if($extracted_content['hasContent'] == false)
				{
				$extracted_content = extract_content($extract_url,true);
				}
			
	clean_content_body();
	}
	
	if($_POST['epub']){
		generate_epub_version($extracted_content);
	}
}

function load_ui(){
	global $extracted_content;
	$extract_url = $_POST['url'];
	 if(!$extract_url){
		$extract_url="http://juxtr.net";
	 }

	if($extracted_content == ""){
			
			$extracted_content = extract_content($extract_url);
				if($extracted_content['hasContent'] == false)
				{
				$extracted_content = extract_content($extract_url,true);
				}
			clean_content_body();
	}
	?>
	<div class="center_block">
		<div class="content_block"/>
			<div class="test_content_display">
				<?php print_content(); ?>
			</div>
		</div>
		<div  class="buttons_section">
			<form action="" method="post">
				<input  type="text" size="30" name="url" value="<?php echo $extract_url; ?>"/>
					<div>
  						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Extract Content') ?>" />
  						<input type="submit" name="epub" class="button-primary" value="<?php esc_attr_e('Generate ePub') ?>" />
					</div>
			</form>
		</div>
	</div>
<?php
}

function generate_epub_version($content){
	global $user_login;
	get_currentuserinfo();

	$fileDir = './';
	
	$book = new EPub();
	$title = $content['title'];
// Title and Identifier are mandatory!
	$book->setTitle($title);
	$book->setIdentifier(get_bloginfo('url'), EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
	$book->setLanguage("en"); // Not needed, but included for the example, Language is mandatory, but EPub defaults to "en". Use RFC3066 Language codes, such as "en", "da", "fr" etc.
	$book->setDescription("This is a brief description\nA test ePub book as an example of building a book in PHP");
	$book->setAuthor($user_login); 
	$book->setPublisher($user_login." Publications", get_bloginfo('url')); // I hope this is a non existant address :) 
	$book->setDate(time()); // Strictly not needed as the book date defaults to time().
	$book->setSourceURL(get_bloginfo('url'));

	$book->addCSSFile("styles.css", "css1", file_get_contents(plugins_url()."/book_style.css"));
	
	// This test requires you have an image, change "images/_cover_.jpg" to match your location.
	$book->setCoverImage("Cover.jpg", file_get_contents(get_bloginfo('template_url')."/images/headers/hanoi.jpg"), "image/jpeg");
	
	// A better way is to let EPub handle the image itself, as it may need resizing. Most Ebooks are only about 600x800
	//  pixels, adding megapix images is a waste of place and spends bandwidth. setCoverImage can resize the image.
	//  When using this method, the given image path must be the absolute path from the servers Document root.
	//$book->setCoverImage(preg_replace("/".str_replace("/","\/",get_bloginfo('siteurl'))."/","",get_bloginfo('template_url')."/images/headers/hanoi.jpg"));

	$content_start =
	"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
	. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
	. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
	. "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
	. "<head>"
	. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
	. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
	. "<title>".$title."</title>\n"
	. "</head>\n"
	. "<body>\n";

	$cover = $content_start . "<div style=\"margin:300px 0px;\"><h1>".$title."</h1>\n<h2>By:".$user_login."</h2>\n"
	. "</div></body>\n</html>\n";
	$book->addChapter("Notices", "Cover.html", $cover);

	$chapter1 = $content_start . $content['body'];

	
	$book->addChapter("Chapter 1", "Chapter001.html", $chapter1,false, EPub::EXTERNAL_REF_ADD, $fileDir);
	$book->finalize();
	$title = preg_replace("/\s+/","-",$title);
	$book->sendBook($title.".epub");
	}

?>