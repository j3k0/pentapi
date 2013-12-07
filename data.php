<?php

function loadData() {
    $jsonstring = file_get_contents(DATA_JSON);
    return json_decode($jsonstring, TRUE);
}

function saveData($data) {
    $jsonstring = json_encode($data, JSON_PRETTY_PRINT); 
    file_put_contents(DATA_JSON, $jsonstring);
}
