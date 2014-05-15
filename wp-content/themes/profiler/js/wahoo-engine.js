// JavaScript Document
FRAME_RATE = 100;
var roll_value = 1;
var board = new Object();
board.xcoord = new Array(0,317,318,318,319,320,320,286,254,220,186,153,154,156,158,160,192,224,257,289,
    322,322,323,323,324,324,355,387,418,450,450,451,451,452,452,485,517,550,582,
    614,616,618,620,621,588,554,520,488,454,455,455,456,456,457,422,387,352,
    387,387,387,387,387,189,222,255,288,387,387,387,387,585,552,519,486,512,
    537,562,587,262,237,212,187,269,247,225,204,506,528,550,571);
board.ycoord = new Array(0,471,440,409,379,348,318,318,318,318,318,318,290,261,232,205,205,205,205,205,
    205,178,150,123,97,71,71,71,71,71,97,123,150,178,205,205,205,205,205,205,
    232,261,290,318,318,318,318,318,318,348,379,409,440,471,471,471,471,261,440,
    409,379,348,261,261,261,261,97,123,150,178,261,261,261,261,370,391,412,434,
    370,391,412,434,159,140,122,104,159,140,122,104);
var marble_div = new Object;
marble_div.red = ['#marble-red-1', '#marble-red-2', '#marble-red-3', '#marble-red-4'];
marble_div.white = ['#marble-white-1', '#marble-white-2', '#marble-white-3', '#marble-white-4'];
marble_div.green = ['#marble-green-1', '#marble-green-2', '#marble-green-3', '#marble-green-4'];
marble_div.black = ['#marble-black-1', '#marble-black-2', '#marble-black-3', '#marble-black-4'];
var marble_active = new Object;
marble_active.red = ['#active-red-1', '#active-red-2', '#active-red-3', '#active-red-4'];
marble_active.white = ['#active-white-1', '#active-white-2', '#active-white-3', '#active-white-4'];
marble_active.green = ['#active-green-1', '#active-green-2', '#active-green-3', '#active-green-4'];
marble_active.black = ['#active-black-1', '#active-black-2', '#active-black-3', '#active-black-4'];
var exit_div = ['#exit-ne', '#exit-se', '#exit-sw', '#exit-nw'];
var colours = new Array('red','white','green','black');
var marbles = new Object();
var all_home = new Object();
var starters = new Object();
var game_over=false;
var game_pause = false;
var shift = new Object();
shift.red = {
    main:0,
    home:2,
    exit:2
};
shift.white = {
    main:14,
    home:6,
    exit:3
};
shift.green = {
    main:28,
    home:10,
    exit:0
};
shift.black = {
    main:42,
    home:14,
    exit:1
};
var turn = 3;
var team_turn;
var comp_player = new Object();
comp_player.red = false;
comp_player.white = true;
comp_player.black = true;
comp_player.green = true;
var valid_move = new Array();
var active_number;
var board_active = false;
var exits = [6,20,34,48];
var hi_lite_shift_x = -10;
var hi_lite_shift_y = -10;
var arrow_shift_x = 13;
var arrow_shift_y = 13;
var HLTimerId;
var MATimerId;
var MessageTimer;
var game_speed = 1000;
 
$(document).ready(function(){
    for (var i in exit_div) {
        $(exit_div[i]).on('mousedown', function() {
            exitClick(this)
        });	
    }
    $('button#roll').on('mousedown', function (){
        roll()
    });
    $('button#yes').on('mousedown', function (){
        shtcut(true)
    });
    $('button#no').on('mousedown', function (){
        shtcut(false)
    });
    $('button#rs-yes').on('mousedown', function (){
        restart(true)
    });
    $('button#rs-no').on('mousedown', function (){
        restart(false)
    });
    $('#board').on('mousedown', function (evt) {
        onBoardClick(evt)
    });
    $('body').on('keydown', function (e) {
        keyRoll(e)
    });
    $('#menu #gs p').click(function(e) {
        switch (e.target.id) {
            case 'gs-slow' :
                game_speed = 2000;
                break;
            case 'gs-med' :
                game_speed = 1000;
                break;
            default :
                game_speed = 500;
        }
        $('#menu p').removeClass('selected');
        $(e.target).addClass('selected');
    });
    $('#menu #g-restart p').click(function() {
        game_pause = true;
        $('#message p').text('Are you sure you want to restart this game?');
        $('#message p').fadeIn('fast');
        $('button#roll').hide();
        $('button#rs-yes').show();
        $('button#rs-no').show();
    });
    newGame();
});

function newGame() {    
    game_pause = false;
    turn = (getCookie('turn') != undefined) ? Number(getCookie('turn')) : 3;
    for(var i in colours) {
        if(getCookie(colours[i]) != undefined) {
            marbles[colours[i]] = getCookie([colours[i]]).split('-', 4);
        } else {
            marbles[colours[i]] = new Array(0, 0, 0, 0);
        }
    }
    /*marbles.red = new Array(0, 0, 0, 60);
    marbles.white = new Array(0, 0, 0, 0);
    marbles.green = new Array(0, 0, 0, 0);
    marbles.black = new Array(0, 0, 34, 0);*/
    initPositionCustom();    
    rollReady();
}

