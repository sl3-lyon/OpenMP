<?php
// Constantes
const DB_HOST = "localhost";
const DB_NAME = "package_manager";
const DB_USER = "root";
const DB_PASS = "";

const JSON_OK = 0;                  // La requête est satisfaite, voir le reste du JSON.
const JSON_INVALID_REQUEST = 1;     // Requête impossible à satisfaire.
const JSON_PACKAGE_NOT_FOUND = 2;   // Le paquet est introuvable.
const JSON_VERSION_NOT_FOUND = 3;   // La version demandée pour ce paquet est introuvable.

const TAR_OK = 4;   				// Le fichier tar.gz à bien été envoyé

const DATABASE_ERROR = 126;    // La base de donnée ne fonctionne pas.

const REGEX_PACKAGE = "/^[a-z0-9_+-]+$/i";
const REGEX_VERSION = "/^[0-9]+\\.[0-9]+\\.[0-9]+$/i";
const REGEX_FILENAME = "/^$/i"; //TODO

const DIR_ALL = "file";
const DIR_PACKAGE = "package";
const DIR_LIB = "lib";
const DIR_OUT = "output";

/**
 * Renvoie une instance de la BDD.
 *
 * @return PDO
 */
function getBdd() {
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        sendJson(DATABASE_ERROR);
    }
}
/**
 * Envoie un code et d'autres données puis exit.
 * @param int $code   Code de retour
 * @param array $data Tableau à envoyer
 */
function sendJson($code, $data = []) {
    $data["code"] = $code;
    echo json_encode($data);
    exit();
}
/**
 * Converti un numéro de version de string (voir REGEX_VERSION) en int pour la comparaison
 * @param string $ver Numéro de version. S'il ne respecte pas REGEX_VERSION renvoie JSON_INVALID_REQUEST et exit.
 *
 * @return int
 */
function version2int($ver) {
    if (!preg_match(REGEX_VERSION, $ver)) {
        sendJson(JSON_INVALID_REQUEST);
    }
    $arr = explode(".", $ver);
    return $arr[0] * 10000 + $arr[1] * 100 + $arr[2];
}
/**
 * Converti un numéro de version d'int vers string.
 * @param int $int Numéro de version à convertir.
 *
 * @return string
 */
function int2version($int) {
    $major = ($int / 10000) % 10000;
    $minor = ($int / 100) % 100;
    $rev   = $int % 10;
    return $major . "." . $minor . "." . $rev;
}


/**
 * TODO comments
 * TODO use regex
 *
 */
function filenameValid($name) {
	return true;
}

/**
 * TODO comments
 * TODO exceptions, errors, etc.
 *
 */
function sendTar($filename, $package, $dependencies) {
	$baseDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . DIR_ALL . DIRECTORY_SEPARATOR;
	$packDir = $baseDir . DIR_PACKAGE . DIRECTORY_SEPARATOR;
	$libDir = $baseDir . DIR_LIB . DIRECTORY_SEPARATOR;
	$outDir = $baseDir . DIR_OUT . DIRECTORY_SEPARATOR; 
	
	$tempFile = $outDir . $filename . ".tar";
	
	$phar = new PharData($tempFile);

	$packageWithVersion = $package["name"] . "-" . $package["version"];
	$phar->buildFromIterator(
		new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($packDir . $packageWithVersion, FilesystemIterator::SKIP_DOTS)),
			$baseDir);
	
	foreach($dependencies as $dependency) {
		$dependencyWithVersion = $dependency["name"] . "-" . $dependency["version"];
		$phar->buildFromIterator(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($libDir . $dependencyWithVersion, FilesystemIterator::SKIP_DOTS)),
				$baseDir);
	}
	
	$phar->compress(Phar::GZ);
	
	$fileToSend = $tempFile . ".gz";
	if (file_exists($fileToSend)) {
		header('Content-Description: Archive Transfer');
		header('Content-Type: application/x-compressed');
		header('Content-Disposition: attachment; filename="' . basename($fileToSend) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($fileToSend));
		readfile($fileToSend);
	}
	
	unlink($fileToSend); // delete tar.gz
	
	unset($phar);
    Phar::unlinkArchive($tempFile); // delete tar
	
	echo(TAR_OK);
	
	exit();
}