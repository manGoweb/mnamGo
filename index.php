<?php header('Content-Type:text/html;charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Denní meníčka</title>
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
ob_end_flush();
}
?>
<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
$('.restaurant-name').on('touchstart mousedown', function(e){
	$(this).closest('.restaurant').toggleClass('is-open');
});
</script>
</body>
</html>