function keyRoll(e) {
    if(($('button#roll').css('display') == 'inline-block' || $('button#roll').css('display') == 'inline') && e.keyCode == 32) {
        roll();
    }
}

function rollReady() {	
    setCookie('turn', turn, 7);
    for(var i in colours) {
        set_str = '';
        for(var j in marbles[colours[i]]) {
            set_str += marbles[colours[i]][j] + '-';
        }
        setCookie(colours[i], set_str, 7);
    }
    
    //clearTimeout(MessageTimer);
    
    if ($('#message p').text() == 'You have no valid moves!') {
        $('#message p').fadeOut(1000); 
    }
   
    
    var dice = turn + 1;
    var to_hide = '#dice-' + dice + ' .final-' + roll_value;
    $(to_hide).hide();
    if (!game_over) {
        if (roll_value!=6) {
            turn=(turn+1)%4;
        }
        
        if (!comp_player[colours[turn]]) {
            $('button#roll').show();
        } else {
            roll();
        }
    } else {
        message = 'Game Over! Team '+winner+' wins!\n\nPlay Again?';
        $('#message p').text(message);
        //$('#message p').stop(true);
        $('#message p').fadeIn('fast');
        $('button#yes').off();
        $('button#no').off();
        $('button#yes').on('mousedown', function() {
            endGame(false)
        });
        $('button#no').on('mousedown', function() {
            endGame(true)
        });
        $('button#yes').show();
        $('button#no').show();
        setCookie('turn', 3, 7);
        for(var i in colours) {
            setCookie(colours[i], '0-0-0-0-' , 7);
        }
    }
	
//var o = $(marble_div.red[0]).offset();
//alert($(marble_div.red[0]).offset().left);
}


function roll() {
    $('button#roll').blur();
    $('button#roll').hide();
    var dice = turn + 1;
    $('#dice-' + dice + ' .frame-1').show();
    setTimeout('frameAdvance(1,' + dice + ')',FRAME_RATE);
}

function frameAdvance(frame, dice) {
    var e = '#dice-' + dice + ' .frame-' + frame;
    var next = '#dice-' + dice + ' .frame-' + (frame + 1);
    $(e).hide();
    $(next).show();
    frame++;
    if (frame < 3) {
        var f = 'frameAdvance(' + frame + ', ' + dice + ')';
        setTimeout(f,FRAME_RATE);
    } else {
        setTimeout('rollFinal(' + dice + ')',FRAME_RATE);	
    }
}

function rollFinal(dice) {
    roll_value = Math.ceil(Math.random()*6);
    //roll_value = 1;
    $('#dice-' + dice + ' .frame-3').hide();
    $('#dice-' + dice + ' .final-' + roll_value).show();
    team_turn= (all_home[colours[turn]]==4) ? (turn+2)%4 : turn;
    switch (checkValidMoves(roll_value)) {
        case 0 :
            cantMove();
            break;
        case 1 :
            if (!comp_player[colours[team_turn]]) {
                setTimeout('autoMove()', 500);
            } else {
                setTimeout('engineMove()', game_speed);
            }
            break;
        default :
            if (!comp_player[colours[team_turn]]) {
                activateMarbles(colours[team_turn]);
            } else {
                setTimeout('engineMove()', game_speed);
            }
    }
}

function checkValidMoves(roll_value) {
    var total=4;
    var start_pos=0;
    for (var i in marbles[colours[team_turn]]) {
        valid_move[i]=true;
        switch (marbles[colours[team_turn]][i]) {
            case 0 :
                for (var j=0; j<4; j++) {
                    if (i!=j&&valid_move[i]==true) {
                        if (marbles[colours[team_turn]][j]==1 || (roll_value!=6&&roll_value!=1)) {
                            valid_move[i]=false;
                            total--;
                        }
                    }
                }
                if (valid_move[i]==true) {
                    start_pos++;
                    if (start_pos>1) {
                        total--;
                    }
                }
                break;
            case 6 :
            case 20 :
            case 34 :
            case 48 :
                var shortcut=shortcutTest(i);
                for (j=0; j<4; j++) {
                    if (i!=j&&valid_move[i]==true) {
                        if (marbles[colours[team_turn]][i]+roll_value>=marbles[colours[team_turn]][j]
                            && marbles[colours[team_turn]][i]<marbles[colours[team_turn]][j]
                            && !shortcut) {
                            valid_move[i]=false;
                            total--;
                        }
                    }
                }
                break;
            case 60 :
                if (roll_value!=1) {
                    valid_move[i]=false;
                    total--;
                }
                break;
            default :
                for (j=0; j<4; j++) {
                    if (i!=j&&valid_move[i]==true) {
                        if (marbles[colours[team_turn]][i]+roll_value>=marbles[colours[team_turn]][j]
                            && marbles[colours[team_turn]][i]<marbles[colours[team_turn]][j]) {
                            valid_move[i]=false;
                            total--;
                        }
                        if (valid_move[i]==true&&marbles[colours[team_turn]][i]+roll_value>59) {
                            valid_move[i]=false;
                            total--;
                        }
                    }
                }
        }
    }
    return total;
}

