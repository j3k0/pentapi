<?php
/*
    Pentapi  Copyright (C) 2013  Jean-Christophe Hoelt
    This program comes with ABSOLUTELY NO WARRANTY.
    This is free software, and you are welcome to redistribute it
    under certain conditions.
    See COPYING for details.
*/

header('Content-type: application/json');

require('config.php');
require('data.php');
$data = loadData();
$json = array();

$request = $_SERVER['REQUEST_URI'];
$request = explode('/', $_GET['cmd']);
while ($request[count($request) - 1] === '') array_pop($request);

// Extract payload
$payload = json_decode(file_get_contents('php://input'), TRUE);

// Make sure data is initialized
if (!isset($data['games']))
    $data['games'] = array();
if (!isset($data['players']))
    $data['players'] = array();
if (!isset($data['games-last-id']))
    $data['games-last-id'] = 1;

$pentobi_rules = array(
    'classic' => 'Blokus',
    'classic2' => 'Blokus Two-Player',
    'duo' => 'Blokus Duo'
);

function newGame($rules) {
    global $data;
    if ($rules === 'classic' || $rules === 'classic2' || $rules === 'duo') {
        $data['games-last-id'] += 1;
        $game = array(
            'id'      => $data['games-last-id'],
            'rules'   => $rules,
            'players' => array(),
            'history' => array()
        );
        return $game;
    }
    return null;
}

function findGameIndex($id) {
    global $data;
    $games = $data['games'];
    foreach ($data['games'] as $index => $game) {
        if ($game['id'] === $id)
            return $index;
    }
    return null;
}

function exportGames() {
    global $json;
    global $data;
    $games = array();
    foreach ($data['games'] as $index => $game) {
        $game['players'];
        array_push($games, $game); // array('id' => $game['id']));
    }
    $json['games'] = $games;
}

function newPlayer($privateID, $publicID, $displayName) {
    global $data;
    foreach ($data['players'] as $index => $player) {
        if ($publicID === $player['publicID'] || $privateID === $player['privateID'] || $displayName === $player['displayName'])
            return null;
    }
    return array(
        'publicID' => $publicID,
        'privateID' => $privateID,
        'displayName' => $displayName,
        'points' => 0,
        'ranking' => 0
    );
}

function findPlayerIndexPublic($publicID) {
    global $data;
    $players = $data['players'];
    foreach ($data['players'] as $index => $player) {
        if ($player['publicID'] === $publicID)
            return $index;
    }
    return null;
}

function findPlayerIndexPrivate($privateID) {
    global $data;
    $players = $data['players'];
    foreach ($data['players'] as $index => $player) {
        if ($player['privateID'] === $privateID)
            return $index;
    }
    return null;
}

function exportPlayerPrivate($privateID) {
    global $data, $json;
    $playerIndex = findPlayerIndexPrivate($privateID);
    if ($playerIndex !== null) {
        $json['player'] = $data['players'][$playerIndex];
    }
    else {
        $json['error'] = 'Player not found';
    }
}

function exportPlayer($publicID) {
    global $data, $json;
    $playerIndex = findPlayerIndexPublic($publicID);
    if ($playerIndex !== null) {
        $player = $data['players'][$playerIndex];
        $json['player'] = array(
            'publicID' => $player['publicID'],
            'displayName' => $player['displayName'],
            'points' => $player['points'],
            'ranking' => $player['ranking']
        );
    }
    else {
        $json['error'] = 'Player not found';
    }
}

function runGame($game) {
    global $pentobi_rules;
    $commands = "set_game " . $pentobi_rules[$game['rules']];
    $i = 0;
    foreach ($game['history'] as $index => $move) {
        $commands .= "\n" . (++$i) . " play " . $move;
    }
    $commands .= "\nshowboard";
    $commands .= "\nquit";
    $output = array();
    exec("echo '$commands\n' | ./pentobi-gtp", $output);
    foreach ($output as $index => $line) {
        if ($line === "=$i") {
            // Last move is valid
            return true;
        }
    }
    return false;
}

function getBoard($game) {
    global $pentobi_rules;
    $commands = "set_game " . $pentobi_rules[$game['rules']];
    foreach ($game['history'] as $index => $move) {
        $commands .= "\nplay " . $move;
    }
    $commands .= "\n999 showboard";
    $commands .= "\nquit";
    $output = array();
    exec("echo '$commands\n' | ./pentobi-gtp", $output);
    $append = false;
    $ret = array();
    foreach ($output as $index => $line) {
        if ($line === "=")
            $append = false;
        if ($append) {
            array_push($ret, $line);
        }
        if ($line === "=999")
            $append = true;
    }
    return $ret;
}

