var Game = function(id, pPosition) {
    this.FRAME_RATE = 100;
    this.matchID = id;
    this.gameData = new Object();
    this.prevGameState = this.gameData;
    this.board = new Object();
    this.board.xcoord = new Array(0, 317, 318, 318, 319, 320, 320, 286, 254, 220, 186, 153, 154, 156, 158, 160, 192, 224, 257, 289,
            322, 322, 323, 323, 324, 324, 355, 387, 418, 450, 450, 451, 451, 452, 452, 485, 517, 550, 582,
            614, 616, 618, 620, 621, 588, 554, 520, 488, 454, 455, 455, 456, 456, 457, 422, 387, 352,
            387, 387, 387, 387, 387, 189, 222, 255, 288, 387, 387, 387, 387, 585, 552, 519, 486, 512,
            537, 562, 587, 262, 237, 212, 187, 269, 247, 225, 204, 506, 528, 550, 571);

    this.board.ycoord = new Array(0, 471, 440, 409, 379, 348, 318, 318, 318, 318, 318, 318, 290, 261, 232, 205, 205, 205, 205, 205,
            205, 178, 150, 123, 97, 71, 71, 71, 71, 71, 97, 123, 150, 178, 205, 205, 205, 205, 205, 205,
            232, 261, 290, 318, 318, 318, 318, 318, 318, 348, 379, 409, 440, 471, 471, 471, 471, 261, 440,
            409, 379, 348, 261, 261, 261, 261, 97, 123, 150, 178, 261, 261, 261, 261, 370, 391, 412, 434,
            370, 391, 412, 434, 159, 140, 122, 104, 159, 140, 122, 104);

    this.absColour = ['Red', 'White', 'Green', 'Black'];

    this.marbleDiv = new Object();
    this.marbleActive = new Object();

    this.assignMarbles(pPosition);

    this.exitDiv = ['#exit-ne', '#exit-se', '#exit-sw', '#exit-nw'];
    this.exits = [6, 20, 34, 48];

    this.shift = new Object();
    this.shift.south = {
        main: 0,
        home: 2,
        exit: 2
    };
    this.shift.west = {
        main: 14,
        home: 6,
        exit: 3
    };
    this.shift.north = {
        main: 28,
        home: 10,
        exit: 0
    };
    this.shift.east = {
        main: 42,
        home: 14,
        exit: 1
    };

    this.starters = new Object();
    this.starters = {
        1: 0,
        2: 0,
        3: 0,
        4: 0
    }

    this.hi_lite_shift_x = -10;
    this.hi_lite_shift_y = -10;
    this.arrow_shift_x = 13;
    this.arrow_shift_y = 13;
    this.HLTimerId;
    this.MATimerId;
    this.GameTimerID;
    this.syncTimerID;
    this.SCantMove = false;
    this.winner = false;
    this.countdown = 30;
    this.timeoutStatus = 'open';
    this.timeoutValue = 300;

    this.validMoves = new Array();

    this.getGame();

};

Game.prototype.getGame = function() {

    clearTimeout(this.actionTimerID);
    clearTimeout(this.countdownTimerID);
    clearTimeout(this.syncTimerID);
    this.countdown = 30;

    $('#message p').fadeOut('fast');
    $('#message p').text('');

    this.prevGameState = this.gameData;

    $.ajax({
        type: 'POST',
        url: MyAjax.ajaxurl,
        dataType: 'json',
        async: true,
        data: {
            action: 'get_game',
            MatchID: this.matchID
        }
    }).done(function(data) {
        theGame.gameData = data;
        theGame.renderGame();
    });

};

Game.prototype.renderGame = function() {

    this.winner = this.checkWin();

    this.renderDice();


    if (!this.winner) {

        //check if this game id under a timeout

        if (this.gameData.status == 'timeout') {

            this.timeoutValue = this.gameData.timeout_value;
            this.timeout();

        } else {

            if (this.gameData.turn == this.player.south) {

                if (this.gameData.turn_status == 'waiting_for_roll') {
                    this.renderRollButton();
                    this.highlightMoves();
                } else {
                    this.marbles = this.checkAllHome();
                    switch (this.checkValidMoves()) {

                        case 0 :
                            this.cantMove();
                            break;

                        case 1 :
                            this.autoMove();
                            break;

                        default :
                            this.activateMarbles();
                    }
                }

            } else {

                this.highlightMoves();
                this.GameTimerID = setTimeout('theGame.getGame()', 5000);

            }

        }

    } else {

        $('#end-game').show('fast');

        $('#rematch').one('click', function() {
            window.location = '/rematch?yes';
        });

        $('#new-game').one('click', function() {
            window.location = '/rematch?no';
        });

        $('#log-out').one('click', function() {
            window.location = '/rematch?out';
        });

    }

};

