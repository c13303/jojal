<?php

$devmode = true;


require('jojal_twitter.php');

$botname = 'Jojal';
$room = '#communication_interne';
$table = 'jojal';
$intelligence = 250000; // nb dentry a parser pour trouver reponse pertinente

if ($devmode) {
    $botname = 'Jojal2';
    $room = '#jojaltest';
    $table = 'logs';
    $intelligence = 100000;
}

ini_set(default_charset, "ISO-8859-1");
mb_internal_encoding("ISO-8859-1");
echo " it is " . mb_internal_encoding();
$server = "chat.freenode.net";
date_default_timezone_set('Europe/Brussels');

$chance = 0;
$scumming = array();
$scumbuffer = array();
$scumtopic = array();
$caca = array();
$bofmots = array();
$chrono = array();

error_reporting(E_ALL);

$min_normal = 5;
$max_normal = 100;
$hot_jojal = 3; // lorsquil est highlight�

$original_chance = 0;
$said = '';
$table_lastid = array();



require('jojal_sql.php');

/* fin des fonctions */

/** connection */
// Prevent PHP from stopping the script after 30 sec
set_time_limit(0);

// Opening the socket to the Rizon network
connect();
$replica = 0;
$response = $room;
// Force an endless while
while (1) {


    $socket = fsockopen($server, 6667);
    fputs($socket, "USER $botname tarteflure.com CM :CM bot\n");
    fputs($socket, "NICK $botname\n");
    fputs($socket, "JOIN " . $room . "\n");
    //  fputs($socket, "PRIVMSG " . $room . " :/mode $botname -R \n");
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
        echo '[data]' . $data . '[end data]';

        // Separate all data
        $ex = explode(' ', $data);

        if ($ex[1] == '353') {

            $dec = explode(':', $data);
            $room_users = $dec[2];
            $users = explode(' ', $room_users);
            echo PHP_EOL.'LiStIng UsErS+-';
            $users = list_users($users);
        }


        // Send PONG back to the server
        if ($ex[0] == "PING") {
            fputs($socket, "PONG " . $ex[1] . "\n");
            $chance = rand($min_normal, $max_normal);
            $totalchance = rand($min_normal, $max_normal);
            echo PHP_EOL . 'New Ping > New Chance : ' . $chance;
            fputs($socket, "JOIN " . $room . "\n");
            $de = rand(0, 999);
            if ($de > 990 && $de < 995) {

                $randoms = chope('SELECT say FROM '.$table.' ORDER BY rand() LIMIT 0,1');
                $randoms = $randoms['say'];
                fputs($socket, "PRIVMSG " . $room . " :$randoms\n");
            }
            if ($de >= 995) {

                $randoms = tweet();
                foreach ($randoms as $txt) {
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }
        }
        if ($ex[1] == 'JOIN') {
            //fputs($socket,"PRIVMSG ".$room." :\x03".rand(0,15).",".rand(0,15)."salur \x03\n");

            $de = rand(0, 999);
            if ($de > 800) {
                fputs($socket, "PRIVMSG " . $room . " :salut\n");
            }



            $newuser = explode('!', $ex[0]);
            $newuser = str_replace(':', '', $newuser[0]);
            echo PHP_EOL.'NEW USER : ' . $newuser;
            $users[] = $newuser;
            $users = list_users($users);

            // add user to list
        }

        if ($ex[1] == 'PART' || $ex[1] == 'QUIT') {


            $newuser = explode('!', $ex[0]);
            $newuser = str_replace(':', '', $newuser[0]);
            echo PHP_EOL.'GONE USER : ' . $newuser;
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
            $ex[0] = substr($ex[0], 1);
            echo PHP_EOL."Pv from: '" . $ex[0] . "\n";
            $pouet = explode('!', $ex[0]);
            $data = str_replace($ex[0], $pouet[0], $data);
            $dit = explode(':', $data);
            /*
            if (strstr($dit[2], 'conjojo')) {
                mysql_close();
                echo "***disconnected***\n";
            }
             * 
             */
            echo PHP_EOL."--- NEW ---------------------------------".PHP_EOL."Chance : $chance\n";
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
            $mots = explode(' ', $dit[2]);
            $nb_mots = count($mots);
            // echo PHP_EOL."ICI c'est $ex[0] ex1: $ex[1] ex2: $ex[2]\n";
            $qui = explode('!', $ex[0]);
            $qui = $qui[0];
            echo PHP_EOL."Interlocuteur : $qui";
            /// public or private
            if ($ex[2] == $botname) {
                $privatemode = 1;
                echo  PHP_EOL."Private mode ! En priv� avec $qui";
                $response = $qui;
            } else {
                $privatemode = 0;
                $response = $room;
            }
            $dey = '';
            echo  PHP_EOL."Phrase reçue: $dit[2] ($nb_mots mots)";
            /*
              if (stristr($dit[2], 'mmm')) {
              $pourcentage = rand(0, 100);
              $terms=array('erotique','bolos','BFA','ta gueule','frais');
              $siz=count($terms) - 1;
              $term=rand(0,$siz);
              fputs($socket, "PRIVMSG " . $response . " : ta gueule :-) \n");
              }
             */
            if (stristr($dit[2], 'taux de ')) {
                $pourcentage = rand(0, 100);

                fputs($socket, "PRIVMSG " . $response . " :" . $pourcentage . "% \n");
            }



            $jour = date('Y-m-d');

            if (strstr($dit[2], 'tweet')) {
                $randoms = tweet();
                foreach ($randoms as $txt) {
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }

            if (strstr($dit[2], 'scum')) {
                $randoms = scum();
                foreach ($randoms as $txt) {
                    sleep(2);
                    fputs($socket, "PRIVMSG " . $room . " :$txt\n");
                }
            }




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



            /* POTE QUI FEATURE */

            if (strstr($dit[2], ' pote qui ') || strstr($dit[2], ' potes qui ')) {

                $potequi = chope('SELECT say FROM '.$table.' WHERE say LIKE "% pote qui %" ORDER BY rand() LIMIT 0,1');

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

            if (strstr($dit[2], ' ou bien ')) {

                $dit[2] = str_replace($botname, '', $dit[2]);
                $dit[2] = str_replace('jojal', '', $dit[2]);
                $dit[2] = str_replace('?', '', $dit[2]);
                $dit[2] = str_replace("t'", "m'", $dit[2]);
                $dit[2] = str_replace(" te ", " me '", $dit[2]);

                $opts = explode(' ou bien ', $dit[2]);
                shuffle($opts);

                fputs($socket, "PRIVMSG " . $response . " :$qui: $opts[0] \n");
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
                echo "[dey=$dey / $chance]\n";
            }



            if ($dey == 1 && !$nodey && !$shutup) {
                // PARLER : TROUVER LA REPLIQUE CINGLANTE
                $methode = 1;
                /* METHODE 1 : COMPARAISON DES CHAINES */
                if ($nb_mots > 4 && $methode == 1)
                    foreach ($mots as $key => $wordsaid) {
                        echo 'm';
                        $wordsaid = str_replace(':', '', $wordsaid);
                        $exist = '';
                        $censor = array('jojo', $botname, ucfirst($botname), 'jojal', 'Jojal');

                        $wordsaid = strtolower($wordsaid);
                        if (isset($exist['say']))
                            $exist['say'] = strtolower($exist['say']);
                        if (strlen($wordsaid) > 3 && !in_array($wordsaid, $bofmots)) {
                            $existe = requete('SELECT iid,say,lastiid FROM ' . $table . ' WHERE say LIKE "%' . $wordsaid . '%" ORDER BY rand() LIMIT 0,' . $intelligence);
                            while ($exist = mysql_fetch_assoc($existe)) {
                                if (!isset($tab[$exist['iid']]))
                                    $tab[$exist['iid']] = 0;
                                $tab[$exist['iid']] ++;
                                $tosay[$exist['iid']] = $exist['lastiid'];
                                $phrastest[$exist['iid']] = $exist['say'];
                                if ($tab[$exist['iid']] > 2) /// si le score est suffisant
                                    break(1);
                            }
                        }//end key
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
                    echo PHP_EOL . 'Meilleure Correspondance : ' . $lastscore . ': [lastiid:' . $best . '] "' . $phrabest . '
            ';
                else
                    echo PHP_EOL . "Pas de correspondance\n";

                if (!isset($alreadysaid))
                    $alreadysaid = array();
                if ($best && !in_array($best, $alreadysaid)) {
                    $ignore = array('McCaca');
                    $ig_req = '';
                    foreach ($ignore as $ignoble) {
                        $ig_req .= ' AND nick!="' . $ignoble . '" ';
                    }
                    /* selection du parent de la réponse, en tant que meilleure réponse  */
                    $what = chope('SELECT iid,say FROM ' . $table . ' WHERE lastiid=' . $best . ' ' . $ig_req . ' LIMIT 0,1;');
                    $answer = $what['say'];
                    echo PHP_EOL . "REPONSE : << $answer >>";
                    $reponse_b = '';

                    if (in_array($best, $alreadysaid))
                        echo '-----deja tire';
                    if ($lastscore < 3) {
                        echo PHP_EOL."Poor Matching ! ($lastscore words)";
                        $dey = 2;

                        //new feature 19/11/2015
                        // au lieu de rien dire, il sort le match correspondant
                        // $reponse_b=$phrabest;
                    }
                    $replica++;

                    if ($replica > 300) {
                        echo "{RESETING}\n";
                        $alreadysaid = array();
                        $replica = 0;
                        fputs($socket, "PRIVMSG " . $room . " : Faites votre devoir de citoyen et votez : http://ouiounon.golmon.fr \n");
                    }


                    if (!$said) { //
                        if ($what['say']) { //// la phrase a dire
                            //en cas de reponse B
                            if ($reponse_b) {
                                $what['say'] = $reponse_b;
                            }

                            $what['say'] = str_replace('jojo', $qui, $what['say']);
                            $what['say'] = str_replace($botname, $qui, $what['say']);
                            $what['say'] = str_replace(strtolower($botname), $qui, $what['say']);




                            foreach ($pipol as $pip) {
                                if (strlen($pip) > 3) {
                                    if (in_array($pip, $users))
                                        $what['say'] = str_replace($pip, $qui, $what['say']);
                                    else
                                        $what['say'] = str_replace($pip, '', $what['say']);
                                }
                            }
                            $adress = rand(1, 3);
                            if ($adress == 1)
                                $what['say'] = $qui . ' : ' . $what['say'];
                            $what['say'] = preg_replace('/\s{2,}/', ' ', $what['say']);
                            if (!strstr($what['say'], 'http')) {
                                fputs($socket, "PRIVMSG " . $response . " :" . $what['say'] . "\n");
                            }
                            usleep(3000000);
                        }
                        $alreadysaid[] = $best;
                    } else {
                        echo "no said (?)\n";
                    }
                }
            } // no dey
            else {
                echo PHP_EOL."- Has been Shut - \n";
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
            $replique_parent = chope("SELECT * FROM $table WHERE iid=$lastid LIMIT 0,1"); // verification la replique parent
            $replica = $replique_parent['say'];

            if ($dit[2] != $exist['say']) {

                // en $private avec $qui

                echo  PHP_EOL."SQL INSERT > $dit[2]";
                if($replica)  echo  PHP_EOL."PARENT > $replica";
                if (!strstr($dit[2], 'http'))
                    requete('INSERT INTO ' . $table . '(nick,say,lastiid,private) VALUES("' . $qui . '","' . $dit[2] . '",' . $lastid . ',' . $privatemode . ');');
                $lastid = mysql_insert_id();

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
?>
