<?php
if (!is_user_logged_in()) {
    wp_redirect(site_url());
    exit;
}

$q_str = $_SERVER['QUERY_STRING'];


global $wpdb;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_meta = get_user_meta($user_id);

//is the player already in an active match?

$query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and status = 'in_progress'";

$match = $wpdb->get_row($query, ARRAY_A);

if ($match) {

    //they shouldn't be on this page
    wp_redirect(site_url('game'));
}

//if another player created the game first you will already be in it

$query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and status = 'rematch'";

$rematch = $wpdb->get_row($query, ARRAY_A);

if ($q_str == 'yes') {

    if ($rematch == null) {

        //get all the players from the last match
        $query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and (status = 'complete' or status = 'forfeit') order by created desc";
        $rematch = $wpdb->get_row($query, ARRAY_A);

        //If this user waited too long any of the other players could be in a new or in_progress match

        $query = "SELECT * FROM `match` where (player_1 = {$rematch['player_1']} or player_2 = {$rematch['player_2']}  or player_3 = {$rematch['player_3']}  or player_4 = {$rematch['player_4']}) and (status = 'new' or status = 'in_progress')";

        $test_new = $wpdb->get_row($query, ARRAY_A);

        if ($test_new == null) {

            //create new match
            $data = array(
                'player_1' => $rematch['player_1'],
                'player_2' => $rematch['player_2'],
                'player_3' => $rematch['player_3'],
                'player_4' => $rematch['player_4'],
                'status' => 'rematch',
                'created' => time()
            );
            $wpdb->insert('match', $data);
            $match_id = $wpdb->insert_id;
        } else {

            wp_redirect(site_url() . '?expired');
        }
    } else {

        $match_id = $rematch['id'];
    }

    //update the user's flag

    update_user_meta($user_id, 'rematch_flag', 'yes');
} else {

    if ($rematch) {

        //zero out the users player

        for ($i = 1; $i <= 4; $i++) {

            if ($rematch['player_' . $i] == $user_id) {

                $rematch['player_' . $i] = 0;
            }
        }

        $wpdb->update(
                'match', array(
            'player_1' => $rematch['player_1'],
            'player_2' => $rematch['player_2'],
            'player_3' => $rematch['player_3'],
            'player_4' => $rematch['player_4']
                ), array('ID' => $rematch['id']), '%s', array('%d')
        );
    } else {
        //get all the players from the last match
        $query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and (status = 'complete' or status = 'forfeit') order by created desc";
        $rematch = $wpdb->get_row($query, ARRAY_A);

        //If this user waited too long any of the other players could be in a new or in_progress match

        $query = $query = "SELECT * FROM `match` where (player_1 = {$rematch['player_1']} or player_2 = {$rematch['player_2']}  or player_3 = {$rematch['player_3']}  or player_4 = {$rematch['player_4']}) and (status = 'new' or status = 'in_progress')";

        $test_new = $wpdb->get_row($query, ARRAY_A);

        if ($test_new == null) {

            //zero out the users player

            for ($i = 1; $i <= 4; $i++) {

                if ($rematch['player_' . $i] == $user_id) {

                    $rematch['player_' . $i] = 0;
                }
            }

            //create new match but this user will be zeroed out

            $data = array(
                'player_1' => $rematch['player_1'],
                'player_2' => $rematch['player_2'],
                'player_3' => $rematch['player_3'],
                'player_4' => $rematch['player_4'],
                'status' => 'rematch',
                'created' => time()
            );
            $wpdb->insert('match', $data);
            $match_id = $wpdb->insert_id;
        }
    }

    //update the user's flag

    update_user_meta($user_id, 'rematch_flag', 'no');

    if ($q_str == 'no') {

        wp_redirect(site_url());
    } else {

        wp_redirect(site_url() . '?logout');
    }
}

$wpdb->flush();