switch($request[0]) {

    case 'create_player':
        if (count($request) > 3) {
            $privateID = $request[1];
            $publicID  = $request[2];
            $displayName = $request[3];
            $player = newPlayer($privateID, $publicID, $displayName);
            if ($player !== null) {
                array_push($data['players'], $player);
                // $json['p'] = $data['players'];
                // exportPlayerPrivate($privateID);
                $json['success'] = true;
            }
            else {
                $json['error'] = 'Could not create player.';
            }
        }
        break;

    case 'delete_player':
        if (count($request) > 1) {
            $privateID = $request[1];
            $playerIndex = findPlayerIndexPrivate($privateID);
            if ($playerIndex !== null) {
                unset($data['players'][$playerIndex]);
                $json['success'] = true;
            }
            else {
                $json['error'] = 'Player not found';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'player_private':
        if (count($request) > 1) {
            $privateID = $request[1];
            exportPlayerPrivate($privateID);
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'player':
        if (count($request) > 1) {
            $publicID = $request[1];
            exportPlayer($publicID);
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'games':
        exportGames();
        break;

    case 'create_game':
        if (count($request) > 1) {
            $rules = $request[1];
            if ($rules === 'classic' || $rules === 'classic2' || $rules === 'duo') {
                $game = newGame($rules);
                array_push($data['games'], $game);
                $json['game'] = $game;
            }
            else {
                $json['error'] = 'Invalid rules';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'delete_game':
        if (count($request) > 1) {
            $gameID = (int)$request[1];
            $gameIndex = findGameIndex($gameID);
            if ($gameIndex !== null) {
                unset($data['games'][$gameIndex]);
                $json['success'] = true;
            }
            else {
                $json['error'] = 'Game not found';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'join_game':
        if (count($request) > 2) {
            $gameID   = (int)$request[1];
            $privateID = $request[2];
            $gameIndex = findGameIndex($gameID);
            $game = $data['games'][$gameIndex];
            if ($game['rules'] === 'classic')
                $nplayer = 4;
            else
                $nplayer = 2;
            if ($gameIndex !== null) {
                if (count($data['games'][$gameIndex]['players']) < $nplayer) {
                    $playerIndex = findPlayerIndexPrivate($privateID);
                    if ($playerIndex !== null) {
                        $player = $data['players'][$playerIndex];
                        array_push($data['games'][$gameIndex]['players'], $player['publicID']);
                        $json['game'] = $data['games'][$gameIndex];
                    }
                    else {
                        $json['error'] = 'Player not found';
                    }
                }
                else {
                    $json['error'] = 'Game is full';
                }
            }
            else {
                $json['error'] = 'Game not found';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'play':
        if (count($request) > 3) {
            $gameID = (int)$request[1];
            $privateID = $request[2];
            $move = str_replace("'", "", $request[3]);
            $gameIndex = findGameIndex($gameID);
            if (strlen($move) <= 20) {
                if ($gameIndex !== null) {
                    $game = $data['games'][$gameIndex];
                    if ($game['rules'] === 'classic')
                        $nplayer = 4;
                    else
                        $nplayer = 2;
                    if (count($game['players']) === $nplayer) {
                        $playerIndex = findPlayerIndexPrivate($privateID);
                        if ($playerIndex !== null) {
                            $player = $data['players'][$playerIndex];
                            $playerColor = '';
                            if ($nplayer === 2) {
                                if ($game['players'][0] === $player['publicID']) $playerColor = 'b';
                                if ($game['players'][1] === $player['publicID']) $playerColor = 'w';
                            }
                            elseif ($nplayer === 4) {
                                if ($game['players'][0] === $player['publicID']) $playerColor = '1';
                                if ($game['players'][1] === $player['publicID']) $playerColor = '2';
                                if ($game['players'][2] === $player['publicID']) $playerColor = '3';
                                if ($game['players'][3] === $player['publicID']) $playerColor = '4';
                            }
                            if ($playerColor !== '') {
                                array_push($game['history'], "$playerColor $move");
                                $ok = runGame($game);
                                if ($ok) {
                                    $data['games'][$gameIndex] = $game;
                                    $json['valid_move'] = true;
                                    $json['game'] = $game;
                                }
                                else {
                                    $json['valid_move'] = false;
                                }
                            }
                            else {
                                $json['error'] = 'Player not found in this game';
                            }
                        }
                        else {
                            $json['error'] = 'Player not found on this server';
                        }
                    }
                    else {
                        $json['error'] = 'Game not full';
                    }
                }
                else {
                    $json['error'] = 'Game not found';
                }
            }
            else {
                $json['error'] = 'Invalid parameter: \'move\'';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    case 'showboard':
        if (count($request) > 1) {
            $gameID = (int)$request[1];
            $gameIndex = findGameIndex($gameID);
            if ($gameIndex !== null) {
                $json['board'] = getBoard($data['games'][$gameIndex]);
            }
            else {
                $json['error'] = 'Game not found';
            }
        }
        else {
            $json['error'] = 'Invalid parameters';
        }
        break;

    default:
        $json['error'] = 'Invalid command';
}

echo json_encode($json, JSON_PRETTY_PRINT);
saveData($data);
