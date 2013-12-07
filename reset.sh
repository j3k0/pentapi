#!/bin/bash
UUID=`cat /proc/sys/kernel/random/uuid`
DATA_JSON="data-${UUID}.json"

cat << EOF > config.php
<?php
define('DATA_JSON', '$DATA_JSON');
EOF

cat << EOF > $DATA_JSON
{
    "games": [
    ],
    "players": [
    ],
    "games-last-id": 0
}
EOF
