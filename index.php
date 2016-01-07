<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<title>Denní meníčka</title>
<link rel="stylesheet" href="assets/styles/index.css">
</head>
<body>
<main class="restaurants">
<?php
$urls = [
	'Potrefená Husa' => 'https://www.zomato.com/cs/praha/potrefen%C3%A1-husa-n%C3%A1rodn%C3%AD-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Bistro OS' => 'https://www.zomato.com/cs/praha/bistroos-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
	'U Medvídků' => 'https://www.zomato.com/cs/praha/restaurace-u-medv%C3%ADdk%C5%AF-star%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Modrá zahrada' => 'https://www.zomato.com/cs/praha/modr%C3%A1-zahrada-star%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Chilli Point' => 'https://www.zomato.com/cs/praha/chilli-point-star%C3%A9-m%C4%9Bsto-praha-1/menu',
	'Restaurant Ostrovní' => 'https://www.zomato.com/cs/praha/restaurace-ostrovn%C3%AD-nov%C3%A9-m%C4%9Bsto-praha-1/menu',
];

foreach($urls as $name => $url) {
	$options = array(
		"ssl"=>array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
			),
		'http' => array(
			'method' => "GET",
			'header' => "User-Agent: PHP\r\n"
			)
		);

	$context = stream_context_create($options);
	$content = file_get_contents($url, false, $context);

	$start = strpos($content, '<div class="tmi-group ">');
	$end = strpos($content, "\n                </div>", $start);
?>
	<div class="restaurant">
		<h2 class="restaurant-name"><?php echo $name ?></h2>
		<div class="restaurant-menu">
<?php
	echo substr($content, $start, $end - $start) . '</div></div>';
}
?>
		</div>
	</div>
</main>
</body>
</html>