Game.prototype.renderMarbles = function() {
    var i, x, y, key, marbles, absHole;

    for (var key in this.marbleDiv) {
        i = this.player[key];
        this.starters[i] = 0;
        marbles = this.gameData['marbles_' + i];

        for (var key2 in marbles) {

            switch (marbles[key2]) {
                case 0:
                    x = this.board.xcoord[72 + this.shift[key].home + 3 - this.starters[i]];
                    y = this.board.ycoord[72 + this.shift[key].home + 3 - this.starters[i]];

                    this.starters[i]++;
                    break;

                default:
                    absHole = this.map(key, marbles[key2]);
                    x = this.board.xcoord[absHole];
                    y = this.board.ycoord[absHole];

            }


            $(this.marbleDiv[key][key2]).css({
                'left': x + 'px',
                'top': y + 'px',
                'display': 'block'
            });

        }

        i++;
    }


};

Game.prototype.assignMarbles = function(pPosition) {

    this.colours = new Array();

    switch (pPosition) {

        case 'player_1':

            this.colours = ['red', 'white', 'green', 'black'];
            this.player = {
                south: 1,
                west: 2,
                north: 3,
                east: 4
            };

            break;

        case 'player_2':

            this.colours = ['white', 'green', 'black', 'red'];
            this.player = {
                south: 2,
                west: 3,
                north: 4,
                east: 1
            };

            break;

        case 'player_3':

            this.colours = ['green', 'black', 'red', 'white'];
            this.player = {
                south: 3,
                west: 4,
                north: 1,
                east: 2
            };

            break;

        default:

            this.colours = ['black', 'red', 'white', 'green'];
            this.player = {
                south: 4,
                west: 1,
                north: 2,
                east: 3
            };

    }

    var key;

    this.marbleDiv.south = new Array();
    this.marbleDiv.west = new Array();
    this.marbleDiv.north = new Array();
    this.marbleDiv.east = new Array();

    this.marbleActive.south = new Array();
    this.marbleActive.west = new Array();
    this.marbleActive.north = new Array();
    this.marbleActive.east = new Array();

    for (i = 1; i <= 4; i++) {

        key = '#marble-' + this.colours[0] + '-' + i;
        this.marbleDiv.south.push(key);
        key = '#marble-' + this.colours[1] + '-' + i;
        this.marbleDiv.west.push(key);
        key = '#marble-' + this.colours[2] + '-' + i;
        this.marbleDiv.north.push(key);
        key = '#marble-' + this.colours[3] + '-' + i;
        this.marbleDiv.east.push(key);

        key = '#active-' + this.colours[0] + '-' + i;
        this.marbleActive.south.push(key);
        key = '#active-' + this.colours[1] + '-' + i;
        this.marbleActive.west.push(key);
        key = '#active-' + this.colours[2] + '-' + i;
        this.marbleActive.north.push(key);
        key = '#active-' + this.colours[3] + '-' + i;
        this.marbleActive.east.push(key);

    }

};

Game.prototype.map = function(pNum, relHoleNumber) {

    var absHoleNumber;

    if (relHoleNumber == 60) {
        absHoleNumber = 57;
    } else if (relHoleNumber == 0) {
        absHoleNumber = 0;
    } else if (relHoleNumber >= 56 && relHoleNumber <= 59) {
        absHoleNumber = relHoleNumber + this.shift[pNum].home;
    } else {
        absHoleNumber = (relHoleNumber + this.shift[pNum].main) % 56;
        if (absHoleNumber == 0) {
            absHoleNumber = 56;
        }
    }

    return absHoleNumber;
}

