<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require('jojal_sql.php');
require('wordage.php');

connect();

requete('TRUNCATE TABLE relation');
requete('TRUNCATE TABLE words');


$repliques = requete('SELECT iid,say FROM logs ORDER BY iid ASC LIMIT 0,1000');
while ($line = mysql_fetch_assoc($repliques)) {

  wordme($line['say'],$line['iid']);
}