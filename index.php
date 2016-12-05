<?php

header('Content-Type:text/html;charset=utf-8');
require_once __DIR__ . '/vendor/autoload.php';
Tracy\Debugger::enable(Tracy\Debugger::DETECT, __DIR__ . '/log');

function download_menu($url) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	
	
	$headers = array();
	$headers[] = "Pragma: no-cache";
	$headers[] = "Dnt: 1";
	$headers[] = "Accept-Encoding: deflate, sdch, br";
	$headers[] = "Accept-Language: en,cs;q=0.8,en-GB;q=0.6";
	$headers[] = "Upgrade-Insecure-Requests: 1";
	$headers[] = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36";
	$headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
	$headers[] = "Cache-Control: no-cache";
	$headers[] = "Authority: www.zomato.com";
	$headers[] = "Cookie: ak_bmsc=882745FCADD9468899DC6D37AFA9546B6867493F9C76000024364558C33F4F38~plQAqfVwtSDb+GEeBa7Bl6owISjtG1VOE4cZQDrVYI+sgz0aA8kJ9ox/r1twnwd9B1voFQPB/goQrEbj5mlX8Wa9LBZkoSf3L+jkpFBes6dURf9b2xBsEyz3SWeF5hD2tibpzQIUSi0T28V9048VLpA313kqg1fljLz6AlAL28sLy+VcDLqCZDknm92jbIa1ohTXiuMKIhL2KfqEp6P8zj+A==";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	return $result;
}

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
	$html = download_menu($url);

	$document = new DOMDocument();
	$document->loadHTML($html);
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
<script src="https://npmcdn.com/masonry-layout@4.1/dist/masonry.pkgd.min.js"></script>
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
		<h2 class="restaurant-name"><a href="<?php echo htmlspecialchars($url) ?>"><?php echo htmlspecialchars($name) ?></a></h2>
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

if(window.innerWidth > 800) {
	var msnry = new Masonry( '.restaurants', {
		itemSelector: '.restaurant',
		columnWidth: 380
	});
}
</script>
</body>
</html>