Game.prototype.renderRollButton = function() {

    clearTimeout(this.GameTimerID);

    if (this.gameData.status != 'timeout') {

        this.actionTimerID = setTimeout('theGame.timeWarning("roll")', 30000);

    }

    this.skipMessage = false;

    $('button#roll').show('fast');
    $('button#roll').one('click', function() {
        $.ajax({
            type: 'POST',
            url: MyAjax.ajaxurl,
            async: true,
            data: {
                action: 'game_roll',
                MatchID: theGame.matchID
            }
        }).done(function() {
            theGame.getGame();
        });

        $('button#roll').hide();

    });
};


Game.prototype.renderDice = function() {

    var dice;

    if (this.gameData.last_roll != 0) {

        if (this.gameData.roll_count != this.prevGameState.roll_count) {

            if (this.gameData.turn_status == 'waiting_for_roll' && this.gameData.last_roll < 6) {

                //render dice of previous turn player
                //dice = (this.prevGameState.turn - this.player.south + 7) % 4 + 1;
                for (var j = 1; j <= 4; j++) {
                    for (var i = 1; i <= 6; i++) {
                        var to_hide = '#dice-' + j + ' .final-' + i;
                        $(to_hide).hide();
                    }
                }

                dice = (this.gameData.turn - this.player.south + 7) % 4 + 1;
                $('#dice-' + dice + ' .frame-1').show();
                setTimeout('theGame.frameAdvance(1,' + dice + ')', this.FRAME_RATE);

            } else { //waiting for move

                //render dice of current player

                //dice = (this.prevGameState.turn - this.player.south + 7) % 4 + 1;
                for (var j = 1; j <= 4; j++) {
                    for (var i = 1; i <= 6; i++) {
                        var to_hide = '#dice-' + j + ' .final-' + i;

                        $(to_hide).hide();
                    }
                }

                dice = (this.gameData.turn - this.player.south + 4) % 4 + 1;
                $('#dice-' + dice + ' .frame-1').show();
                setTimeout('theGame.frameAdvance(1,' + dice + ')', this.FRAME_RATE);

            }
        } else {

            this.renderMarbles();

        }

    } else {

        this.renderMarbles();

    }

};

Game.prototype.frameAdvance = function(frame, dice) {

    var e = '#dice-' + dice + ' .frame-' + frame;
    var next = '#dice-' + dice + ' .frame-' + (frame + 1);
    $(e).hide();
    $(next).show();
    frame++;
    if (frame < 3) {
        var f = 'theGame.frameAdvance(' + frame + ', ' + dice + ')';
        setTimeout(f, this.FRAME_RATE);
    } else {
        setTimeout('theGame.rollFinal(' + dice + ')', this.FRAME_RATE);
    }

};

Game.prototype.rollFinal = function(dice) {

    $('#dice-' + dice + ' .frame-3').hide();
    $('#dice-' + dice + ' .final-' + this.gameData.last_roll).show();

    setTimeout('theGame.renderMarbles()', 500);

};

Game.prototype.checkValidMoves = function() {

    var total = 4;
    var start_pos = 0;
    var shortcut;

    for (var i in this.marbles) {
        shortcut = false;
        this.validMoves[i] = true;
        switch (this.marbles[i]) {
            case 0 :
                for (var j = 0; j < 4; j++) {
                    if (i != j && this.validMoves[i] == true) {
                        if (this.marbles[j] == 1 || (this.gameData.last_roll != 6 && this.gameData.last_roll != 1)) {
                            this.validMoves[i] = false;
                            total--;
                        }
                    }
                }
                if (this.validMoves[i] == true) {
                    start_pos++;
                    if (start_pos > 1) {
                        total--;
                    }
                }
                break;
            case 60 :
                if (this.gameData.last_roll != 1) {
                    this.validMoves[i] = false;
                    total--;
                }
                break;
            case 6 :
            case 20 :
            case 34 :
            case 48 :
                shortcut = this.shortcutTest(i);
            default :

                if (!shortcut) {
                    for (j = 0; j < 4; j++) {
                        if (i != j && this.validMoves[i] == true) {
                            if (this.marbles[i] + +this.gameData.last_roll >= this.marbles[j] && this.marbles[i] < this.marbles[j]) {
                                this.validMoves[i] = false;
                                total--;
                            }
                            if (this.validMoves[i] == true && +this.marbles[i] + +this.gameData.last_roll > 59) {
                                this.validMoves[i] = false;
                                total--;
                            }
                        }
                    }
                } 
        }
    }

    return total;
};

