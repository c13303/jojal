<?php

/* file created by charles.torris@gmail.com */

require('jojal_twitter.php');



$botnames = array('Jojal', 'Jojal2', 'Jojo',$botname);



//$methode = 1;$intelligence = 250000;  // ORIGINAL METHOD WORD COUNT by CHARLES désolé ça reste la meilleure :'(
//$methode = 2; $intelligence = 100000;  // METHODE SIMILAR_TEXT
$methode = 3;$intelligence = 350000;  // METHODE INDICE JACQUARD + SELE



/* fonction indice methode 3 */

function getSimilarityCoefficient($mots, $string2,$bofmots =null) {
    $minWordSize = 3;
    $item1 = $mots;
    $item2 = array();
    $delim = ' \n\t,.!?:;';
    $tok = strtok($string2, $delim);
    while ($tok !== false) {
        if (strlen($tok) > $minWordSize && !in_array($tok,$bofmots)){
            $item2[] = $tok;
        }
        $tok = strtok($delim);
    }
    $arr_intersection = array_intersect($item1, $item2);
    $arr_union = array_unique(array_merge($item1, $item2));
    $coefficient = count($arr_intersection) / count($arr_union);
    return $coefficient;
}


function cleanageToSpeak($say, $qui, $botnames,$pipol,$users) {
    foreach ($botnames as $botna) {
        $say = str_replace($botna, $qui, $say);
        $say = str_replace(strtolower($botna), $qui, $say);
    }
    foreach ($pipol as $pip) {
        if (strlen($pip) > 3) {
            if (in_array($pip, $users))
                $say = str_replace($pip, $qui, $say);
            else
                $say = str_replace($pip, '', $say);
        }
    }
    $adress = rand(1, 3);
    if ($adress == 1)
        $say = $qui . ' : ' . $say;
    $say = preg_replace('/\s{2,}/', ' ', $say);
    return($say);
}

ini_set(default_charset, "ISO-8859-1");
mb_internal_encoding("ISO-8859-1");
echo " it is " . mb_internal_encoding();
$server = "chat.freenode.net";
date_default_timezone_set('Europe/Brussels');

$chance = 0;
/*
$scumming = array();
$scumbuffer = array();
$scumtopic = array();
 * 
 */
$caca = array();
require('bofmots.php');
$chrono = array();

error_reporting(E_ALL);

$min_normal = 5;
$max_normal = 100;
$hot_jojal = 3; // lorsquil est highlight�

$original_chance = 0;
$said = '';
$table_lastid = array();

$qui = $botname;

require('jojal_sql.php');

/* fin des fonctions */

/** connection */
// Prevent PHP from stopping the script after 30 sec
set_time_limit(0);

// Opening the socket to the Rizon network
connect();


$count = chope('SELECT count(iid) AS c FROM `logs` WHERE 1');
$count = $count['c'];

