<?php
// Connexion à la base

$host = 'mysql';
$dbname = 'testurururu_somedudes';
$username = '443944';
$password = 'tkD:L!]B9@>y!Gk';//ne pas toucher a cela


// Récupérer l'adresse IP du visiteur
$ip = $_SERVER['REMOTE_ADDR'];


$cookievalue = $ip;

// Le cookie ne doit etre transmis qu en HTTPS lorsque le site y est servi
$secure = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";


setcookie(
    'liste_ip',              // nom du cookie
    $cookievalue,            // valeur du cookie
    time() + 12*24*3600,     // expiration (12 jours)
    '/',                     // chemin (disponible sur tout le site)
    '',                      // domaine (vide = domaine courant)
    $secure,                 // securise : true des que le site est servi en HTTPS
    true                     // HttpOnly (empêche accès via JS)
);