Game.prototype.cantMove = function() {

    clearTimeout(this.GameTimerID);

    this.SCantMove = true

    $.ajax({
        type: 'POST',
        url: MyAjax.ajaxurl,
        async: true,
        data: {
            action: 'game_cant_move',
            MatchID: theGame.matchID,
            player: theGame.teamTurn
        }
    }).done(function() {
        //document.write(data);
        theGame.getGame();
    });

};

Game.prototype.autoMove = function() {

    var i = 0;
    var moved = false;
    while (!moved) {
        if (this.validMoves[i]) {

            switch (this.marbles[i]) {

                case 0 :
                    i = this.findFirstStart();
                    this.moveMarble(i);
                    break;

                case 6 :
                case 20 :
                case 34 :
                case 48 :

                    if (this.shortcutTest(i)) {
                        this.getShortcut(i);
                    } else {
                        this.moveMarble(i);
                    }

                    break;

                case 60 :
                    this.moveMarble(i);
                    break;
                default :
                    this.moveMarble(i);
            }

            moved = true;
        } else {
            i++;
        }
    }
};

Game.prototype.checkAllHome = function() {
    var home = 4;
    for (var i = 0; i < 4; i++) {
        if (this.gameData['marbles_' + this.player.south][i] < 56 || this.gameData['marbles_' + this.player.south][i] > 59) {
            home--;
        }
    }

    if (home == 4) {
        this.teamDir = 'north';
        this.teamTurn = this.player.north;
        return this.gameData['marbles_' + this.player.north];
    } else {
        this.teamDir = 'south';
        this.teamTurn = this.player.south;
        return this.gameData['marbles_' + this.player.south];
    }

};

Game.prototype.isAllHome = function(player) { //generic version of checkAllHome()

    var home = 4;
    for (var i = 0; i < 4; i++) {
        if (this.gameData['marbles_' + player][i] < 56 || this.gameData['marbles_' + player][i] > 59) {
            home--;
        }
    }

    if (home == 4) {

        return true

    }

}

Game.prototype.shortcutTest = function(i) {
    var shortcut = false;
    if (this.gameData.last_roll == 1) {
        shortcut = true;
        for (var j = 0; j < 4; j++) {
            if (i != j && shortcut == true) {
                if (this.marbles[j] == 60) {
                    shortcut = false;
                }
            }
        }
    }
    return shortcut;
};

Game.prototype.moveMarble = function(i) {

    this.toMove = i;
    switch (this.marbles[this.toMove]) {

        case 0 :
            this.marbles[this.toMove] = 1;
            this.sendMove();
            break;

        case 6 :
        case 20 :
        case 34 :
        case 48 :
            var shortcut = this.shortcutTest(this.toMove);
            var blocked = false;
            for (var j = 0; j < 4; j++) {
                if (this.toMove != j && blocked == false) {
                    if (this.marbles[this.toMove] + +this.gameData.last_roll >= this.marbles[j] && this.marbles[this.toMove] < this.marbles[j]) {
                        blocked = true;
                    }
                }
            }

            if (!blocked && shortcut) {
                this.getShortcut();
            } else if (blocked) {
                this.marbles[this.toMove] = 60;
                this.sendMove();
            } else {
                this.marbles[this.toMove] += +this.gameData.last_roll;
                this.sendMove();
            }
            break;

        case 60 :
            this.activateExits();
            break;

        default :
            this.marbles[this.toMove] += +this.gameData.last_roll;
            this.sendMove();
    }

};

Game.prototype.getShortcut = function() {

    $('#message p').text('Take shortcut?');
    $('#message p').fadeIn('fast');
    $('button#yes').show();
    $('button#no').show();
    $('button#yes').on('click', function() {
        theGame.shortcut(true)
    });
    $('button#no').on('click', function() {
        theGame.shortcut(false)
    });
};

