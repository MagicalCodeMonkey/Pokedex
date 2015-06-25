<?php
ini_set('max_execution_time', 60);
?>

<html lang="en" class="no-js">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="css/baraja.css" />
		<link rel="stylesheet" type="text/css" href="css/custom.css" />
		<script type="text/javascript" src="js/modernizr.custom.79639.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="pokedex">
				<ul id="baraja-el" class="baraja-container">
<?php

$time_start = microtime(true);
$max_execution_time = ini_get('max_execution_time'); 

$pokeapi_baseurl = "http://pokeapi.co/";

$db = new SQLite3('catch_them_all.db') or die('Unable to open database');
$query = <<<EOD
  CREATE TABLE IF NOT EXISTS pokemon (
    name STRING PRIMARY KEY,
    description STRING,
    image STRING,
    pokeapi_uri STRING)
EOD;
$db->exec($query) or die('Create db failed');

$pokeCount = $db->query('SELECT COUNT(*) FROM pokemon')->fetchArray()[0];

if($pokeCount == 0)
{
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $pokeapi_baseurl."api/v1/pokedex/1/");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$pokedex_r = curl_exec ($curl);
	curl_close ($curl);
	$pokedex_json = json_decode($pokedex_r);

	$poke_count = 0;
	foreach($pokedex_json->pokemon as $pokeball)
	{
		$name = $pokeball->name;
		$info_uri = $pokeapi_baseurl.$pokeball->resource_uri;
		
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $info_uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$pokeball_info_r = curl_exec ($curl);
		curl_close ($curl);
		
		$pokeball_json = json_decode($pokeball_info_r);
		
		$description_uri = $pokeapi_baseurl.$pokeball_json->descriptions[0]->resource_uri; //EX: /api/v1/description/64/
		
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $description_uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$description_r = curl_exec ($curl);
		$desc_json = json_decode($description_r);
		curl_close ($curl);
		
		$description = $desc_json->description;
		
		$image_uri = $pokeapi_baseurl.$pokeball_json->sprites[0]->resource_uri; //EX: /api/v1/sprite/6/
		
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $image_uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$image_r = curl_exec ($curl);
		$image_json = json_decode($image_r);
		curl_close ($curl);
		
		$image = $pokeapi_baseurl.$image_json->image;
		
		$db->exec('INSERT INTO pokemon (name, description, image, pokeapi_uri) VALUES ("'.$name.'", "'.$description.'", "'.$image.'", "'.$info_uri.'")');
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		if($time > $max_execution_time - 10)
		{
			break;
		}
	}

}

$result = $db->query('SELECT * FROM pokemon') or die('Query failed');
	
$i = 0;
while ($row = $result->fetchArray())
{	
	if(is_array($row))
	{
		echo ''
			.'<li>'
			.'	<img src="'.$row['image'].'" height="100" width="100" alt="Image of '.$row['name'].'"/>'
			.'	<h4>'.$row['name'].'</h4>'
			.'	<p>'.$row['description'].'</p>'
			.'</li>'
			.'';
	}
}
?>
					</ul>
				</div>
				<nav class="nav actions light">
					<span id="nav-prev">&lt;</span>
					<span id="nav-next">&gt;</span>
				</nav>
			</section>
			
        </div>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script type="text/javascript" src="js/jquery.baraja.js"></script>
		<script type="text/javascript">	
			$(function() {

				var $el = $( '#baraja-el' ),
					baraja = $el.baraja();

				// navigation
				$( '#nav-prev' ).on( 'click', function( event ) {
					baraja.previous();
				});

				$( '#nav-next' ).on( 'click', function( event ) {
					baraja.next();
				});
				
				$("body").keydown(function(e) {
				  if(e.keyCode == 37) { // left
					baraja.previous();
				  }
				  else if(e.keyCode == 39) { // right
					baraja.next();
				  }
				});
			});
		</script>
</body>