get_header();
?>
<body>

    <div id="wrapper">

        <div id="leftSidebar">
            <h5 style="position: absolute;text-align: center;width: 180px;top: 82px;font-family: Arial;font-size: 23px;color: #D8E0E4;">Multi-Player</h5>
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg" width="180" height="80" alt="Wahoo" /><br />
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_dice.jpg" width="180" height="149" alt="Six" />
            <div id="left-menu">
                <p id="playlink" class="active" onclick="showpage(this)">Play Wahoo</p>
                <p id="guidelink" class="inactive" onclick="showpage(this)">Quick Start Guide</p>
                <p id="aboutlink" class="inactive" onclick="showpage(this)">About</p>
            </div>

            <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

        </div>
        <div id="game">

            <div id="header">
                <div id="menu">

                    <ul>
                        <li>Welcome <?php echo $user_meta['first_name'][0] ?></li>
                        <li>&bigcirc;</li>
                        <li><a href="profile">My Profile</a></li>
                        <li><a href="<?php echo get_site_url() . '?logout' ?>">Log Out</a></li>
                    </ul>

                </div>
            </div>

            <div id="join-game">

                <div id="player-list">Loading...

                </div>
                <div class="clear"><button onclick="window.location = 'exit-game?<?php echo $match_id ?>'">Leave Game</button></div>

            </div>
        </div>

        <div id="guide">
            <h2>Quick Start Wahoo Guide</h2>
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo-diagram-01.jpg" width="793" height="593" /><br />
            <p>(terms in bold are indicated in the game diagram)</p>
            <p>Just click &#8220;Roll&#8221; (in most browsers you can just hit the spacebar). Your marbles are the red marbles. The green marbles are your partner's, so try and protect them. The &#8220;Roll&#8221; button appears automatically when it is your turn and you have to roll your dice. The program will tell you when you have no valid moves and if you only have one possible move, it will be made automatically.</p>
            <p>If you have more than one valid move you must select which marble you want to move. If the marble can make a valid move it will have a yellow halo around it, otherwise the marble can't be moved. Once you click the marble it will be automatically moved. The program will prompt you if a move involves entering or exiting the <strong>shortcut</strong> hole.</p>
            <p>You must roll a six or a one to bring a marble from the <strong>start stack</strong> to the <strong>starting hole</strong> and you must roll a one to enter or exit a <strong>shortcut</strong>.</p>
            <p>Using the &#8220;Options&#8221; menu you can quit your current game and start a brand new game or you can adjust the game speed.</p>
            <h4>Detailed Wahoo Rules</h4>
            <p>1. The object of Wahoo is to get all 4 of your marbles into your <strong>home</strong> area. This version of the game is played with teams. Your team partner is on the opposite side of the board. For your team to win both you and your partner must get all 4 of your marbles in the <strong>home</strong> area before the other team. When one team member has all his marbles in the home area he continues to roll on his turn but his partner must use the amount shown on the dice to move one of his marbles if he has a valid move.</p>
            <p>2. To get one of your marbles from the <strong>start stack</strong> into the <strong>starting hole</strong> of your <strong>main path</strong> you must roll a six or a one and your <strong>starting hole</strong> cannot be occupied by one of your own marbles. You can have as many marbles on the <strong>main path</strong> as you like. Once you are on the <strong>main path</strong> you can move any one of your marbles the number of holes shown on the dice if the move is valid. If you only have one valid move you must make that move. If you have no valid moves you can't move any marbles. Normally you roll once per turn with the next turn going to the player on your left. If you roll a six you can move then roll again. Even if you can't use the six for a valid move you still roll again.</p>
            <p>3. You cannot jump or land in a hole occupied by one of your own marbles. This rule also applies to your marbles in the <strong>home</strong> area. If the roll of your dice would take you past the last hole of your <strong>home</strong> area, then the move is not valid. If you land in a hole occupied by an opponent or your partner you must remove their marble and place it in their <strong>start stack</strong>. this action is referred to as "kicking". Marbles in the <strong>home</strong> area cannot be kicked.</p>
            <p>4. To enter the <strong>shortcut</strong> hole your marble must enter from a <strong>shortcut entrance</strong> and you must roll a one. To exit the <strong>shortcut</strong> hole you must also roll a one. You can use any <strong>shortcut exit</strong> as long as it is not occupied by one of your own marbles.</p>
            <p>Just remember, Wahoo is a game that can drive you crazy very quickly. So good luck!! and roll those sixes!</p>
            <p><a href="http://wahoogame.blogspot.ca/" target="_blank">Visit the Wahoo Blog</a> for tips and tricks</p>
        </div>

        <div id="about">
            <h2>About</h2>
            <p>Version 1.0</p>
            <p>
                Welcome to version 1.0 of Multi-Player Wahoo. Feel free to <a href="mailto:info@bagtoons.com">report any bugs or problems</a>.
            </p>

        </div>
    </div>
</body>

<script>

    var open;

    $(function() {
        update();
    });

    function update() {
        var status = $("#current-status").val();
        
        console.log(status);

        if (status == 'new') {

            window.location = 'waiting';

        } else {

            open = $("input:radio[name=open]:checked").val();

            $('#player-list').html('<p>Updating...</p>');
            $('#player-list').load(MyAjax.ajaxurl, {
                action: 'update_rematch',
                MatchID: <?php echo $match_id ?>,
                open: open
            }, checkStart);

            var updateTimer = setTimeout(update, 5000);
        }
    }

    function checkStart() {

        var startGame = $('#start-game').attr('rel');

        if (startGame == 'yes') {

            window.location = 'game';

        }

    }

</script>

<script type="text/javascript">
    <!--
        
    function showpage(e) {
        var toDeactivate;
        var menu = {playlink: '#game', guidelink: '#guide', aboutlink: '#about'};
        for (var pid in menu) {
            toDeactivate = '#' + pid;
            $(toDeactivate).removeClass('active');
        }
        $('#' + e.id).addClass('active');
        e.blur();
        $('#game').fadeOut('fast');
        $('#guide').fadeOut('fast');
        $('#about').fadeOut('fast');
        $(menu[e.id]).fadeIn('fast');
    }
  -->
</script>

</html>
