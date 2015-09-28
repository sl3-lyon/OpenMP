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
const JSON_DATABASE_ERROR = 126;    // La base de donnée ne fonctionne pas.
const JSON_UNKOWN_ERROR = 127;      // Autre erreur (non identifiée).

const REGEX_PACKAGE = "/^[a-z0-9_+-]+$/i";
const REGEX_VERSION = "/^[0-9]+\\.[0-9]+\\.[0-9]+$/i";

/**
 * Renvoie une instance de la BDD.
 *
 * @return PDO
 */
function getBdd() {
    try {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        sendJson(JSON_DATABASE_ERROR);
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
    $major = $int / 10000;
    $minor = ($int / 100) % 100;
    $rev   = $int % 100;
    return $major . "." . $minor . "." . $rev;
}