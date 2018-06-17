<?php

/* file created by charles.torris@gmail.com */
require "php-html-parser/vendor/autoload.php";

use PHPHtmlParser\Dom;

function arrayize($data){
    $txt = html_entity_decode($data);
    $txt = str_replace('&#39;', "'", $txt);
    $txt = str_replace('&rsquo;', "'", $txt);

    
    $array = str_split($txt, 512);
    echo "\n Scrap : ".sizeof($array)." parts";
    return($array);
}


function tweet() {
    $dom = new Dom;



    $revolte = array(
        'https://twitter.com/nadine__morano',
        'https://twitter.com/laurentwauquiez',
        'https://twitter.com/FrancoisFillon',
        'https://twitter.com/NicolasSarkozy',
        'https://twitter.com/MarleneSchiappa',
    );
    $rand = rand(0, count($revolte) - 1);

    $url = $revolte[$rand];

    $page = file_get_contents($url);

    $dom->load($page);
    $tweets = $dom->find('.tweet-text');

    $rand = rand(0, count($tweets) - 1);
    $array = arrayize($tweets[$rand]->text);
    
    return($array);
}




function web() {

    $dom = new Dom;
    $revolte = array(
        'http://scum.5tfu.org/page/'.rand(1,27).'/',
    );
    $rand = rand(0, count($revolte) - 1);

    $url = $revolte[$rand];
    echo PHP_EOL.'Scrapping URL : '.$url;
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
    while(!$result){
        $rand = rand(0, count($tweets) - 1);
        if($tweets[$rand]){
             $array = arrayize($tweets[$rand]->text);
             $result=true;
        }
    }
    

     return($array);
}

$result=web();
foreach($result as $line){
    echo PHP_EOL.$line.PHP_EOL;
}