Game.prototype.shortcut = function(opt) {

    $('#message p').fadeOut('fast');
    $('#message p').text('');
    $('button#yes').off('click').hide();
    $('button#no').off('click').hide();

    if (opt) {

        this.marbles[this.toMove] = 60;

    } else {

        this.marbles[this.toMove]++;

    }

    this.sendMove();

};

Game.prototype.findFirstStart = function() {

    var found = false;
    var k = 0;
    var i, x, y;
    while (!found) {
        i = 0;
        while (!found && i <= 3) {
            x = parseInt($(this.marbleDiv[this.teamDir][i]).css('left'));
            if (x == this.board.xcoord[k + 72 + this.shift[this.teamDir].home]) {
                y = parseInt($(this.marbleDiv[this.teamDir][i]).css('top'));
                if (y == this.board.ycoord[k + 72 + this.shift[this.teamDir].home]) {
                    found = true;
                } else {
                    i++;
                }
            } else {
                i++;
            }
        }
        k++;
    }
    return i;
};

Game.prototype.activateMarbles = function() {

    clearTimeout(this.GameTimerID);

    this.actionTimerID = setTimeout('theGame.timeWarning("move")', 60000);

    for (var i in this.validMoves) {

        if (this.validMoves[i]) {

            $(this.marbleActive[this.teamDir][i]).show('fast');
        }
    }

    $('#message p').text('Choose marble to move!');
    $('#message p').fadeIn('fast');

    //turn on event listener

    $('#board').one('mousedown', function(evt) {
        theGame.onBoardClick(evt)
    });

};

Game.prototype.deactivateMarbles = function() {
    for (var i in this.validMoves) {
        if (this.validMoves[i]) {
            $(this.marbleActive[this.teamDir][i]).hide();
        }
    }

    $('#message p').fadeOut('fast');
    $('#message p').text('');

};

Game.prototype.onBoardClick = function(evt) {

    var x, y;
    //find absolute hole# of marble clicked
    var found = false;
    var i = 0;
    while (!found && i < 4) {
        x = 12 + $(this.marbleDiv[this.teamDir][i]).offset().left;
        y = 10 + $(this.marbleDiv[this.teamDir][i]).offset().top;
        if (Math.abs(evt.pageX - x) < 15) {
            if (Math.abs(evt.pageY - y) < 15) {
                if (this.validMoves[i]) {
                    found = true
                }
                var rel_hole_number = this.marbles[i];
                if (rel_hole_number == 0) {
                    i = this.findFirstStart();
                }
            } else {
                i++;
            }
        } else {
            i++;
        }
    }

    if (found) {
        this.deactivateMarbles();
        this.moveMarble(i);
    } else {

        $('#board').one('mousedown', function(evt) {
            theGame.onBoardClick(evt)
        });

    }

};

Game.prototype.sendMove = function() {

    clearTimeout(this.GameTimerID);

    this.highlightMoves();

    $.ajax({
        type: 'POST',
        url: MyAjax.ajaxurl,
        async: true,
        data: {
            action: 'game_move',
            MatchID: theGame.matchID,
            player: theGame.teamTurn,
            marbles: theGame.marbles
        }
    }).done(function(data) {
        theGame.getGame();
        //document.write(data);
    });

};

Game.prototype.activateExits = function() {

    for (var i in this.exits) {

        var empty = true;

        for (var j in this.marbles) {

            if (j != this.toMove) {

                if (this.marbles[j] == this.exits[i]) {

                    empty = false;
                }
            }
        }

        if (empty) {

            var iShift = this.shift[this.teamDir].exit;
            iShift = (parseInt(i) + parseInt(iShift)) % 4;
            $(this.exitDiv[iShift]).show().on('mousedown', function() {
                theGame.exitClick(this)
            });

        }
    }

    $('#message p').text('Choose an exit!');
    $('#message p').fadeIn('fast');
};

Game.prototype.exitClick = function(obj) {

    var target = '#' + obj.id;
    //map obj.id to an absolute hole number

    for (var i in this.exitDiv) {
        $(this.exitDiv[i]).hide().off('mousedown');
        if (target == this.exitDiv[i]) {
            var abs_exit = (parseInt(i) + 2) % 4;
        }
    }

    this.marbles[this.toMove] = (this.exits[abs_exit] + 56 - this.shift[this.teamDir].main) % 56;

    $('#message p').fadeOut('fast');
    $('#message p').text('');

    this.sendMove();

};

