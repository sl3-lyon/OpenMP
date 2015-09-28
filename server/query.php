<?php
require_once("functions.php");

$query = $_GET;

if (!isset($query["package"])) {
	sendJson(JSON_INVALID_REQUEST);
}
$name = $query["package"];

if (!preg_match(REGEX_PACKAGE, $name)) {
	sendJson(JSON_INVALID_REQUEST);
}

$bdd = getBdd();

// Nombre de versions
$req = $bdd->prepare("SELECT COUNT(*) FROM package WHERE name = :name");
$req->bindValue(":name", $name, PDO::PARAM_STR);
$req->execute();
$arr = $req->fetchAll();

$nbVer = (int) $arr[0][0];

if ($nbVer === 0) {
	sendJson(JSON_PACKAGE_NOT_FOUND);
}

// Récup du paquet (à jour ou la version demandée)
$pack = null;
if (!isset($query["version"])) {
	$req = $bdd->prepare("SELECT * FROM package WHERE name = :name ORDER BY version DESC LIMIT 1");
	$req->bindValue(":name", $name);
	$req->execute();
	$arr = $req->fetchAll(PDO::FETCH_ASSOC);
	$pack = $arr[0];
} else {
	$version = version2int($query["version"]);
	$req = $bdd->prepare("SELECT * FROM package WHERE name = :name AND version = :version");
	$req->bindValue(":name", $name);
	$req->bindValue(":version", $version, PDO::PARAM_INT);
	$req->execute();
	$arr = $req->fetchAll(PDO::FETCH_ASSOC);
	if (count($arr) === 0) {
		sendJson(JSON_VERSION_NOT_FOUND);
	}
	$pack = $arr[0];
}
$pack["version"] = int2version($pack["version"]);

// Dépendances
$req = $bdd->prepare("SELECT * FROM lib JOIN package_lib ON package_lib.id_lib = lib.id_lib WHERE package_lib.id_package = :pack");
$req->bindValue(":pack", $pack["id_package"]);
$req->execute();
$deps = $req->fetchAll(PDO::FETCH_ASSOC);
foreach ($deps as $dep) {
	$dep["version"] = int2version($dep["version"]);
}

$shouldTar = false;
if (isset($query["filename"]) && filenameValid($query["filename"])) {
	sendTar($query["filename"], $pack, $deps);
} else {
	// Envoi des infos
	sendJson(JSON_OK, [
		"package" => $pack,
		"nbVer" => $nbVer,
		"dependencies" => $deps
	]);
}
?>