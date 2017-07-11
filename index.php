<?php

define('API_URL', 'https://developers.zomato.com/api/v2.1');
define('API_KEY', '938c81de2f543a03f1d05f2d6ba54f39');

use Nette\Utils\Json;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

header('Content-Type:text/html;charset=utf-8');
require_once __DIR__ . '/vendor/autoload.php';
Tracy\Debugger::enable(Tracy\Debugger::DETECT, __DIR__ . '/log');

function download_menu($id) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, API_URL . "/dailymenu?res_id=$id");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: application/json',
		'user_key: ' . API_KEY,
	]);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	return Json::decode($result, Json::FORCE_ARRAY)['daily_menus'][0]['daily_menu'];
}

$group = empty($_GET['g']) ? "mango" : $_GET['g'];
$db = Json::decode(file_get_contents(__DIR__ . '/db.json'), Json::FORCE_ARRAY);

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
<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<script src="https://npmcdn.com/masonry-layout@4.1/dist/masonry.pkgd.min.js"></script>
</head>
<body>
<div class="mnamgo">
<main class="restaurants">
<?php

$cache = new Cache(new FileStorage(__DIR__ . '/cache'));

$first = TRUE;
foreach($sources as $name => $id):
	$data = $cache->call('download_menu', $id, date('j_n_Y'));
?>
	<div class="restaurant<?php echo $first ? ' is-open' : ''; echo empty($data) ? ' is-empty' : '';?>">
		<h2 class="restaurant-name"><a href="http://zoma.to/r/<?php echo $id ?>"><?php echo htmlspecialchars($name) ?></a></h2>
		<div class="restaurant-menu">
			<div class="tmi-group mtop">
				<div class="tmi-group-name bold fontsize3 pb5 bb"><?php echo date('j. n. Y', strtotime($data['start_date'])) ?></div>
	<?php if(empty($data)):	?>
		<div class="empty-menu">Chyba</div>
<?php
	else:
		foreach ($data['dishes'] as $dish):
			$dish = $dish['dish'];
	        ?>
				<div class="tmi tmi-daily pb5 pt5  ">
					<div class="tmi-text-group col-l-14 col-s-14">
						<div class="row">
							<div class="tmi-name"><?php echo $dish['name']; ?></div>
						</div>
					</div>
					<div class="tmi-price ta-right col-l-2 col-s-2 bold600">
						<div class="row"><?php echo $dish['price']; ?></div>
					</div>
					<div class="clear"></div>
				</div>
			 <?php
		endforeach;
		endif;
?>
			</div>
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