Game.prototype.showMoveArrow = function(o_x, o_y, x, y) {

    clearTimeout(this.MATimerId);
    var theta;
    var m, vx, vy;
    o_x = o_x + +this.arrow_shift_x;
    o_y = o_y + +this.arrow_shift_y;
    x = x + +this.arrow_shift_x;
    y = y + +this.arrow_shift_y;
    var dx = x - o_x;
    if (dx < 0) {
        vx = -1;
    } else {
        vx = 1;
    }
    var dy = y - o_y;
    if (dy < 0) {
        vy = -1;
    } else {
        vy = 1;
    }
    var d = Math.sqrt(dx * dx + dy * dy);
    if (dx != 0) {
        m = Math.abs(dy / dx);
    } else {
        m = 9999;
    }
    var dd = d - 30;
    var ddx = Math.sqrt(dd * dd / (m * m + 1));
    var ddy = Math.abs(m * ddx);
    var clip_x = (Math.abs(dx) - ddx) / 3;
    var clip_y = (Math.abs(dy) - ddy) / 3;

    var new_x = o_x + vx * ddx;
    var new_y = o_y + vy * ddy;
    var a_x = o_x + vx * (ddx + clip_x);
    var a_y = o_y + vy * (ddy + clip_y);

    if (vx == 1 && vy == 1) {
        theta = Math.atan(m) + (1 / 2) * Math.PI;
    } else if (vx == 1 && vy == -1) {
        theta = (1 / 2) * Math.PI - Math.atan(m);
    } else if (vx == -1 && vy == -1) {
        theta = -(1 / 2) * Math.PI + Math.atan(m);
    } else if (vx == -1 && vy == 1) {
        theta = -(1 / 2) * Math.PI - Math.atan(m);
    }
    //alert(theta);
    var canvas = document.getElementById('move-arrow');
    var ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#ff1bff';
    ctx.fillStyle = '#ff1bff';
    ctx.lineWidth = 5.0;
    ctx.clearRect(0, 0, 800, 600);

    ctx.save();
    ctx.beginPath();
    ctx.moveTo(o_x, o_y);
    ctx.lineTo(new_x, new_y);
    ctx.closePath();
    ctx.stroke();
    ctx.restore();

    ctx.save();

    ctx.translate(a_x, a_y);
    ctx.rotate(theta);
    ctx.beginPath();
    ctx.moveTo(0, 0);
    ctx.lineTo(10, 17.32);
    ctx.lineTo(-10, 17.32);
    ctx.closePath();
    ctx.fill();
    ctx.restore();


    $('#move-arrow').show();
    this.MATimerId = setTimeout("$('#move-arrow').hide()", 2000);

};

