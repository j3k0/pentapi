Pentapi
=======

Pentapi is a open server for the board game of Blokus.

Its an experimental project aiming to define open API for client/server blokus communications, as well as provide a reference implementation.

Use cases
---------

- Add an online mode to a Blokus client.
- Connect an AI with other AIs or real players.
- Allow multiple servers to communicate with each other (allowing players on one server to play with players on another server)

Internal stuff
--------------

Pentapi relies internally on [Pentobi](http://pentobi.sourceforge.net/)'s [GTP](http://www.lysator.liu.se/~gunnar/gtp/gtp2-spec-draft2/gtp2-spec.html) engine for in-game management, adding to it management of the list of players and games.

Install
-------

In order to run Pentapi, you need a web server with PHP enabled, with PHP's `exec()`, `json_encore()` and `json_decode()`  functions available.

Download pentapi from [here](https://github.com/j3k0/pentapi), and pentobi from [there](http://sourceforge.net/p/pentobi/code/ci/master/tree/).

Compile pentobi's GTP engine:

    cd pentobi-code
    cmake -DPENTOBI_BUILD_GTP=ON -DPENTOBI_BUILD_GUI=OFF
    make

Copy pentobi-gtp to pentapi's root directory:

    cp pentobi-code/src/pentobi_gtp/pentobi-gtp pentapi/

Generate pentapi's initial data file and configuration:

Linux:

    cd pentapi/
    ./reset.sh

Other OS: Look what reset.sh does and do the same by hand (it's pretty simple).

Then make sure the generated data-something.json file is writable by your webserver.

