<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function connect() {
    require('parameters.php');


    // connexion au serveur de donn&eacute;es
    @mysql_connect($mysqlserver, $mysqlloggin, $mysqlpassword)
            or print('Connexion impossible putian de merde');
    mysql_set_charset("latin1");
    // S&eacute;lection de la base de donn&eacute;es
    @mysql_select_db($mysqlmaindb)
            or print('can not select BDD putain de merde');

    echo PHP_EOL."connected";
}
// fonction pour faire des reques
function requete($requete) {

    if ($resultat = mysql_query($requete)) {
        return $resultat;
    }
    print( "Erreur dans la requ�te : $requete<br />" . mysql_error());
    print ("
	 reconnexion");
    mysql_close();
    connect();
}

function chope($requete) {
    $tablo = mysql_fetch_assoc(requete($requete));
    if ($tablo) {
        foreach ($tablo as $key => $value) {
            if ($value == 'NULL')
                $tablo[$key] = '';
        }
        return($tablo);
    } else
        return(0);
}

function clean($texteinput) {
  //  $texteinput = utf8_decode($texteinput);
    $str = str_replace('"', "", $texteinput);
    $str = str_replace("\n", "", $str);
    $str = str_replace("\r", "", $str);
    return($str);
}

function list_users($users) {
    foreach ($users as $key => $user) {
        $user = preg_replace('/[^,;a-zA-Z0-9_-]|[,;]$/s', '', $user);
        $users[$key] = $user;
        echo '[' . $user . ']';
    }
    return($users);
}