Game.prototype.highlightMoves = function() {

    var newMarbles, oldMarbles, absPos, oldX, oldY, x, y, moved, message, SCantMove, othersCantMove, distance, totalDistance;
    var checkPlayer = (+this.gameData.turn + 2) % 4 + 1;
    var sixesDelay = 1;

    if (this.isAllHome(checkPlayer)) {
        checkPlayer = this.gameData.turn % 4 + 1;
    }

    var i, j;

    if (this.prevGameState.last_roll != undefined) {

        for (i in this.player) {

            moved = false;

            oldMarbles = this.prevGameState['marbles_' + this.player[i]];
            newMarbles = this.gameData['marbles_' + this.player[i]];

            totalDistance = 0;

            for (j in oldMarbles) {

                if (oldMarbles[j] != newMarbles[j]) {

                    //calculate distance travelled

                    if (newMarbles[j] == 60) {

                        switch (oldMarbles[j]) {


                            case 42 :
                            case 36 :
                            case 30 :
                            case 24 :
                            case 28 :
                            case 12 :


                                distance = 48 - oldMarbles[j];
                                break;

                            case 34 :
                            case 28 :
                            case 22 :
                            case 16 :
                            case 10 :
                            case 4 :

                                distance = 34 - oldMarbles[j];
                                break;


                            case 14 :
                            case 8 :
                            case 2 :

                                distance = 20 - oldMarbles[j];
                                break;

                            case 48 :
                            case 20 :
                            case 6 :
                            default :

                                distance = 1;

                        }

                    } else if (oldMarbles[j] == 0) {

                        distance = newMarbles[j] + 5;

                    } else {

                        distance = newMarbles[j] - oldMarbles[j];

                    }

                    totalDistance += distance;

                    if (newMarbles[j] != 0) { //do not render if 0

                        moved = true;

                        //get the new and old absolute marble position
                        //old

                        if (oldMarbles[j] == 0) {

                            absPos = 72 + this.shift[i].home + 4 - this.starters[this.player[i]];

                        } else {

                            absPos = this.map(i, oldMarbles[j]);

                        }


                        oldX = this.board.xcoord[absPos];
                        oldY = this.board.ycoord[absPos];

                        absPos = this.map(i, newMarbles[j]);
                        x = this.board.xcoord[absPos];
                        y = this.board.ycoord[absPos];

                        this.showMoveArrow(oldX, oldY, x, y);

                        var x_off = x + this.hi_lite_shift_x;
                        var y_off = y + this.hi_lite_shift_y;

                        $('#move-highlight').css({
                            'left': x_off + 'px',
                            'top': y_off + 'px'
                        }).fadeIn(50);

                        clearTimeout(this.HLTimerId);
                        this.HLTimerId = setTimeout("$('#move-highlight').fadeOut(500)", 1000);

                    } else {

                        absPos = 72 + this.shift[i].home + 4 - this.starters[this.player[i]];
                        x = this.board.xcoord[absPos];
                        y = this.board.ycoord[absPos];
                        var x_off = x + this.hi_lite_shift_x;
                        var y_off = y + this.hi_lite_shift_y;

                        //show kick highlight
                        $('#kick-highlight').css({
                            'left': x_off + 'px',
                            'top': y_off + 'px'
                        });

                        $('#kick-highlight').fadeIn();
                        setTimeout("$('#kick-highlight').fadeOut(1000)", 2000);

                        //diplay the kick message
                        message = this.absColour[this.player[i] - 1] + ' was kicked!!';
                        $('#message p').text(message);
                        $('#message p').fadeIn('fast');

                        sixesDelay = 1000;

                    }

                }

            }

            if (totalDistance > 6) {

                var sixes = Math.floor(totalDistance / 6);
                var lastRollMessage;

                if (this.gameData.last_roll == 6) {

                    lastRollMessage = '';

                } else if (this.gameData.last_roll == 1) {

                    lastRollMessage = ' and a 1';

                } else {

                    lastRollMessage = ' and a ' + this.gameData.last_roll + "!";

                }

                if (sixes == 1) {

                    sixes = '1 six';

                } else {

                    sixes = sixes + ' sixes';

                }

                message = sixes + lastRollMessage;
                var f = "$('#message p').text('" + message + "').fadeIn('fast')";
                setTimeout(f, sixesDelay);
            }


            if (this.player[i] == checkPlayer) {

                othersCantMove = (!moved
                        && this.gameData.roll_count > this.prevGameState.roll_count
                        && this.gameData.turn_status == 'waiting_for_roll'
                        && this.prevGameState.last_roll != 6
                        && this.gameData.last_roll != 6);


                if ((this.SCantMove && this.gameData.last_roll != 6) || othersCantMove) {

                    message = this.absColour[checkPlayer - 1] + ' has no moves.';

                    $('#message p').text(message);
                    $('#message p').fadeIn('fast');

                    this.SCantMove = false;

                }

            }

        }

    }

};

Game.prototype.capitalize = function(s) {

    var firstLetter = s.substr(0, 1);
    var theRest = s.substr(1);

    return firstLetter.toUpperCase() + theRest;

};

Game.prototype.checkWin = function() {

    var message = '';

    if (this.gameData.status == 'forfeit') {

        message = this.absColour[this.gameData.turn - 1] + ' has left the game. ';

    }

    switch (this.gameData.winner) {

        case '1' :

            message += 'Team Red / Green Wins!!';
            $('#message p').text(message);
            $('#message p').fadeIn('fast');
            return true;

        case '2' :

            message += 'Team White / Black Wins!!';
            $('#message p').text(message);
            $('#message p').fadeIn('fast');
            return true;

        default :

            return false;

    }

};

