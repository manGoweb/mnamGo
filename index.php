<?php

header('Content-Type:text/html;charset=utf-8');
require_once __DIR__ . '/vendor/autoload.php';
Tracy\Debugger::enable(Tracy\Debugger::DETECT, __DIR__ . '/log');

function utf8Url($url) {
	if(strpos($url, '%') !== FALSE) return $url;
	list($protocol, $rest) = explode('://', $url);
	$parts = explode('/', $rest);
	$parts = array_map('rawurlencode', $parts);
	return $protocol . '://' . implode('/', $parts);
}

function fromZomato($url, $force = FALSE) {
	$url = utf8Url($url);

	$cachePath = __DIR__ . '/cache/temp-' . date('Y-m-d') . '-' . md5($url) . '.html';

	if(!$force && file_exists($cachePath)) {
		return file_get_contents($cachePath);
	}

	$document = new DOMDocument();
	@$document->loadHTMLFile($url);
	$xpath = new DOMXPath($document);
	$nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " tmi-group ")]');
	$node = $nodes->item(0);

	$content = "";

	if($nodes->length && $node->ownerDocument) {
		$content = $node->ownerDocument->saveHTML($node);
	}

	file_put_contents($cachePath, $content);

	return $content;
}

$group = empty($_GET['g']) ? "mango" : $_GET['g'];
$db = json_decode(file_get_contents(__DIR__ . '/db.json'), TRUE);

$sources = $db[$group];

?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="Denní meníčka okolo">
<meta property="og:description" content="Pojďte s náma, nebo bez nás. We don't care.">
<meta property="og:image" content="http://mangoweb.cz/assets/images/og.jpg">
<title>Denní meníčka okolo</title>
<link rel="stylesheet" href="assets/styles/index.css">
</head>
<body>
<div class="mnamgo">
<main class="restaurants">
<?php

$first = TRUE;
foreach($sources as $name => $url):
	$content = fromZomato($url, isset($_GET['force']));
?>
	<div class="restaurant<?php echo $first ? ' is-open' : ''; echo empty($content) ? ' is-empty' : '';?>">
		<h2 class="restaurant-name"><?php echo htmlspecialchars($name) ?></h2>
		<div class="restaurant-menu">
<?php
if(empty($content)) {
	?><div class="empty-menu"><a href="?force&g=<?php echo $group ?>" class="refresh-btn">Zkusit načíst znovu</a></div><?php
} else {
	echo $content;
}
?>
		</div>
	</div>
<?php $first = FALSE; endforeach; ?>
</main>
<footer class="others">
	<?php foreach($db as $group => $sources): ?>
		<a href="?g=<?php echo rawurlencode($group) ?>"><?php echo htmlspecialchars($group) ?></a>
	<?php endforeach ?>
	<a href="https://m.me/viliamkopecky" class="cta">Chcete to taky? Napište Vilíkovi</a>
</footer>
</div>
<script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
<script>
$('.restaurant-name').on('click', function(e){
	$(this).closest('.restaurant').toggleClass('is-open');
});
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-73886929-1', 'auto');
  ga('send', 'pageview');
</script>
</body>
</html>
