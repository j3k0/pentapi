Pentapi
=======

Pentapi is a open server for the board game of Blokus.

Its an experimental project aiming to define open API for client/server blokus communications, as well as provide a reference implementation.

Use cases (not all implemented)
---------

- Add an online mode to a Blokus client.
- Connect an AI with other AIs or real players.
- Allow multiple servers to communicate with each other (to allowing players on one server to play with players on another server)

Internal stuff
--------------

Pentapi relies internally on [Pentobi](http://pentobi.sourceforge.net/)'s [GTP](http://www.lysator.liu.se/~gunnar/gtp/gtp2-spec-draft2/gtp2-spec.html) engine for in-game management, adding to it methods to manage the list of games and players.

Install
-------

Download pentapi from [here](https://github.com/j3k0/pentapi), and pentobi from [there](http://sourceforge.net/p/pentobi/code/ci/master/tree/).

Compile pentobi's GTP engine:

    cd pentobi-code
    cmake -DPENTOBI_BUILD_GTP=ON -DPENTOBI_BUILD_GUI=OFF
    make

Copy pentobi-gtp to pentapi's root directory:

    cp pentobi-code/src/pentobi_gtp/pentobi-gtp pentapi/

Generate pentapi's data file and configuration:

Linux:

    cd pentapi/
    ./reset.sh

Others:

    Look what reset.sh does and do the same by hand (it's pretty simple).
