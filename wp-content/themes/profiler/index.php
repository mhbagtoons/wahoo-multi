<?php
$pre_pop = array(
    'log' => '',
    'email' => '',
    'pwd' => '',
    're_pwd' => '',
    'first_name' => '',
    'last_name' => ''
);

$success = true;

if (is_user_logged_in()) {


    if (isset($_SERVER['QUERY_STRING'])) {
        if ($_SERVER['QUERY_STRING'] == 'logout') {
            wp_logout();
            wp_redirect(site_url());
            exit;
        }

        if ($_SERVER['QUERY_STRING'] == 'removed') {
            $join_message = 'Sorry, this game no longer exists. Please join a different game or create a new game.';
        }

        if ($_SERVER['QUERY_STRING'] == 'full') {
            $join_message = 'Sorry, this game is already full. Please join a different game or create a new game.';
        }

        if ($_SERVER['QUERY_STRING'] == 'forfeit') {
            $join_message = 'You forfeited your current game. You can join a different game or create a new game.';
        }
        
        if ($_SERVER['QUERY_STRING'] == 'expired') {
            $join_message = 'The rematch has expired. You can join a different game or create a new game.';
        }
    }

    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_meta = get_user_meta($user_id);
    $wins = (!isset($user_meta['wins'])) ? 0 : $user_meta['wins'][0];
    $losses = (!isset($user_meta['losses'])) ? 0 : $user_meta['losses'][0];


//get the new games

    $query = "SELECT * 
FROM  `match` 
WHERE STATUS =  'new'";

    $new_games = $wpdb->get_results($query, ARRAY_A);



//is the player in an active match?

    $query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and status != 'complete' and status != 'forfeit'";

    $match = $wpdb->get_row($query);

    if ($match != null) {

//send user to appropriate page depending on the match status

        if ($match->status == 'new') {
            wp_redirect(site_url('waiting'));
        } else {
            wp_redirect(site_url('game'));
        }
    }

    $wpdb->flush();
} else {

    $message_class = 'message-red';

    if (isset($_POST['process_login'])) {


        $creds = array();
        $creds['user_login'] = $_POST['log'];
        $creds['user_password'] = $_POST['pwd'];
        $creds['remember'] = false;



//get registration status

        $pending_user = get_user_by('login', $creds['user_login']);
        $pending_meta = get_user_meta($pending_user->data->ID);

        if ($pending_meta['ver_key'][0] && $pending_meta['reg_pending'][0] == 'false') {

            $user = wp_signon($creds, false);
            if (is_wp_error($user)) {
                $log_message = $user->get_error_message();
                $log_message = preg_replace('/\s<a.+a>\?/', '', $log_message);
            } else {
                wp_redirect(site_url());
                exit;
            }
        } else {

            if ($pending_meta['ver_key'][0]) {
                $log_message = 'You must verify you registration before logging in. Check your email for the verification link message';
            } else {
                $log_message = 'The username you entered does not exist.';
            }
        }
    } elseif (isset($_POST['register'])) {

        $pre_pop['log'] = $_POST['log'];
        $pre_pop['email'] = $_POST['email'];
        $pre_pop['first_name'] = $_POST['first_name'];
        $pre_pop['last_name'] = $_POST['last_name'];
        $pre_pop['pwd'] = $_POST['pwd'];
        $pre_pop['re_pwd'] = $_POST['re_pwd'];

        //check username exists?

        if (username_exists($_POST['log'])) {

            $reg_message = 'Username already exists';
            $success = false;
        } elseif (email_exists($_POST['email'])) {

            $reg_message = 'That email address is already in use';
            $success = false;
        } elseif ($_POST['pwd'] != $_POST['re_pwd']) {

            //check pwd match
            $reg_message = 'Passwords do not match';
            $success = false;
            $pre_pop['pwd'] = $pre_pop['re_pwd'] = '';
        } elseif ($_POST['log'] == '') {

            $reg_message = 'Username cannot be empty';
            $success = false;
        } elseif (!preg_match('/^[\w\.-]+@[\w\.-]+\.\w+$/i', $_POST['email'])) {

            $reg_message = 'email address format is incorrect';
            $success = false;
        } elseif ($_POST['pwd'] == '') {

            $reg_message = 'Password cannot be empty';
            $success = false;
        } elseif ($_POST['first_name'] == '') {

            $reg_message = 'Please enter a first name';
            $success = false;
        }


        if ($success) {

            //create user

            $username = $_POST['log'];
            $password = $_POST['pwd'];
            $email = $_POST['email'];
            $user_id = wp_create_user($username, $password, $email);

            $ver_key = wp_generate_password(24, false);
            add_user_meta($user_id, 'reg_pending', 'true', true);
            add_user_meta($user_id, 'ver_key', $ver_key, true);
            add_user_meta($user_id, 'profile_pic', get_bloginfo('template_url') . '/images/placeholder-2-100.jpg', true);
            update_user_meta($user_id, 'first_name', $_POST['first_name']);
            update_user_meta($user_id, 'last_name', $_POST['last_name']);

            $subject = 'Wahoo Registration Verification Link';
            $e_mess = '<html>'
                    . '<head><title>Welcome to Wahoo Multi-Player</title></head>'
                    . '<body>'
                    . '<p>Welcome ' . $_POST['first_name'] . ',<br /><br /></p>'
                    . "<p>Your username: $username</p>"
                    . '<p>Your password: (The password you created during the registration process)</p>'
                    . '<p>To complete the registration process <a href="' . site_url() . '/verify-registration/?ID=' . $user_id . '&key=' . $ver_key . '">click this verification link</a>. You will be taken to the Wahoo website and your user account will be verified.</p>'
                    . '<p>If you have problems using the provided link please paste the following URL into your browser\'s address bar:</p>'
                    . '<p>' . site_url() . '/verify-registration/?ID=' . $user_id . '&key=' . $ver_key . '</p>'
                    . '<p>Thank you for joining Wahoo Multi-Player</p>'
                    . '</body></html>';
            $headers[] = 'From: Multi-Player Wahoo <info@bagtoons.com>';
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';

            wp_mail($email, $subject, $e_mess, $headers);
        }
    } elseif (isset($_GET['verify'])) {
        if ($_GET['verify'] == 'error') {

            $log_message = 'There was an error verifying your registration. Please visit the <a href="' . site_url() . '/help">Help Page</a> and follow the instructions there.';
        } else {

            $message_class = 'message-green';
            $log_message = 'You have successfully verified your registration. Please log in to continue.';
        }
    }
}

