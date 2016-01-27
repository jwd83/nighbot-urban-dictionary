<?php

# !addcom !ud $(customapi http://jwd.me/twitch/api/urban-dictionary.php?q=$(query))


if(strlen($_GET['q'] <= 200)){

    # define our cache server
    $m = new Memcached();
    $m->addServer('localhost', 11211);

    if($_GET['q'] == '!flush') {
        $m->flush();
        echo 'flushed cache';
        exit();
    }

    # define the cache key for this query
    $mc_key = 'udapi-' . strtolower($_GET['q']);

    # define our output variable to it's default state
    $output = 'Not found - you should go define it FeelsGoodMan';

    # check cache for key
    $cache_hit = $m->get($mc_key);
    if($m->getResultCode() == Memcached::RES_SUCCESS) {
        $output = $cache_hit;
    } else {
        $url = 'http://api.urbandictionary.com/v0/define?term=' . str_replace(' ', '%20', trim($_GET['q']));
        $json = file_get_contents($url);
        $data = json_decode($json);
        if(isset($data->list[0]->definition) ){

            # pull definition from object
            $definition = $data->list[0]->definition;

            # filter out line breaks
            $definition = str_replace("\r", " ", $definition);
            $definition = str_replace("\n", " ", $definition);

            # store key

            $m->set($mc_key, $definition);

            # set output

            $output = $definition;
        }
    }

    echo substr($output, 0, 400);
}
