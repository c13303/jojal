<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require('../PHPcheck/PHPcheck.php');

function wordme($text, $id_log) {


    $delim = ' \n\t,.!?:;';

    $tok = strtok($text, $delim);

    while ($tok !== false) {
        echo PHP_EOL . $tok;
        $word = $tok;
        
        
         $dicos = charge_dicos(array($word));
        
        $correc = correct_word($word,$dicos);
        echo '[';
        echo $correc ? 'faute':'correct';
        echo ']';
                
        
        
        $tok = strtok($delim);
        
        if (!$correc && strlen($word) > 4 && strlen($word) < 20) {
            $idword = chope('SELECT id FROM words WHERE word="' . $word . '"');
            if ($idword['id']) {
                $idword = $idword['id'];
                echo '|exist|';
            } else {
                requete('INSERT INTO words(id,word) VALUES(null,"' . $word . '")');
                $idword = mysql_insert_id();
                echo '|insert|';
            }

            echo '-id' . $idword;




            $connex = chope('SELECT count(id_log) AS con FROM relation WHERE id_word=' . $idword . ' AND id_log = ' . $id_log);
            if ($connex['con']) {
                echo '-con_exists';
            } else {
                requete('INSERT INTO relation(id_log,id_word) VALUES(' . $id_log . ',' . $idword . ')');
                echo '-con created';
            }
        }
    }
    
}