function autoMove() {
    var i = 0;
    var moved = false;
    while (!moved) {
        if (valid_move[i]) {
            if (marbles[colours[team_turn]][i]==0) {
                i=findFirstStart();
            }
            moveMarble(colours[team_turn],i);
            moved=true;
        } else {
            i++;
        }
    }
}

function findFirstStart() {
    var found = false;
    var k = 0;
    var i, x, y;
    while (!found) {
        i=0;
        while (!found && i<=3) {
            x = parseInt($(marble_div[colours[team_turn]][i]).css('left'));
            if (x == board.xcoord[k+72+shift[colours[team_turn]].home]) {
                y = parseInt($(marble_div[colours[team_turn]][i]).css('top'));
                if (y == board.ycoord[k+72+shift[colours[team_turn]].home]) {
                    found=true;
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
}

function moveMarble(colour,i) {
    var x, y;
    var waiting = false;
    active_number = i;
    switch (marbles[colour][i]) {
        case 0 :
            starters[colour]-=1;
            marbles[colour][i]++;
            break;
        case 6 :
        case 20 :
        case 34 :
        case 48 :
            var shortcut = shortcutTest(i);
            var blocked = false;
            for (var j=0; j<4; j++) {
                if (i!=j&&blocked==false) {
                    if (marbles[colours[team_turn]][i]+roll_value>=marbles[colours[team_turn]][j]
                        && marbles[colours[team_turn]][i]<marbles[colours[team_turn]][j]) {
                        blocked=true;
                    }
                }
            }

            if (!blocked && shortcut) {
                takeShortcut();
                waiting=true;
            } else if (blocked) {
                marbles[colour][i]=60;
            } else {
                marbles[colour][i]+=roll_value;
            }
            break;
        case 60 :
            activateExits();
            waiting=true;
            break;
        default :
            marbles[colour][i]+=roll_value;
    }
    if (! waiting) {
        var abs_pos = map(colour,marbles[colour][i]);
        if (marbles[colour][i]<56||marbles[colour][i]==60) {
            checkKick(abs_pos);
        } else {
            checkAllHome();
        }
        x = board.xcoord[abs_pos];
        y = board.ycoord[abs_pos];
        var x_off = x + hi_lite_shift_x;
        var y_off = y + hi_lite_shift_y;
        var orig_abs_x = $(marble_div[colour][i]).position().left;
        var orig_abs_y = $(marble_div[colour][i]).position().top;
        $(marble_div[colour][i]).css({
            'left': x+'px',
            'top': y+'px'
        });
        $('#move-highlight').css({
            'left': x_off+'px',
            'top': y_off+'px'
        });
        //$('#move-highlight').stop(true);
        $('#move-highlight').fadeIn(50);
        if ($('#message p').text() == 'Choose marble to move!' || $('#message p').text() == 'Choose an exit!') {
            $('#message p').fadeOut(1000);
        }
        clearTimeout(HLTimerId);
        HLTimerId = setTimeout("$('#move-highlight').fadeOut(500)",1000);
        if (!game_pause) {
            setTimeout('rollReady()',500);
        }
        showMoveArrow(orig_abs_x,orig_abs_y,x,y);
    }
}

function map(colour,rel_hole_number) {
    var abs_hole_number;
    if (rel_hole_number==60) {
        abs_hole_number=57;
    } else if (rel_hole_number==0) {
        abs_hole_number=0;
    } else if (rel_hole_number>=56 && rel_hole_number<=59) {
        abs_hole_number=rel_hole_number+shift[colour].home;
    } else {
        abs_hole_number=(rel_hole_number+shift[colour].main)%56;
        if (abs_hole_number==0) {
            abs_hole_number=56;
        }
    }
    return abs_hole_number;
}

function cantMove() {
    if(comp_player[colours[team_turn]]) {
        $('#message p').text(colours[team_turn]+' has no valid moves!');
        //$('#message p').stop(true);
        $('#message p').fadeIn('fast');
        if (!game_pause) {
            setTimeout('rollReady()',1000);
        }
    } else {
        $('#message p').text('You have no valid moves!');
        //$('#message p').stop(true);
        $('#message p').fadeIn('fast');
        if (!game_pause) {
            setTimeout('rollReady()',1000);
        }
    }	
}

function activateMarbles(colour){
    board_active = true;
    for (var i in valid_move) {
        if (valid_move[i]) {
            $(marble_active[colour][i]).show();
        }
    }
    $('#message p').text('Choose marble to move!');
    //$('#message p').stop(true);
    $('#message p').fadeIn('fast');
}

function deactivateMarbles(colour){
    board_active = false;
    for (var i in valid_move) {
        if (valid_move[i]) {
            $(marble_active[colour][i]).hide();
        }
    }
}

function onBoardClick(evt) {
    if (board_active) {
        var x, y;
        //find absolute hole# of marble clicked
        var found = false;
        var i = 0;
        while (!found && i < 4) {
            x = 12 + $(marble_div[colours[team_turn]][i]).offset().left;
            y = 10 + $(marble_div[colours[team_turn]][i]).offset().top;
            if (Math.abs(evt.pageX - x) < 15) {
                if (Math.abs(evt.pageY - y) < 15) {
                    if(valid_move[i]){
                        found=true
                    }
                    var rel_hole_number = marbles[colours[team_turn]][i];
                    if (rel_hole_number==0) {
                        i=findFirstStart();
                    }
                } else {
                    i++;
                }
            } else {
                i++;
            }
        }
		
        if (found) {
            deactivateMarbles(colours[team_turn]);
            moveMarble(colours[team_turn],i);
        }
    }
}

function relMap(colour,abs_hole_number) {
    var rel_hole_number;
    if (abs_hole_number==57) {
        rel_hole_number=60;
    } else if (abs_hole_number>=58 && abs_hole_number<=73) {
        rel_hole_number=abs_hole_number-shift[colour].home;
    } else if (abs_hole_number>=74) {
        rel_hole_number=0;
    } else {
        rel_hole_number=(abs_hole_number+56-shift[colour].main)%56;
    }
    return rel_hole_number;
}

function shortcutTest(i){
    var shortcut=false;
    if (roll_value==1) {
        shortcut=true;
        for (var j=0; j<4; j++) {
            if (i!=j&&shortcut==true) {
                if (marbles[colours[team_turn]][j]==60) {
                    shortcut=false;
                }
            }
        }
    }
    return shortcut;
}

function takeShortcut() {
    $('#message p').text('Take shortcut?');
    //$('#message p').stop(true);
    $('#message p').fadeIn('fast');
    $('button#yes').show();
    $('button#no').show();	
}
function shtcut(opt) {
    $('#message p').text('');
    $('button#yes').hide();
    $('button#no').hide();
    if (opt) {
        marbles[colours[team_turn]][active_number]=60;
        var abs_pos=57;
    } else {
        marbles[colours[team_turn]][active_number]++;
        var abs_pos = map(colours[team_turn],marbles[colours[team_turn]][active_number]);
    }
	
    checkKick(abs_pos);
    var x = board.xcoord[abs_pos];
    var y = board.ycoord[abs_pos];
    var x_off = x + hi_lite_shift_x;
    var y_off = y + hi_lite_shift_y;
    var orig_abs_x = $(marble_div[colours[team_turn]][active_number]).position().left;
    var orig_abs_y = $(marble_div[colours[team_turn]][active_number]).position().top;
    $(marble_div[colours[team_turn]][active_number]).css({
        'left': x+'px',
        'top': y+'px'
    });
    $('#move-highlight').css({
        'left': x_off+'px',
        'top': y_off+'px'
    });
    //$('#move-highlight').stop(true);
    $('#move-highlight').fadeIn(50);
    clearTimeout(HLTimerId);
    HLTimerId = setTimeout("$('#move-highlight').fadeOut(500)",1000);
    showMoveArrow(orig_abs_x,orig_abs_y,x,y);
    if (!game_pause) {
        setTimeout('rollReady()', 1000);
    }
}

function activateExits() {
    for (var i in exits) {
        var empty=true;
        for (var j in marbles[colours[team_turn]]) {
            if (j!=active_number) {
                if (marbles[colours[team_turn]][j]==exits[i]) {
					
                    empty=false;
                }
            }
        }
        if (empty) {
            var iShift = shift[colours[turn]].exit;
            iShift = (parseInt(i) + parseInt(iShift))%4;
            $(exit_div[iShift]).show();
        }
    }
    $('#message p').text('Choose an exit!');
    //$('#message p').stop(true);
    $('#message p').fadeIn('fast');
}

function exitClick(obj) {
    var target = '#'+obj.id;
    //map obj.id to an absolute hole number
		
    for (var i in exit_div) {
        $(exit_div[i]).hide();
        if (target == exit_div[i]) {
            var abs_exit = (parseInt(i)+2)%4;	
        }		
    }
    marbles[colours[team_turn]][active_number]=(exits[abs_exit]+56-shift[colours[team_turn]].main)%56;
    checkKick(exits[abs_exit]);
    x = board.xcoord[exits[abs_exit]];
    y = board.ycoord[exits[abs_exit]];
    var x_off = x + hi_lite_shift_x;
    var y_off = y + hi_lite_shift_y;
    $(marble_div[colours[team_turn]][active_number]).css({
        'left': x+'px',
        'top': y+'px'
    });
    $('#move-highlight').css({
        'left': x_off+'px',
        'top': y_off+'px'
    });
    //$('#move-highlight').stop(true);
    $('#move-highlight').fadeIn(50);
    clearTimeout(HLTimerId);
    HLTimerId = setTimeout("$('#move-highlight').fadeOut(500)",1000);
    if (!game_pause) {
        setTimeout('rollReady()', 1000);
    }
	
}

function checkKick(abs_hole_num) {
    var found=false;
    var i=0;
    var j;
    while (!found && i<4) {
        if (i!=team_turn) {
            j=0;
            while (!found && j<4) {
                if (abs_hole_num==map(colours[i],marbles[colours[i]][j])) {
                    marbles[colours[i]][j]=0;
                    starters[colours[i]]+=1;
                    found=true;
                    var start_marbles=0;
                    for (var k in marbles[colours[i]]) {
                        if (marbles[colours[i]][k]==0) {
                            start_marbles++; 
                        }
                    }
                    abs_hole_num=76+shift[colours[i]].home-start_marbles;
                    x = board.xcoord[abs_hole_num];
                    y = board.ycoord[abs_hole_num];
                    var x_off = x + hi_lite_shift_x;
                    var y_off = y + hi_lite_shift_y;
                    $(marble_div[colours[i]][j]).css({
                        'left': x+'px',
                        'top': y+'px'
                    });
                    $('#kick-highlight').css({
                        'left': x_off+'px',
                        'top': y_off+'px'
                    });
                    //$('#kick-highlight').stop(true);
                    $('#kick-highlight').fadeIn();
                    setTimeout("$('#kick-highlight').fadeOut(500)",1000);
                    var message = colours[i]+' was kicked!!';
                    $('#message p').text(message);
                    //$('#message p').stop(true);
                    $('#message p').fadeIn('fast');
                } else {
                    j++;
                }
            }
        }
        i++;
    }
}

function checkAllHome() {
    var home=4;
    for (var i in marbles[colours[team_turn]]) {
        if (marbles[colours[team_turn]][i]<56||marbles[colours[team_turn]][i]>59) {
            home--;
        }
    }
    all_home[colours[team_turn]]=home;
    if (all_home[colours[turn]]==4 && all_home[colours[(turn+2)%4]]==4) {
        game_over=true;
        winner=colours[turn]+'/'+colours[(turn+2)%4];
    }
}

function engineMove() {
    var move_params=new Object();
    move_params.MAN_HOME=1500; //orig - 1000
    move_params.MAN_OUT=400;
    move_params.KICK_OPP=1500;
    move_params.RUN_HOME=500;
    move_params.CHASE=300;
    move_params.SHORTCUT=800;
    move_params.OPEN_HOUSE=400;
    move_params.PUSH_UP=500;
    move_params.GOTO_SHORTCUT=300;
    move_params.GET_OUT_HOUSE=1000;
    //NOT params
    move_params.NOT_KICK_PARTNER=2000;
    move_params.STAY_OUT_HOUSE=900;
    move_params.STAY_BEHIND=400;
    move_params.STAY_BEHIND_SHORTCUT=100;
    move_params.STAY_SHORTCUT=200;
    move_params.STAY_BEHIND_PARTNER=300;
    move_params.GO_AHEAD_PARTNER=300;
    //create array of all possible valid moves only
    var possible_moves=new Array();
    var start_move=false;
    var temp_obj = new Object();
    /*
     * temp_obj objects get stored in the possible moves array
     * the mtype property values can be:
     * 0 - start move
     * 1 - standard path move
     * 2 - enter shortcut
     * x - exit short cut to hole x
     */
    var j;
    for (var i in valid_move) {
        if (valid_move[i]) {
            switch (marbles[colours[team_turn]][i]) {
                case 0 :
                    if (!start_move) {
                        start_move=true;
                        temp_obj = {
                            marble:i,
                            mtype:0,
                            rating:move_params.MAN_OUT
                        };
                        possible_moves.push(temp_obj);
                    }
                    break;
                case 6 :
                case 20 :
                case 34 :
                case 48 :
                    possible_moves.push({
                        marble:i,
                        mtype:1,
                        rating:0
                    });
                    if (shortcutTest(i)) {
                        possible_moves.push({
                            marble:i,
                            mtype:2,
                            rating:0
                        });
                    }
                    break;
                case 60 :
                    var possible_exits = new Array();
                    possible_exits=checkExits();
                    for (j in possible_exits) {
                        possible_moves.push({
                            marble:i,
                            mtype:possible_exits[j],
                            rating:0
                        });
                    }
                    break;
                default :
                    temp_obj = {
                        marble:i,
                        mtype:1,
                        rating:0
                    };
                    possible_moves.push(temp_obj);
            }
        }
    }
    //
    //rate the moves!!
    //
    var max_rating=-9999;
    var max_marble;
    var max_type;
    var opp_left=colours[(team_turn+1)%4];
    var opp_right=colours[(team_turn+4-1)%4];
    var partner=colours[(team_turn+2)%4];
    var self=colours[team_turn];
    var orig;
    var dest;
    var dist;
    var kick_factor;
    var chase_factor=new Array(0,0);
    var no_stab_n_go;
    for (i in possible_moves) {
        orig=marbles[self][possible_moves[i].marble];
        dest=orig+roll_value;
        switch (possible_moves[i].mtype) {
            case 0 ://is man out
                var tempArray=marbles[opp_left].slice();
                tempArray.sort(descNumSort);
                if (Math.max(tempArray[0])>29) {
                    possible_moves[i].rating+=200;
                }
                if (Math.max(tempArray[0])>43) {
                    possible_moves[i].rating+=200;
                }
                if (roll_value==1) {
                    possible_moves[i].rating+=400;
                }
                if (isKick(opp_left,1,14)) {
                    possible_moves[i].rating+=move_params.KICK_OPP*0.8;
                } else if (isKick(opp_right,1,42)) {
                    possible_moves[i].rating+=move_params.KICK_OPP*0.7;
                } else if (isKick(partner,1,28)) {
                    possible_moves[i].rating-=move_params.NOT_KICK_PARTNER;
                }
                break;
            case 1 :
                no_stab_n_go=((dest==15||dest==43)&&roll_value!=6);
                if (dest>=56) {//is man home
                    if(orig>=56) {
                        possible_moves[i].rating=move_params.PUSH_UP
                        *((all_home[self]-1)/4+1);
                    } else {
                        possible_moves[i].rating=move_params.MAN_HOME;
                    }
                } else if (isKick(opp_left,dest,14) && !no_stab_n_go) {
                    kick_factor=(dest+42)%56;
                    kick_factor=(kick_factor==0)?56:kick_factor;
                    kick_factor=(kick_factor+55)/110;
                    possible_moves[i].rating=move_params.KICK_OPP*kick_factor;
                } else if (isKick(opp_right,dest,42) && !no_stab_n_go) {
                    kick_factor=(dest+14)%56;
                    kick_factor=(kick_factor==0)?56:kick_factor;
                    kick_factor=(kick_factor+55)/110;
                    possible_moves[i].rating=move_params.KICK_OPP*kick_factor;
                } else if (isKick(partner,dest,28)) {
                    kick_factor=(dest+28)%56;
                    kick_factor=(kick_factor==0)?56:kick_factor;
                    kick_factor=(kick_factor+55)/110;
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER*kick_factor;
                } else if (orig==15 && starters[opp_left]!=0) {
                    possible_moves[i].rating=move_params.GET_OUT_HOUSE*0.9;
                } else if (orig==29 && starters[partner]!=0) {
                    possible_moves[i].rating=move_params.GET_OUT_HOUSE*0.8;
                } else if (orig==43 && starters[opp_right]!=0) {
                    possible_moves[i].rating=move_params.GET_OUT_HOUSE;
                } else {
                    //stay out of house
                    if (dest==15 && starters[opp_left]!=0 && roll_value!=6) {
                        possible_moves[i].rating-=move_params.STAY_OUT_HOUSE*0.9;
                    } else if (dest==29 && starters[partner]!=0 && roll_value!=6) {
                        possible_moves[i].rating-=move_params.STAY_OUT_HOUSE*0.8;
                    } else if (dest==43 && starters[opp_right]!=0 && roll_value!=6) {
                        possible_moves[i].rating-=move_params.STAY_OUT_HOUSE;
                    }
                    //go to shortcut
                    if (dest==6 && !battleOntheCorner(opp_left)) {
                        possible_moves[i].rating+=move_params.GOTO_SHORTCUT;
                    }
                    //evaluate chase options
                    for (j in marbles[opp_left]) {
                        if (marbles[opp_left][j]!=0&&marbles[opp_left][j]<56) {
                            dist=(marbles[opp_left][j]+14)%56;
                            if (!(dist<=13&&dist>7&&orig>=7&&orig<=12)) {
                                dist=dist-dest;
                                dist=(dist<0)?999999:dist;
                                chase_factor.push(marbles[opp_left][j]/(dist+12)
                                    *(all_home[opp_left]+1)*move_params.CHASE);
                            }
                        }
                        if (marbles[opp_right][j]!=0&&marbles[opp_right][j]<56) {
                            dist=(marbles[opp_right][j]+42)%56;
                            if (!(dist<=41&&dist>35&&orig>=35&&orig<=40)) {
                                dist=dist-dest;
                                dist=(dist<0)?999999:dist;
                                chase_factor.push(marbles[opp_right][j]/(dist+12)
                                    *(all_home[opp_right]+1)*move_params.CHASE);
                            }
                        }
                    }
                    //chase_factor.sort(Array.DESCENDING|Array.NUMERIC);
                    chase_factor.sort(descNumSort);
                    possible_moves[i].rating+=chase_factor[0];
                    //RUN HOME
                    possible_moves[i].rating+=move_params.RUN_HOME*dest/55;
                    if (orig==1 && roll_value!=6 && starters[self]!=0) {
                        if(!leftGoingHome(opp_left) && !rightToClose(opp_right)) {
                            possible_moves[i].rating+=move_params.OPEN_HOUSE;
                        }
                    }
                }
                break;
            case 2 :
                if (isKick(opp_left,60,0)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(opp_right,60,0)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(partner,60,0)) {
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER;
                }
                possible_moves[i].rating+=move_params.SHORTCUT
                *(48-orig)/42;
                break;
            case 6 :
                //is kick opp
                if (isKick(opp_left,6,14)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(opp_right,6,42)) {
                    possible_moves[i].rating=move_params.KICK_OPP*0.2;
                } else if (isKick(partner,6,28)) {
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER;
                }
                break;
            case 20 :
                //is kick opp
                if (isKick(opp_left,20,14)) {
                    possible_moves[i].rating=move_params.KICK_OPP*0.2;
                } else if (isKick(opp_right,20,42)) {
                    possible_moves[i].rating=move_params.KICK_OPP*0.7;
                } else if (isKick(partner,20,28)) {
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER;
                }
                possible_moves[i].rating+=50;
                break;
            case 34 :
                //is kick opp
                if (isKick(opp_left,34,14)) {
                    possible_moves[i].rating=move_params.KICK_OPP*0.4;
                } else if (isKick(opp_right,34,42)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(partner,34,28)) {
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER;
                }
                possible_moves[i].rating+=150;
                break;
            case 48 :
                //is kick opp
                if (isKick(opp_left,48,14)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(opp_right,48,42)) {
                    possible_moves[i].rating=move_params.KICK_OPP;
                } else if (isKick(partner,48,28)) {
                    possible_moves[i].rating=-move_params.NOT_KICK_PARTNER;
                }
                possible_moves[i].rating+=1000;
                break;
        }
        if (possible_moves[i].rating>max_rating) {
            max_rating=possible_moves[i].rating;
            max_marble=possible_moves[i].marble;
            max_type=possible_moves[i].mtype;
        }
        
    }
    
    var abs_pos;
    switch (max_type) {
        case 0 :
            max_marble=findFirstStart();
            marbles[self][max_marble]++;
            starters[self]-=1;
            abs_pos=map(self,marbles[self][max_marble]);
            break;
        case 1 :
            marbles[self][max_marble]+=roll_value;
            abs_pos=map(self,marbles[self][max_marble]);
            break;
        case 2 :
            marbles[self][max_marble]=60;
            abs_pos=57;
            break;
        default :
            marbles[self][max_marble]=max_type;
            abs_pos=map(self,marbles[self][max_marble]);
    }
    if (marbles[self][max_marble]<56||marbles[self][max_marble]==60) {
        //alert('checkkick'+abs_pos);
        checkKick(abs_pos);
    } else {
        ///alert('checkAllHome');
        checkAllHome();
    }
    //alert('foobar');
    var x = board.xcoord[abs_pos];
    var y = board.ycoord[abs_pos];
    var x_off = x + hi_lite_shift_x;
    var y_off = y + hi_lite_shift_y;
    var orig_abs_x = $(marble_div[self][max_marble]).position().left;
    var orig_abs_y = $(marble_div[self][max_marble]).position().top;
    $(marble_div[self][max_marble]).css({
        'left': x+'px',
        'top': y+'px'
    });
    $('#move-highlight').css({
        'left': x_off+'px',
        'top': y_off+'px'
    });
    //$('#move-highlight').stop(true);
    $('#move-highlight').fadeIn(50);
    clearTimeout(HLTimerId);
    HLTimerId = setTimeout("$('#move-highlight').fadeOut(500)",1000);
    showMoveArrow(orig_abs_x,orig_abs_y,x,y);
	
    if (!game_pause) {
        setTimeout('rollReady()', 1000);
    }
}

function checkExits() {
    var valid_exits=new Array();
    for (var i in exits) {
        var empty=true;
        for (var j in marbles[colours[team_turn]]) {
            if (j!=active_number) {
                if (marbles[colours[team_turn]][j]==exits[i]) {
                    empty=false;
                }
            }
        }
        if (empty) {
            valid_exits.push(exits[i]);
        }
    }
    return valid_exits;
}

function isKick(colour ,dest, shift) {
    var kick=false;
    var j=0;
    while (!kick && j<=3) {
        if (marbles[colour][j]<56 && marbles[colour][j]>0 && (marbles[colour][j]+shift)%56==dest) {
            //alert('kick found');
            kick=true;
        }
        if (dest==60&&marbles[colour][j]==60) {
            kick=true;
        }
        j++;
    }
    return kick;
}

function leftGoingHome(colour) {
    var i=0;
    var found=false;
    while(!found && i<=3) {
        if (marbles[colour][i]>29 && marbles[colour][i]<43) {
            found=true;
        //alert('left going home');
        } else {
            i++;
        }
    }
    return found;
}

function rightToClose(colour) {
    var i=0;
    var found=false;
    while(!found && i<=3) {
        if (marbles[colour][i]>6 && marbles[colour][i]<15) {
            found=true;
        //alert('right to close');
        } else {
            i++;
        }
    }
    return found;
}

function battleOntheCorner(colour) {
    var i=0;
    var backup=0;
    var found=false;
    while (!found&&i<4) {
        found=(marbles[colour][i]==60)?true:false;
        i++;
    }
    for (i in marbles[colours[team_turn]]) {
        if (marbles[colours[team_turn]][i]>0&&marbles[colours[team_turn]][i]<6) {
            backup++;
        }
    }
    if (found&&backup<2) {
        //alert('balls of steel!');
        return true;
    } else {
        return false;
    }
}

function descNumSort(x, y) {
    if (x < y) {
        return 1;
    } else {
        return -1;
    }
}

function endGame(yes) {	
    //alert(yes);
    $('button#yes').hide();
    $('button#no').hide();
    if(!yes) {
        $('button#yes').off();
        $('button#no').off();
        $('button#yes').on('mousedown', function (){
            shtcut(true)
        });
        $('button#no').on('mousedown', function (){
            shtcut(false)
        });
        game_over = false;
        initPosition();
        rollReady();
    } else {
        $('#message p').fadeOut('slow');	
    }
}

function initPosition() {
    for(var c in colours) {
        var colour = colours[c];
        marbles[colour] = new Array(0,0,0,0);
        for(var i in marbles[colour]) {
            var x = board.xcoord[72+shift[colour].home+parseInt(i)];
            var y = board.ycoord[72+shift[colour].home+parseInt(i)];
            //var x = board.xcoord[map(colour, marbles[colour][i])];
            //var y = board.ycoord[map(colour, marbles[colour][i])];
            $(marble_div[colour][i]).css({
                'left': x+'px',
                'top': y+'px'
            });
            $(marble_active[colour][i]).hide();
        }
        all_home[colour]=0;
        starters[colour]=4;
    }	
}

function initPositionCustom() {
    var x, y;
    for(var c in colours) {
        var colour = colours[c];
        starters[colour]=0;
        all_home[colour]=0;
        for(var i in marbles[colour]) {
            marbles[colour][i] = Number(marbles[colour][i]);
            switch (marbles[colour][i]) {
                case 0:
                    x = board.xcoord[72+shift[colour].home+3-starters[colour]];
                    y = board.ycoord[72+shift[colour].home+3-starters[colour]];
                    starters[colour]++;
                    break;
                case 56:
                case 57:
                case 58:
                case 59:
                    all_home[colour]++;
                //no break
                default:
                    x = board.xcoord[map(colour, marbles[colour][i])];
                    y = board.ycoord[map(colour, marbles[colour][i])];
            }
            $(marble_div[colour][i]).css({
                'left': x+'px',
                'top': y+'px'
            });
            $(marble_active[colour][i]).hide();
        }
    }	
}

function showMoveArrow(o_x, o_y, x, y) {
    clearTimeout(MATimerId);
    var theta;
    var m, vx, vy;
    o_x = o_x + arrow_shift_x;
    o_y = o_y + arrow_shift_y;
    x = x + arrow_shift_x;
    y = y + arrow_shift_y;
    var dx = x - o_x;
    if(dx < 0) {
        vx = -1;
    } else {
        vx = 1;
    }
    var dy = y - o_y;
    if(dy < 0) {
        vy = -1;
    } else {
        vy = 1;
    }	
    var d = Math.sqrt(dx*dx + dy*dy);
    if (dx != 0) {
        m = Math.abs(dy/dx);
    } else {
        m = 9999;
    }
    dd = d - 30;
    ddx = Math.sqrt(dd*dd / (m*m + 1));
    ddy = Math.abs(m*ddx);
    clip_x = (Math.abs(dx) - ddx)/3;
    clip_y = (Math.abs(dy) - ddy)/3;
	
    new_x = o_x + vx*ddx;
    new_y = o_y + vy*ddy;
    a_x = o_x + vx*(ddx + clip_x);
    a_y = o_y + vy*(ddy + clip_y);
	
    if (vx == 1 && vy == 1) {
        theta = Math.atan(m) + (1/2)*Math.PI;
    } else if (vx == 1 && vy == -1) {
        theta = (1/2)*Math.PI - Math.atan(m);
    } else if (vx == -1 && vy == -1) {
        theta = - (1/2)*Math.PI + Math.atan(m);
    } else if (vx == -1 && vy == 1) {
        theta = - (1/2)*Math.PI - Math.atan(m);
    }
    //alert(theta);
    var canvas = document.getElementById('move-arrow');  
    var ctx = canvas.getContext('2d'); 
    ctx.strokeStyle='#ff1bff';
    ctx.fillStyle='#ff1bff';
    ctx.lineWidth=5.0;
    ctx.clearRect(0, 0, 800, 600);
	
    ctx.save();
    ctx.beginPath();  
    ctx.moveTo(o_x,o_y);  
    ctx.lineTo(new_x,new_y);
    ctx.closePath();
    ctx.stroke();
    ctx.restore();
	
    ctx.save();
	
    ctx.translate(a_x, a_y);
    ctx.rotate(theta);
    ctx.beginPath();  
    ctx.moveTo(0,0);  
    ctx.lineTo(10, 17.32);
    ctx.lineTo(-10, 17.32);
    ctx.closePath();
    ctx.fill();
    ctx.restore();

    //$('#move-arrow').stop(true);
    $('#move-arrow').show();
    MATimerId = setTimeout("$('#move-arrow').hide()",1000);
}

function restart(confirm) {     
    var to_hide;
    var face;
    $('#message p').text('');
    $('button#rs-yes').hide();
    $('button#rs-no').hide();
    if (confirm) {
        for (var dice = 1; dice <= 4; dice++) {
            for (face = 1; face <= 6; face++) {
                to_hide = '#dice-' + dice + ' .final-' + face;
                $(to_hide).hide();
            }
        }
        setCookie('turn', '3', 7);
        for(var i in colours) {        
            setCookie(colours[i], '0-0-0-0-', 7);
        }
        newGame();
    } else {
        game_pause = false;
        if (!comp_player[colours[turn]]) {
            turn=(turn-1)%4;
        }
        rollReady();
    }
}