$replica = 0;
$response = $room;
// Force an endless while
while (1) {


    $socket = fsockopen($server, 6667);
    fputs($socket, "USER $botname tarteflure.com CM :CM bot\n");
    fputs($socket, "NICK $botname\n");
    fputs($socket, "JOIN " . $room . "\n");
    fputs($socket, "MODE " . $botname . " -R \n");
    /*
     *
     * SELECTION GENS PRESENTS
     *

     */

    $pipol = array();
    $gens = requete('SELECT count(iid) as nb,nick FROM ' . $table . '  GROUP BY nick  HAVING  count(iid) > 50');
    while ($gen = mysql_fetch_assoc($gens)) {
        $pelo = explode('!', $gen['nick']);
        $pipol[] = $pelo[0];
    }


    if (!$pipol)
        exit('no users in log');


    // END SELECT GENS

    $c = 0;
    // Continue the rest of the script here
    while (1) {
        $h = date('H');
        $m = date('i');
        $shutup = 0;
        $data = fgets($socket, 4096);


        if (!empty($delayshit[$h][$m])) {
            echo 'Chrono';
            $renvoi = $delayshit[$h][$m];
            $delayshit[$h][$m] = null;
            fputs($socket, "PRIVMSG " . $room . " :$renvoi\n");
        }

        if (!$data) {
            $info = stream_get_meta_data($socket);
            if (!$info['timed_out']) {
                break;
            }
            continue;
        }
        $c++;
        echo PHP_EOL . '' . $data . '';

        // Separate all data
        $ex = explode(' ', $data);

        if ($ex[1] == '353') {
            $dec = explode(':', $data);
            $room_users = $dec[2];
            $users = explode(' ', $room_users);
            echo PHP_EOL . 'LiStIng UsErS+-';
            $users = list_users($users);
        }


        // Send PONG back to the server
        if ($ex[0] == "PING") {
            $count = chope('SELECT count(iid) AS c FROM `logs` WHERE 1');
            $count = $count['c'];
            fputs($socket, "PONG " . $ex[1] . "\n");
            $chance = rand($min_normal, $max_normal);
            $totalchance = rand($min_normal, $max_normal);
            // echo PHP_EOL . 'New Ping > New Chance : ' . $chance;
            fputs($socket, "JOIN " . $room . "\n");
           
            /*
            if (rand(0, $maxrandomrange) < $scumchance) {
                $randoms = scum();
                foreach ($randoms as $txt) {
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }*/
             if (rand(0, $maxrandomrange) < $randchance) {
                $randoms = chope('SELECT say FROM ' . $table . ' ORDER BY rand() LIMIT 0,1');
                $txt = $randoms['say'];
                fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                
            }
            if (rand(0, $maxrandomrange) < $tweetchance ) {
                $randoms = tweet($users,$qui);
                foreach ($randoms as $txt) {
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }
        }
        
        if ($ex[1] == 'JOIN') {           

            $newuser = explode('!', $ex[0]);
            $newuser = str_replace(':', '', $newuser[0]);
            echo PHP_EOL . 'NEW USER : ' . $newuser;
            $users[] = $newuser;
            $users = list_users($users);
            
             $de = rand(0, $maxrandomrange);
            if ($de > $hellochance && !in_array($newuser, $botnames)) {
                fputs($socket, "PRIVMSG " . $room . " :salut $newuser\n");
            }
        }

        if ($ex[1] == 'PART' || $ex[1] == 'QUIT') {

            $newuser = explode('!', $ex[0]);
            $newuser = str_replace(':', '', $newuser[0]);
            echo PHP_EOL . 'GONE USER : ' . $newuser;
            $de = rand(0, $maxrandomrange);
            if ($de > $hellochance) {
                fputs($socket, "PRIVMSG " . $room . " :ouf\n");
            }
            if (($key = array_search($newuser, $users)) !== false) {
                unset($users[$key]);
            }
            $users = list_users($users);          

            // add user to list
        }

        if (!$chance)
            $chance = 10;

        if (isset($ex[3]))
            $command = str_replace(array(chr(10), chr(13)), '', $ex[3]);
        if (isset($ex[4]))
            $word = str_replace(array(chr(10), chr(13)), '', $ex[4]);
        if (isset($ex[5]))
            $word2 = str_replace(array(chr(10), chr(13)), '', $ex[5]);






        if ($ex[1] == "PRIVMSG") { /// IF SAY RECEPTION DU MESSAGE
            $ex[0] = substr($ex[0], 1); // speaker
            $pouet = explode('!', $ex[0]);
            $data = str_replace($ex[0], $pouet[0], $data);
            $dit = explode(':', $data);

            echo PHP_EOL . "--- NEW ---------------------------------" . PHP_EOL . "Chance : $chance\n";
            /// REPLY
            $lastscore = 0;
            $nodey = 0;
            $tab = array();
            $tosay = array();
            if (!isset($dit[2]))
                $dit[2] = '';
            if (!isset($dit[3]))
                $dit[3] = '';
            if (!isset($dit[4]))
                $dit[4] = '';
            if (!isset($dit[5]))
                $dit[5] = '';
            if (!isset($dit[6]))
                $dit[6] = '';


            $dit[2] = str_replace(',', ' ', $dit[2] . ' ' . $dit[3] . ' ' . $dit[4] . ' ' . $dit[5] . ' ' . $dit[6]);
            $dit[2] = clean($dit[2]);


            /* splitage de la chaine en mots */
            // $mots = explode(' ', $dit[2]);
            $mots = array();
            $delim = ' \n\t,.!?:;';
            $tok = strtok($dit[2], $delim);
            while ($tok !== false) {
                $mots[] = $tok;
                $tok = strtok($delim);
            }


            $nb_mots = count($mots);
            $qui = explode('!', $ex[0]);
            $qui = $qui[0];
            
            /// public or private
            if ($ex[2] == $botname) {
                $privatemode = 1;
                $response = $qui;
            } else {
                $privatemode = 0;
                $response = $room;
            }
            
            $dey = '';
            echo PHP_EOL . "$qui($privatemode): $dit[2] ($nb_mots mots)";

            /* triggers */

            if (stristr($dit[2], 'taux de ')) {
                $pourcentage = rand(0, 100);
                fputs($socket, "PRIVMSG " . $response . " :" . $pourcentage . "% \n");
            }

            $jour = date('Y-m-d');
            if (strstr($dit[2], 'tweet')) {
                $randoms = tweet($users,$qui);
                foreach ($randoms as $txt) {
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }
/*
            if (strstr($dit[2], 'scum')) {
                $randoms = scum();
                foreach ($randoms as $txt) {
                    sleep(2);
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }
*/
            if (strstr($dit[2], '!help')) {

                fputs($socket, "PRIVMSG " . $qui . " : (en pv) #ano [message anonyme] \n");
                fputs($socket, "PRIVMSG " . $qui . " : (en pv) tano#[heure]h[minute] [message anonyme delayed at heure minute] \n");
            }

            /*
             * ANONYMOUS FEATURE
             */

            if (strstr($dit[2], '#ano') && $privatemode) {

                $renvoi = trim(str_replace('#ano', '', $dit[2]));
                //echo $renvoi;
                $dit[2] = $renvoi;
                fputs($socket, "PRIVMSG " . $room . " :$renvoi\n");
                $shutup = 1;
            }

            /*
             * DELAYED ANO
             * #tano#19h30#Ma bite est un cactus
             */
            if (strstr($dit[2], 'tano#') && $privatemode) {
                $shutup = 1;
                $code = explode('#', $dit[2]);
                $tosay = $code[2];
                $time = explode('h', $code[1]);
                $heure = $time[0];
                $minute = $time[1];
                $delayshit[$heure][$minute] = $tosay;
                fputs($socket, "PRIVMSG " . $response . " : Message prévu pour $heure h $minute \n");
            }

            if (strstr($dit[2], 'time') && $privatemode) {
                $shutup = 1;
                echo "\n$h $m\n";
            }
            
            if (strstr($dit[2], 'qui a deja dit ') || strstr($dit[2], 'qui a déjà dit ')) {
                $shutup = 1;
                $string = $dit[2];
                $string = str_replace('?','',$string);
                $string = str_replace('"','',$string);
                $string = str_replace('é','e',$string);
                $string = str_replace('à','a',$string);
                $string = explode('qui a deja dit ',$string);
                $string = $string[1];
               
                $str = trim($string);
                
                $q = 'SELECT nick,count(iid) AS con FROM logs WHERE say LIKE "%'.$str.'%" GROUP BY nick ORDER BY con DESC LIMIT 0,3';
               
                $existe = requete($q);
                $rep = '';
                while ($e = mysql_fetch_assoc($existe)) {
                    $rep .= $rep ? ', ' : '';
                    $rep .=$e['nick'].' x '.$e['con'];
                }
                if(!$rep){
                    $rep = "Personne n'a jamais dit ça ...";           
                }
                fputs($socket, "PRIVMSG " . $response . " : " . $qui . ": " . $rep . " \n");
            }
            
             if (strstr($dit[2], 'que dirait ')) {
                 $shutup = 1;
                $string = $dit[2];
                $string = str_replace('?','',$string);
                $string = str_replace('"','',$string);
                $string = explode('que dirait ',$string);
                $string = explode(' ',$string[1],2);
                $speaker = trim($string[0]);
                $reste = explode('de ',$string[1]);
                $what = trim($reste[1]);
                $sql = 'SELECT say FROM logs WHERE nick="'.$speaker.'" AND say LIKE "%'.$what.'%" ORDER BY rand() LIMIT 0,1';
              //  echo PHP_EOL."$string[1] $reste[0] Compte : ".$sql;
                $rep = chope($sql);
                if(!$rep){
                    $citation = "Il n'en dirait rien ...";
                } else {
                    $citation = "\"".trim($rep['say'])."\" ($speaker)"; 
                }
                 fputs($socket, "PRIVMSG " . $response . " : " . $qui . ": " . $citation . " \n");
               
             }
            
            
            

            /* POTE QUI FEATURE */

            if (strstr($dit[2], ' pote qui ') || strstr($dit[2], ' potes qui ')) {

                $potequi = chope('SELECT say FROM ' . $table . ' WHERE say LIKE "% pote qui %" ORDER BY rand() LIMIT 0,1');

                foreach ($pipol as $pip) {
                    if (strlen($pip) > 3) {
                        if (in_array($pip, $users))
                            $potequi['say'] = str_replace($pip, $qui, $potequi['say']);
                        else
                            $potequi['say'] = str_replace($pip, '', $potequi['say']);
                    }
                }

                fputs($socket, "PRIVMSG " . $response . " : " . $qui . ": moi " . $potequi['say'] . " \n");
            }

            if (strstr($dit[2], 'rigolo ' . $botname)) {
                $pourcentage = rand(0, 100);
                fputs($socket, "PRIVMSG " . $room . " : " . $qui . ": ^^ \n");
                fputs($socket, "PRIVMSG " . $qui . " :tu refais �a t\'es mort \n");
            }

            /* I.A. REPLY aka the Real Jojal */

            if (strstr($dit[2], $botname) || strstr($dit[2], 'Jojal') || $privatemode == 1) {
                $dey = 1;
                echo PHP_EOL . "-forced to reply-\n";
                $chance = $hot_jojal;
                if (strstr($dit[2], 'vener')) {
                    $chance = 1;
                    fputs($socket, "PRIVMSG " . $response . " :" . $qui . ": pd \n");
                }
                if (strstr($dit[2], 'calme') || strstr($dit[2], 'gueule')) {
                    $chance = 25;
                    fputs($socket, "PRIVMSG " . $response . " : :( \n");
                }

                /* answers to a choice */
                if (strstr($dit[2], ' ou ')) {
                    $dit[2] = str_replace($botname, '', $dit[2]);
                    $dit[2] = str_replace('jojal', '', $dit[2]);
                    $dit[2] = str_replace('?', '', $dit[2]);
                    $dit[2] = str_replace("t'", "m'", $dit[2]);
                    $dit[2] = str_replace(" te ", " me '", $dit[2]);
                    $opts = explode(' ou ', $dit[2]);
                    shuffle($opts);
                    fputs($socket, "PRIVMSG " . $response . " :$qui: $opts[0] \n");
                }
            }



            /* HISTOIRE DE DE */
            if (!$nodey && !$shutup) {
                $neochance = $chance - $lastscore; //// 
                if ($neochance < 1)
                    $neochance = 1;
                // echo PHP_EOL.'RANGE(' . $chance . ') - SCORE (' . $lastscore . ') : NEORANGE : ' . $neochance . '';
                if (!$dey)
                    $dey = rand(1, $neochance);
                if ($privatemode == 1)
                    $dey = 1; // si priv� reponse auto
                $original_chance = $chance;
                $chance = $neochance;
                // echo "[dey=$dey / $chance]";
            }

            /* calculate intelligence bordel ! */
            $randStart = 0;

            if ($dey == 1 && !$nodey && !$shutup) {
                // PARLER : TROUVER LA REPLIQUE CINGLANTE

                /* cools words */
                $coolwords = array();
                foreach ($mots as $key => $wordsaid) {
                    echo 'm';
                    $wordsaid = str_replace(':', '', $wordsaid);
                    $exist = '';
                    $censor = array('jojo', $botname, ucfirst($botname), 'jojal', 'Jojal');
                    $wordsaid = strtolower($wordsaid);
                    if (isset($exist['say']))
                        $exist['say'] = strtolower($exist['say']);
                    if (strlen($wordsaid) > 3 && !in_array($wordsaid, $bofmots)) {
                        $coolwords[] = $wordsaid;
                    }//end key
                }

                foreach ($coolwords as $wordsaid) {
                    $q = 'SELECT iid,say,lastiid FROM ' . $table . ' WHERE say LIKE "%' . $wordsaid . '%" ORDER BY rand() LIMIT ' . $randStart . ',' . $intelligence;
                    $existe = requete($q);
                    while ($exist = mysql_fetch_assoc($existe)) {
                        if (!isset($tab[$exist['iid']]))
                            $tab[$exist['iid']] = 0;
                        if ($methode == 1) {
                            $tab[$exist['iid']] ++;
                        }
                        if ($methode == 2) {
                            similar_text($exist['say'], $dit[2], $percent);
                            $tab[$exist['iid']] += $percent;
                        }
                        if ($methode == 3) {
                            $percent = getSimilarityCoefficient($mots, $exist['say'], $bofmots);
                            $tab[$exist['iid']] += $percent;
                        }
                        $tosay[$exist['iid']] = $exist['lastiid'];
                        $phrastest[$exist['iid']] = $exist['say'];
                        if ($tab[$exist['iid']] > 2) /// si le score est suffisant
                            break(1);
                    }
                }



                $best = '';
                foreach ($tab as $key => $value) {
                    if ($value > $lastscore) {
                        $best = $key;
                        $lastscore = $value;
                        $phrabest = $phrastest[$key];
                    }
                }
                if ($best)
                    echo PHP_EOL . 'Best Match : ' . $lastscore . ': "' . $phrabest . '';
                else
                    echo PHP_EOL . "No Match\n";
                


                if (!isset($alreadysaid))
                    $alreadysaid = array();
                if ($best && !in_array($best, $alreadysaid)) {
                    
                    $ignore = array('McCaca');
                    $ig_req = '';
                    $reponse_b = '';
                    
                    foreach ($ignore as $ignoble) {
                        $ig_req .= ' AND nick!="' . $ignoble . '" ';
                    }
                    /* selection du parent de la réponse, en tant que meilleure réponse  */
                    $what = chope('SELECT iid,say FROM ' . $table . ' WHERE lastiid=' . $best . ' ' . $ig_req . ' LIMIT 0,1;');
                    $answer = $what['say'];
                    if (!$answer && $phrabest != $dit[2]) {
                        echo PHP_EOL . "No parent, taking original copy";
                        $reponse_b = $phrabest;
                    }
                    echo PHP_EOL . "Answer : << $answer >>";
                    

                    if (in_array($best, $alreadysaid))
                        echo PHP_EOL . '-----abort : already said----';

                    $replica++;

                    if ($replica > 300) {
                        echo PHP_EOL."{RESETING}";
                        $alreadysaid = array();
                        $replica = 0;                       
                    }


                    if (!$said) { //
                        if ($what['say']) { //// la phrase a dire
                            //en cas de reponse B
                            if ($reponse_b) {
                                $what['say'] = $reponse_b;
                            }
                            
                           $what['say']=cleanageToSpeak($what['say'],$qui,$botnames,$pipol,$users);

                            if (!strstr($what['say'], 'http')) {
                                fputs($socket, "PRIVMSG " . $response . " :" . $what['say'] . "\n");
                            }
                            usleep(3000000);
                        }
                        $alreadysaid[] = $best;
                    } else {
                        echo PHP_EOL."error, nothing to say (?)";
                    }
                }
            } // no dey
            else {
                echo PHP_EOL . "- Shut - ";
            }

            // Insertion de la replique dans la BDD

            if ($privatemode == 0) {
                if (!isset($table_lastid[0]))
                    $table_lastid[0] = 0;
                $lastid = $table_lastid[0];
            }
            if ($privatemode == 1) {
                if (!isset($table_lastid[$qui]))
                    $table_lastid[$qui] = 0;
                $lastid = $table_lastid[$qui];
            }

            $dit[2] = strtolower($dit[2]);
            $exist = chope('SELECT * FROM ' . $table . ' WHERE say="' . $dit[2] . '" LIMIT 0,1'); // verification pas dej� entree
            
            if ($dit[2] != $exist['say']) {
                if (!strstr($dit[2], 'http')){
                    requete('INSERT INTO ' . $table . '(nick,say,lastiid,private) VALUES("' . $qui . '","' . $dit[2] . '",' . $lastid . ',' . $privatemode . ');');
                }
                $justid = mysql_insert_id();
                
                //requete('INSERT INTO logrelation(lid,pid) VALUES('.$justid.','.$lastid.')');

                $lastid = $justid;
                
                


                // stockage lastidd selon la discussion
                if ($privatemode == 0)
                    $table_lastid[0] = $lastid;
                if ($privatemode == 1)
                    $table_lastid[$qui] = $lastid;
            }
            $chance = $original_chance;
        } // END SAY
    }
}