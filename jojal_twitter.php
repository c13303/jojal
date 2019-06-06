<?php

/* file created by charles.torris@gmail.com */
require "php-html-parser/vendor/autoload.php";

use PHPHtmlParser\Dom;

function arrayize($data, $utf8 = false, $pipol = null) {
    //echo PHP_EOL.$data;
    if (strstr($data, 'pic.twitter')) {
        return(null);
    }
    echo PHP_EOL .' arrayize '.strlen($data).' chars';
    print_r($pipol);


    $npos = 0;


    if ($pipol) {
        shuffle($pipol);
        $adom = new Dom;
        $adom->load($data);
        $links = $adom->find('a');

        foreach ($links as $link) {

            if ($npos > count($pipol) - 1) {
                $areplacer = " ma bite ";
            } else {
                $areplacer = ' ' . $pipol[$npos] . ' ';
                $npos++;
            }

            //echo PHP_EOL." replace $link";
            $data = str_replace($link, $areplacer, $data);
            $data = str_replace('  ', ' ', $data);
        }
    }




    $txt = html_entity_decode($data);
    if ($utf8) {
        $txt = utf8_decode($txt);
    }
    $txt = str_replace('&#39;', "'", $txt);
    $txt = str_replace('&rsquo;', "'", $txt);
    $txt = str_replace('&#8230;', "...", $txt);

    $txt = strip_tags($txt);
    if (!$txt)
        return null;


    $array = str_split($txt, 512);
    //  echo "\n Scrap : " . sizeof($array) . " parts";



    return($array);
}

function tweet($pipol, $qui, $source = null) {
    $dom = new Dom;

    $filename = '/usr/home/c13/jojal/twittersources.txt';
    $class = '.tweet-text';
    if ($source == 'booba') {
        $class = '.referent';
        $filename = '/usr/home/c13/jojal/geniussources.txt';
    }


    $revolte = file($filename, FILE_IGNORE_NEW_LINES);
    // echo PHP_EOL . count($revolte) . ' sources';


    $rand = rand(0, count($revolte) - 1);
    $selected = $revolte[$rand];

    $twitass = explode(',', $selected);
    $url = $twitass[0];
    $people = $twitass[1];
    if ($people == '$qui') {
        $signe = $pipol[rand(0, count($pipol) - 1)];
    } else {
        $signe = $people;
    }


    $page = file_get_contents($url);

    if (!$page) {
        echo PHP_EOL . "error url : " . $url . ' (' . $rand . ')';
        return(null);
    }
    $dom->load($page);
    $tweets = $dom->find($class);
    $count = count($tweets);
    if (!$count) {
        echo PHP_EOL . ' JojalTweet ERROR ' . $source . ' found ' . $count . ' elements';
        print_r($url);
        return null;
    } else {
        $rand = rand(0, $count - 1);
        echo PHP_EOL . ' JojalTweet  ' . $url . ' found ' . $count . ' elements, taking ' . $rand;
        $selectedTweet = $tweets[$rand];
      //  echo PHP_EOL . ' Length : ' . strlen($selectedTweet);
      
    }

    $array = arrayize($selectedTweet, null, $pipol);

    if (!$array)
        return null;
    echo PHP_EOL;
    print_r($array);


    if($signe)    $array[count($array) - 1] .= ' (' . $signe . ')';

    return($array);
}

function scum() {
    return(null);
    /*
      $dom = new Dom;
      $revolte = array(
      'http://scum.5tfu.org/page/' . rand(1, 27) . '/',
      );
      $rand = rand(0, count($revolte) - 1);

      $url = $revolte[$rand];
      echo PHP_EOL . 'Scrapping URL : ' . $url;
      $ch = curl_init();

      // set url
      curl_setopt($ch, CURLOPT_URL, $url);

      //return the transfer as a string
      $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
      $http_headers = array(
      'User-Agent: ' . $ua,
      );
      curl_setopt($ch, CURLOPT_USERAGENT, $ua);
      curl_setopt($ch, CURLOPT_COOKIE, "visitor_country=FR;wbCookieNotifier=1;acceptableAds=0");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // $output contains the output string
      $output = curl_exec($ch);

      // close curl resource to free up system resources
      curl_close($ch);

      $dom->load($output);
      $tweets = $dom->find('p');
      $result = false;
      while (!$result) {
      $rand = rand(0, count($tweets) - 1);
      if ($tweets[$rand]) {
      $array = arrayize($tweets[$rand]->text, 1);
      $result = true;
      }
      }
      return($array);
     * 
     */
}

function isdead($qui) {
    $url = 'http://estcequejeanmarielepenestmort.info/dedornot.class.php?nom=' . $qui;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
    $http_headers = array(
        'User-Agent: ' . $ua,
    );
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_COOKIE, "visitor_country=FR;wbCookieNotifier=1;acceptableAds=0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);

    $dom = new Dom;
    $dom->load($output);
    $results = $dom->find('p');
    $result = strip_tags($results[0]);
    if (!$result)
        $result = 'le bob service ne sait pas';
    return($result);
}
