<?php
header('Content-Type:text/html;charset=utf-8');
require_once __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Strings;
function normalize($content) {
	$content = Strings::fixEncoding($content);
	$content = Strings::normalize($content);

	// normalize "s p a c e" separated words
	$content = Strings::replace($content, '~(\p{L} ){3,}~u', function($m) {
		return str_replace(' ', '', $m[0]);
	});

	// match all-caps words of 2 or more utf8 chars and lowercase them
	$content = Strings::replace($content, '~\b(\p{Lu}{2,})\b~u', function($m) {
		return mb_strtolower($m[0]);
	});

	return $content;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="Denní meníčka okolo Jungmaňáku">
<meta property="og:description" content="Pojďte s náma, nebo bez nás. We don't care.">
<meta property="og:image" content="http://mnamgo.mangoweb.cz/assets/images/og.jpg">
<title>Denní meníčka okolo Jungmaňáku</title>
<link rel="stylesheet" href="assets/styles/index.css">
</head>
<body>
<?php
$today = new DateTime();
$id = $today->format('Y-m-d');
$filename = __DIR__ . '/' . $id . '.tmp';

if(file_exists($filename) && !isset($_GET['force'])) {
	include $filename;
} else {
	ob_start();
?>
<div class="mnamgo">
<main class="restaurants">
<?php
$urls = [
	'Potrefená Husa' => 'https://www.zomato.com/cs/praha/potrefen%C3%A1-husa-n%C3%A1rodn%C3%AD-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Bistro OS' => 'https://www.zomato.com/cs/praha/bistroos-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
	'U Medvídků' => 'https://www.zomato.com/cs/praha/restaurace-u-medv%C3%ADdk%C5%AF-star%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Modrá zahrada' => 'https://www.zomato.com/cs/praha/modr%C3%A1-zahrada-star%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Restaurant Ostrovní' => 'https://www.zomato.com/cs/praha/restaurace-ostrovn%C3%AD-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
	'La Piccola Perla' => 'https://www.zomato.com/praha/la-piccola-perla-star%C3%A9-m%C4%9Bsto-praha-1/menu#daily',
	'Café Louvre' => 'https://www.zomato.com/praha/caf%C3%A9-louvre-nov%C3%A9-m%C4%9Bsto-praha-1/menu#daily',
	'Boorgies' => 'https://www.zomato.com/praha/boorgies-nov%C3%A9-m%C4%9Bsto-praha-1/menu#daily',
	'Hlávkův Dvůr' => 'https://www.zomato.com/praha/hl%C3%A1vk%C5%AFv-dv%C5%AFr-1-nov%C3%A9-m%C4%9Bsto-praha-1/menu#daily',
];

$first = true;
foreach($urls as $name => $url) {

	$document = new DOMDocument();

	@$document->loadHTMLFile($url);
	$xpath = new DOMXPath($document);
	$nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " tmi-group ")]');


	if($nodes->length) {
		$node = $nodes->item(0);
		$menuContent = utf8_decode($node->ownerDocument->saveHTML($node));
	} else {
		$menuContent = '<div class="empty-menu"><a href="?force" class="refresh-btn">Zkusit načíst znovu</a></div>';
	}
?>
	<div class="restaurant<?php echo $first ? ' is-open' : '';?>">
		<h2 class="restaurant-name"><?php echo $name ?></h2>
		<div class="restaurant-menu">
<?php echo $menuContent; ?>
		</div>
	</div>
<?php $first = false; } ?>
</main>
</div>
<?php
$content = ob_get_contents();
file_put_contents($filename, $content);
echo normalize(ob_get_clean());
}
?>
<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
$('.restaurant-name').on('click', function(e){
	$(this).closest('.restaurant').toggleClass('is-open');
});
</script>
</body>
</html>
