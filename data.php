<?php
/*
    Pentapi  Copyright (C) 2013  Jean-Christophe Hoelt
    This program comes with ABSOLUTELY NO WARRANTY.
    This is free software, and you are welcome to redistribute it
    under certain conditions.
    See COPYING for details.
*/

function loadData() {
    $jsonstring = file_get_contents(DATA_JSON);
    return json_decode($jsonstring, TRUE);
}

function saveData($data) {
    $jsonstring = json_encode($data, JSON_PRETTY_PRINT); 
    file_put_contents(DATA_JSON, $jsonstring);
}
