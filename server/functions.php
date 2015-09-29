<?php
// Constantes
const DB_HOST = "localhost";
const DB_NAME = "package_manager";
const DB_USER = "root";
const DB_PASS = "";

const JSON_OK = 					0;		// La requête est satisfaite, voir le reste du JSON.

const JSON_INVALID_REQUEST = 		1; 		// Requête impossible à satisfaire.
const JSON_PACKAGE_NOT_FOUND = 		2;		// Le paquet est introuvable.
const JSON_VERSION_NOT_FOUND = 		3;		// La version demandée pour ce paquet est introuvable.

const JSON_INVALID_FILENAME = 		4;		// Le nom de fichier demandé pour l'archive est incorrect.
const JSON_FILE_SYSTEM_ERROR = 		5;		// Problème d'écriture de fichier/dossier.
const JSON_DOWNLOAD_ERROR = 		6;		// Problème de download.

const JSON_DATABASE_ERROR = 		126;    // La base de donnée ne fonctionne pas.

const REGEX_PACKAGE = "/^[a-z0-9_+-]+$/i";
const REGEX_VERSION = "/^[0-9]+\\.[0-9]+\\.[0-9]+$/i";
const REGEX_FILENAME = "/^[a-zA-Z]+[0-9a-zA-Z_\\-]+$/i"; // Pas d'espace, pas de point, et commence par une lettre.

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
 * Converti un numéro de version de string (voir REGEX_VERSION) en int pour la comparaison.
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
 * Valide un nom de fichier (voir REGEX_FILENAME).
 * @param string $name Nom de fichier à valider. Si non-validé, renvoie JSON_INVALID_FILENAME et exit.
 *
 */
function filenameValid($name) {
    if (!preg_match(REGEX_FILENAME, $name)) {
        sendJson(JSON_INVALID_FILENAME);
    }
}

/**
 * Créé un dossier s'il n'existe pas déjà, et retourne son chemin,
 * suivi d'un charactere de séparation de chemin de fichier.
 * Si erreur, renvoie JSON_FILE_SYSTEM_ERROR et exit.
 *
 * @param string $dir Chemin du dossier à vérifier/créer
 *
 * @return string
 */
function createDirIfNotHere($dir) {
	if (!file_exists($dir)) {
		if(!mkdir($dir, 0777, true)) {
			sendJson(JSON_FILE_SYSTEM_ERROR);
		}
	}
	return $dir . DIRECTORY_SEPARATOR;
}

/**
 * Rempli une archive déjà créée avec le contenu d'un dossier.
 * Si erreur, renvoie JSON_FILE_SYSTEM_ERROR et exit.
 *
 * @param PharData $phar L'archive à remplir
 * @param string $include Le dossier à inclure
 * @param string $basePath Le chemin à partir duquel l'arborescence doit être copiée
 */
function fillArchive($phar, $include, $basePath) {
	try{
		$phar->buildFromIterator(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($include, FilesystemIterator::SKIP_DOTS)),
				$basePath);
	} catch(UnexpectedValueException $e) {
		sendJson(JSON_FILE_SYSTEM_ERROR);
	}
}

/**
 * Créé et télécharge une archive au format .tar.gz à partir d'un package et de ses dépendences.
 * Si erreur, renvoie JSON_FILE_SYSTEM_ERROR ou JSON_DOWNLOAD_ERROR et exit.
 *
 * @param string $filename Le nom de l'archive à créer/télécharger
 * @param Object $package Les infos du package (doit contenir les strings "name" et "version")
 * @param array[Object] $dependencies Les infos des dépendences (chaque entrée doit contenir les strings "name" et "version")
 */
function sendTar($filename, $package, $dependencies) {
	$baseDir = createDirIfNotHere(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . DIR_ALL);
	$packDir = createDirIfNotHere($baseDir . DIR_PACKAGE);
	$libDir = createDirIfNotHere($baseDir . DIR_LIB);
	$outDir = createDirIfNotHere($baseDir . DIR_OUT);
	
	$tempFile = $outDir . $filename . ".tar";
	try {
		$phar = new PharData($tempFile);
	} catch(UnexpectedValueException $e) {
		sendJson(JSON_FILE_SYSTEM_ERROR);
	}
	
	$packageToInclude = $packDir . $package["name"] . "-" . $package["version"];
	fillArchive($phar, $packageToInclude, $baseDir);
	foreach($dependencies as $dependency) {
		$dependencyToInclude = $libDir . $dependency["name"] . "-" . $dependency["version"];
		fillArchive($phar, $dependencyToInclude, $baseDir);
	}
	
	$phar->compress(Phar::GZ);
	$fileToSend = $tempFile . ".gz";
	
	unset($phar);
	try {
		Phar::unlinkArchive($tempFile); // Suppression du fichier .tar
	} catch(PharException $e) {
		sendJson(JSON_FILE_SYSTEM_ERROR);
	}
	
	header('Content-Description: Archive Transfer');
	header('Content-Type: application/x-compressed');
	header('Content-Disposition: attachment; filename="' . basename($fileToSend) . '"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($fileToSend));
	
	if(!readfile($fileToSend)) {
		sendJson(JSON_DOWNLOAD_ERROR);
	}
	
	if(!unlink($fileToSend)) { // Suppression du fichier .gz
		die(JSON_FILE_SYSTEM_ERROR); // "die" obligatoire car on a déjà utilisé "readFile"
	}
	
	exit();
}
?>