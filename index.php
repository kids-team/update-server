<?php
require __DIR__ . '/vendor/autoload.php';

header( 'Content-Type: application/json' );

if(!isset($_GET['id'])) {
   echo "no id given";
   die();
}

$slug = $_GET['id'];
$infofile = __DIR__ . "/packages/" . $slug . '/version.txt';
$pluginfile = __DIR__ . '/packages/' . $slug . '/latest.zip';

if(!file_exists($infofile)) {
	header("HTTP/1.0 404 Not Found");
    echo "Plugin Information not found";
    die();
}

if(!file_exists($pluginfile)) {
	header("HTTP/1.0 404 Not Found");
	echo "Plugin ZIP file not found";
	die();
}

$has_readme = file_exists(__DIR__ . '/packages/' . $slug . '/readme.md');
$has_changelog = file_exists(__DIR__ . '/packages/' . $slug . '/changelog.md');

$lines = file($infofile);

$info = [
	'name' => '',
	'slug' => $slug,
	'author' => '',
	'version' => '',
	'download_url' => 'https://update.kids-team.com/wp/packages/' . $slug . '/latest.zip',
	'requires' => '',
	'requires_php' => '',
	'tested' => '',
	'added' => filectime($pluginfile),
	'last_updated' => fileatime($pluginfile),
	'sections' => [
		'description' => '',
		'installation' => '',
		'changelog' => ''
	],
	'banners' => [
		'low' => '',
		'high' => ''
	]
];

$changelog = $has_changelog ? file_get_contents(__DIR__ . '/packages/' . $slug . '/changelog.md') : '';
$description = $has_readme ? file_get_contents(__DIR__ . '/packages/' . $slug . '/readme.md') : '';
$converter = new League\CommonMark\CommonMarkConverter([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);

$info['sections']['changelog'] = $converter->convert($changelog);
$info['sections']['description'] = $converter->convert($description);


foreach ($lines as $lineNumber => $line) {

	if (strpos($line, 'Plugin Name') !== false) {
		$plugin_name = explode(":", $line);
		$info['name'] = trim($plugin_name[1]);
		continue;
	}

	if (strpos($line, 'Author') !== false) {
		$author = explode(":", $line);
		$info['author'] = trim($author[1]);
		continue;
	}

	if (strpos($line, 'Version') !== false) {
		$version = explode(":", $line);
		$info['version'] = trim($version[1]);
		continue;
	}

	if (strpos($line, 'Requires at least') !== false) {
		$requires = explode(":", $line);
		$info['requires'] = trim($requires[1]);
	}

	if (strpos($line, 'Requires PHP') !== false) {
		$requires_php = explode(":", $line);
		$info['requires_php'] = trim($requires_php[1]);
	}

	if (strpos($line, 'Description') !== false) {
		$description = explode(":", $line);
		$info['section']['description'] = trim($description[1]);
	}
	
}

header("HTTP/1.0 200 OK");
echo json_encode($info);