Game.prototype.leaveGame = function() {

    clearTimeout(this.GameTimerID);

    var message = 'Are you sure you want to resign this game?';
    $('#message p').text(message);
    $('#message p').fadeIn('fast');

    $('button#roll').hide('fast');
    $('button#yes').show('fast');
    $('button#no').show('fast');
    $('button#yes').on('click', function() {
        theGame.forfeitGame();
    });

    $('button#no').on('click', function() {

        $('button#yes').off('click').hide();
        $('button#no').off('click').hide();
        theGame.resumeGame();

    });

};

Game.prototype.forfeitGame = function() {

    $('button#yes').off('click').hide();
    $('button#no').off('click').hide();

    $.ajax({
        type: 'POST',
        url: MyAjax.ajaxurl,
        async: true,
        data: {
            action: 'game_forfeit',
            MatchID: theGame.matchID,
            player: theGame.player.south
        }
    }).done(function(data) {
        theGame.getGame();
        //document.write(data);
    });

};

Game.prototype.resumeGame = function() {

    $('button#yes').off('click').hide();
    $('button#no').off('click').hide();

    this.getGame();

};

Game.prototype.timeWarning = function(action) {

    var f;

    $('#message p').text('You have ' + this.countdown + ' sec. to ' + action + ' before your team forfeits the game.');
    $('#message p').fadeIn('fast');

    this.countdown--;

    if (this.countdown == 0) {

        this.forfeitGame();

    } else {

        f = 'theGame.timeWarning("' + action + '")'
        this.countdownTimerID = setTimeout(f, 999);

    }


};

Game.prototype.timeout = function() {

    //remove any control items and event listeners

    $('button#roll').hide('fast');
    $('button#roll').off('click');

    $('#board').off('mousedown');
    this.deactivateMarbles();
    $('#messagep').hide('fast');
    $('#message p').text('');

    for (var i in this.exitDiv) {
        $(this.exitDiv[i]).hide();
    }

    $('button#yes').off('click').hide();
    $('button#no').off('click').hide();

    //stop any running timers

    clearTimeout(this.countdownTimerID);
    clearTimeout(this.actionTimerID);
    clearTimeout(this.GameTimerID);

    //if the game status is not already in timeout - we need to send it to the server

    if (this.gameData.status != 'timeout') {

        this.timeoutValue = 300;

        this.gameData.timeout_player = this.player.south;

        $('#timeout').hide('fast');
        $('#timeout').remove();

        //send the timeout status to the server

        $.ajax({
            type: 'POST',
            url: MyAjax.ajaxurl,
            async: true,
            data: {
                action: 'game_timeout',
                MatchID: theGame.matchID,
                player: theGame.player.south
            }
        }).done(function(data) {

            //document.write(data);
        });

    }
    //start the message countdown

    this.messageCountdown();
    this.syncTimerID = setTimeout('theGame.syncTimeoutValue()', 10000);

};

Game.prototype.messageCountdown = function() {

    if (this.timeoutValue >= 0) {

        var min = Math.floor(this.timeoutValue / 60);
        var secX10 = Math.floor((this.timeoutValue % 60) / 10);
        var sec = (this.timeoutValue % 60) - (secX10 * 10);

        var message = this.absColour[this.gameData.timeout_player - 1] + ' has called a timeout. ' + min + ':' + secX10 + sec + ' left.';

        $('#message p').text(message);
        $('#message p').fadeIn('fast');

        this.timeoutValue--;
        setTimeout('theGame.messageCountdown()', 1000);


    } else {

        $('#message p').fadeOut('fast');
        this.getGame();

    }

};

Game.prototype.syncTimeoutValue = function() {

    $.ajax({
        type: 'POST',
        url: MyAjax.ajaxurl,
        async: true,
        data: {
            action: 'game_sync_timeout',
            MatchID: theGame.matchID
        }
    }).done(function(data) {

        theGame.timeoutValue = data;
        theGame.syncTimerID = setTimeout('theGame.syncTimeoutValue()', 10000);
    });

};