get_header()
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

            <ul id="pages">
                <li><a href="<?php echo site_url('help') ?>">Help and Support</a></li>
            </ul>

            <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

        </div>
        <div id="game">

<?php if ($success && isset($_POST['register'])) { ?>
                <div id="wahoo-register">
                    <p>Your registration has been processed.</p>
                    <p>An email has been sent to the email address you provided.</p>
                    <p>Click the link in the email and follow the instructions to complete the registration process</p>
                </div>

<?php } else { ?>

    <?php if (is_user_logged_in()) { ?>

                    <div id="header">
                        <div id="menu">

                            <ul>
                                <li>Welcome <?php echo $user_meta['first_name'][0] ?> (<?php echo $wins ?>-<?php echo $losses ?>)</li>
                                <li>&bigcirc;</li>
                                <li><a href="profile">My Profile</a></li>
                                <li><a href="<?php echo get_site_url() . '?logout' ?>">Log Out</a></li>
                            </ul>

                        </div>
                    </div>

                    <div id="join-game">

                        <h1>Join Game</h1>

        <?php if ($join_message) { ?>

                            <div class="message-red"><?php echo $join_message ?></div>

                        <?php } ?>

                        <div id="game-list">

                            <p>Updating...</p>

                        </div>

                        <p><button onclick="window.location = 'waiting?new'">Create New Game</button></p>

                    </div>

    <?php } else { ?>

                    <div id="wahoo-login">

                        <h1>Log In</h1>

        <?php if ($log_message) { ?>

                            <div class="<?php echo $message_class ?>"><?php echo $log_message ?></div>

                        <?php } ?>

                        <form id='login-form' method="post">
                            <input type="hidden" name="process_login" value="1" />
                            <table>
                                <tr>
                                    <td class="right">Username:</td>
                                    <td><input type="text" name="log" /></td>
                                </tr>
                                <tr>
                                    <td class="right">Password:</td>
                                    <td><input type="password" name="pwd" /></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td><input type="submit" value="Log In" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo site_url('help'); ?>"><small>Forgot Password?</small></a></td>
                                </tr>
                            </table>

                        </form> 

                    </div>

                    <div id="wahoo-register">

                        <h1>Register</h1>

                        <p>Don't have a player profile? Register here. A verification email will be sent to the email address you provide. Click the link in the email to verify and activate your player profile.</p>



        <?php if ($reg_message) { ?>

                            <div class="<?php echo $message_class ?>"><?php echo $reg_message ?></div>

                        <?php } ?>

                        <form id="registration-form" action="" method="post">
                            <input type="hidden" name="register" value="1" />
                            <table>
                                <tr>
                                    <td class="right"><span class="red">*</span> - Required Field</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="right"><span class="red">*</span>Username:</td>
                                    <td><input type="text" name="log" value="<?php echo $pre_pop['log'] ?>" /> <small>(This will be your login name)</small></td>
                                </tr>
                                <tr>
                                    <td class="right"><span class="red">*</span>First Name:</td>
                                    <td><input type="text" name="first_name" value="<?php echo $pre_pop['re_pwd'] ?>" /> <small>(The name other users will see)</small></td>
                                </tr>
                                <tr>
                                    <td class="right">Last Name (optional):</td>
                                    <td><input type="text" name="last_name" value="<?php echo $pre_pop['re_pwd'] ?>" /></td>
                                </tr>
                                <tr>
                                    <td class="right"><span class="red">*</span>email:</td>
                                    <td><input type="text" name="email" size="40" value="<?php echo $pre_pop['email'] ?>" /></td>
                                </tr>
                                <tr>
                                    <td class="right"><span class="red">*</span>Password:</td>
                                    <td><input type="password" name="pwd" value="<?php echo $pre_pop['pwd'] ?>" /></td>
                                </tr>
                                <tr>
                                    <td class="right"><span class="red">*</span>Re-type Password:</td>
                                    <td><input type="password" name="re_pwd" value="<?php echo $pre_pop['re_pwd'] ?>" /></td>
                                </tr>

                                <tr>
                                    <td></td><td><input type="submit" value="Register" /></td>
                                </tr>
                            </table>

                        </form> 



                    </div>

    <?php } ?>


<?php } ?>

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
    $(function() {
        update();
    });

    function update() {
        $('#game-list').html('<p>Updating...</p>');

        $('#game-list').load(MyAjax.ajaxurl, {
            action: 'update_game_list'
        });

        var updateTimer = setTimeout(update, 20000);
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
