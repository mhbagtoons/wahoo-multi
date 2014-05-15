<?php
show_admin_bar(false);

add_action('login_enqueue_scripts', 'my_login_logo');

function my_login_logo() {
    ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url('<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg');
            background-size: 180px 80px;
            padding-bottom: 30px;
        }

        body.login {
            background-color: #000;
            text-shadow:none;
        }
    </style>
    <?php

}

add_filter('login_headerurl', 'my_login_logo_url');

function my_login_logo_url() {
    return get_bloginfo('url');

}

add_filter('login_headertitle', 'my_login_logo_url_title');

function my_login_logo_url_title() {
    return null;

}

add_filter('template_include', 'page_template', 99);

function page_template($template) {
    global $wp_query;

    header("HTTP/1.1 200 OK");
    $pagename = $wp_query->query_vars['name'];

    if ($pagename == 'register') {
        $new_template = locate_template(array('page-register.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'lgn') {
        $new_template = locate_template(array('page-login.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'profile') {
        $new_template = locate_template(array('page-profile.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'verify-registration') {
        $new_template = locate_template(array('page-verify-registration.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'waiting') {
        $new_template = locate_template(array('page-waiting.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'exit-game') {
        $new_template = locate_template(array('page-exit-game.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'game') {
        $new_template = locate_template(array('page-game.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'leave-game') {
        $new_template = locate_template(array('page-leave-game.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }

    if ($pagename == 'rematch') {
        $new_template = locate_template(array('page-rematch.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }


    return $template;

}

function match_clean($id) {

    global $wpdb;

    $query = "SELECT * 
              FROM  `match` 
              WHERE id = $id";

    $match = $wpdb->get_row($query);

    $temp = array();

    $empty_found = false;
    $is_clean = true;

    for ($i = 1; $i <= 4; $i++) {

        $player = 'player_' . $i;

        if ($match->$player != 0) {

            if ($empty_found) {

                $is_clean = false;
            }

            $temp[] = $match->$player;
        } else {

            $empty_found = true;
        }
    }

    if (!$is_clean) {

        for ($i = 0; $i <= 3; $i++) {

            if (!isset($temp[$i])) {

                $temp[$i] = 0;
            }
        }

        $wpdb->update(
                'match', array(
            'player_1' => $temp[0],
            'player_2' => $temp[1],
            'player_3' => $temp[2],
            'player_4' => $temp[3]
                ), array('ID' => $id), '%s', array('%d')
        );
    }

}

function profiler_scripts() {
    wp_enqueue_script('scripts', get_template_directory_uri() . '/js/scripts.js');
    //wp_enqueue_script('wahoo-jquery', get_template_directory_uri() . '/js/jquery-1.7.2.min.js');
    wp_enqueue_script('save-game', get_template_directory_uri() . '/js/save-game.js');
    //wp_enqueue_script('wahoo-engine', get_template_directory_uri() . '/js/wahoo-engine.js');
    wp_localize_script('scripts', 'MyAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    wp_enqueue_style('wahoo', get_template_directory_uri() . '/style-js.css');
//wp_enqueue_style( 'style-mob', get_template_directory_uri() . '/style-mob.css');

}

add_action('wp_enqueue_scripts', 'profiler_scripts');

add_action('wp_ajax_update_game', 'update_game_callback');
add_action('wp_ajax_nopriv_update_game', 'update_game_callback');

function update_game_callback() {

    ob_clean();
    global $wpdb;

    $id = $_POST['MatchID'];

    $query = "SELECT * 
              FROM  `match` 
              WHERE id = $id";

    $match = $wpdb->get_row($query, ARRAY_A);

    //find the match owner
    $match_owner = 0;
    $player_count = 0;
    $i = 1;

    while ($match_owner == 0 && $i <= 4) {
        $player = 'player_' . $i;

        if ($match[$player] != 0) {


            $match_owner = $match[$player];
        }
        $i++;
    }

    for ($i = 1; $i <= 4; $i++) {

        $player = 'player_' . $i;

        if ($match[$player] != 0) {

            $player_count++;

            if ($match_owner == 0) {

                $match_owner = $match[$player];
            }
        }
    }



    $owner = get_user_by('id', $match_owner);
    $owner_name = get_user_meta($owner->ID, 'first_name', true);
    ?>
    <h1><?php echo $owner_name ?>'s Game</h1>

    <table>
        <?php
        for ($i = 1; $i <= 4; $i++) {
            if ($match['player_' . $i] != 0) {
                $player = get_user_by('id', $match['player_' . $i]);
                $user_meta = get_user_meta($player->ID);
                $wins = (!isset($user_meta['wins'])) ? 0 : $user_meta['wins'][0];
                $losses = (!isset($user_meta['losses'])) ? 0 : $user_meta['losses'][0];
                $player_name = $user_meta['first_name'][0] . ' (' . $wins . '-' . $losses . ')';
            } else {
                $player_name = 'empty spot';
            }
            ?>

            <tr>
                <td class="player-<?php echo $i ?>"><?php echo $i ?></td>
                <td><?php echo $player_name ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <div id="wait-status">

        <?php
        if ($player_count == 4) {

            $marbles = json_encode(array(0, 0, 0, 0));

            $wpdb->update(
                    'match', array(
                'marbles_1' => $marbles,
                'marbles_2' => $marbles,
                'marbles_3' => $marbles,
                'marbles_4' => $marbles,
                'status' => 'in_progress',
                'modified' => time()
                    ), array('ID' => $id), '%s', array('%d')
            );
            ?>

            <p id="start-game" rel="yes">4 players have joined the game! Starting game...</p>

            <button onclick="window.location = 'game?<?php echo $id ?>'">Start Game</button>

        <?php } else { ?>

            <p>Waiting for more players...</p>

            <?php
            //check to see if there are other matches open lookig for players

            $query = "SELECT * 
                        FROM  `match` 
                        WHERE STATUS =  'new'";

            $new_games = $wpdb->get_results($query, ARRAY_A);

            $new_summary = array(0, 0, 0, 0);

            foreach ($new_games as $value) {

                if ($value['id'] != $id) {

                    $empty_count = 0;

                    for ($i = 1; $i <= 4; $i++) {

                        if ($value['player_' . $i] == 0) {

                            $empty_count++;
                        }
                    }

                    $new_summary[$empty_count] ++;
                }
            }

            for ($i = 1; $i <= 3; $i++) {

                if ($new_summary[$i] != 0) {

                    $text_a = ($new_summary[$i] == 1) ? 'is 1 other new game' : 'are ' . $new_summary[$i] . ' other new games';
                    $text_b = ($i == 1) ? ' player' : ' players';
                    ?>

                    <p>There <?php echo $text_a ?> looking for <?php echo $i . $text_b ?></p>

                <?php
            }
        }
        ?>

            <p>If you want, you can leave this game to join a different one.</p>

    <?php } ?>

    </div>

    <?php
    $wpdb->flush();
    die();

}

add_action('wp_ajax_update_game_list', 'update_game_list_callback');

function update_game_list_callback() {

    ob_clean();
    global $wpdb;

    //get the new games

    $query = "SELECT * 
FROM  `match` 
WHERE STATUS =  'new'";

    $new_games = $wpdb->get_results($query, ARRAY_A);

    if (count($new_games) > 0) {
        ?>

        <table>

            <tr class="table-header">
                <td colspan="3"><h2>Available Games</h2></td>
            </tr>

        <?php
        foreach ($new_games as $value) {
            ?>

                <tr>

            <?php
            //find the match owner
            $match_owner = 0;
            $i = 1;

            while ($match_owner == 0) {
                if ($value['player_' . $i] != 0) {
                    $match_owner = $value['player_' . $i];
                }
                $i++;
            }

            /* OLD WAY
              $query = "SELECT name
              FROM  `player`
              WHERE id = $match_owner";

              $owner_name = $wpdb->get_var($query);
             * 
             */

            $owner = get_user_by('id', $match_owner);
            $owner_name = get_user_meta($owner->ID, 'first_name', true);
            ?>

                    <td><?php echo $owner_name ?>'s Game</td>
                    <td>
                        <table class="sub-player-list">
            <?php
            for ($i = 1; $i <= 4; $i++) {
                if ($value['player_' . $i] != 0) {

                    $player = get_user_by('id', $value['player_' . $i]);
                    $user_meta = get_user_meta($player->ID);
                    $wins = (!isset($user_meta['wins'])) ? 0 : $user_meta['wins'][0];
                    $losses = (!isset($user_meta['losses'])) ? 0 : $user_meta['losses'][0];
                    $player_name = $user_meta['first_name'][0] . ' (' . $wins . '-' . $losses . ')';
                } else {

                    $player_name = 'empty spot';
                }
                ?>

                                <tr>
                                    <td class="player-<?php echo $i ?>"><?php echo $i ?></td>
                                    <td><?php echo $player_name ?></td>
                                </tr>

                <?php
            }
            ?>
                        </table>
                    </td>
                    <td><button onclick="window.location = 'waiting?<?php echo $value['id'] ?>'">Join Game</button></td>
                </tr>

            <?php
        }
        ?>

        </table>

        <?php
    } else {
        ?>

        <p>There are no open games to join.</p>

        <?php
    }

    $wpdb->flush();
    die();

}

include_once get_template_directory() . '/classes/wahoo.php';

global $the_game;
$the_game = new Wahoo();

add_action('wp_ajax_get_game', 'get_game_callback');

function get_game_callback() {

    global $the_game;

    $id = $_POST['MatchID'];

    $the_game->get_game($id);

}

add_action('wp_ajax_game_roll', 'game_roll_callback');

function game_roll_callback() {
    global $the_game;

    $id = $_POST['MatchID'];

    $the_game->roll($id);

}

add_action('wp_ajax_game_move', 'game_move_callback');

function game_move_callback() {

    global $the_game;

    $id = $_POST['MatchID'];
    $player = $_POST['player'];
    $marbles = $_POST['marbles'];

    foreach ($marbles as $key => $value) {
        $marbles[$key] = (int) $value;
    }


    $the_game->move($id, $player, $marbles);

}

add_action('wp_ajax_game_cant_move', 'game_cant_move_callback');

function game_cant_move_callback() {

    global $the_game;

    $id = $_POST['MatchID'];
    $player = $_POST['player'];


    $the_game->cant_move($id, $player);

}

add_action('wp_ajax_game_forfeit', 'game_forfeit_callback');

function game_forfeit_callback() {

    ob_clean();

    global $the_game;

    $id = $_POST['MatchID'];
    $player = $_POST['player'];


    $the_game->forfeit($id, $player);

    die();

}

add_action('wp_ajax_update_rematch', 'update_rematch_callback');
add_action('wp_ajax_nopriv_update_rematch', 'update_rematch_callback');

function update_rematch_callback() {

    ob_clean();
    global $wpdb;

    $id = $_POST['MatchID'];
    $open = $_POST['open'];


    $query = "SELECT * 
              FROM  `match` 
              WHERE id = $id";

    $match = $wpdb->get_row($query, ARRAY_A);

    $match_age = time() - $match['created'];
    $current_status = ($open == 'yes') ? 'new' : $match['status'];

    //check for rematch declines

    $declined = false;
    $player_count = 0;
    ?>
    <h1>Rematch</h1>

    <table>
    <?php
    for ($i = 1; $i <= 4; $i++) {

        if ($match['player_' . $i] != 0) {

            $player = get_user_by('id', $match['player_' . $i]);
            $user_meta = get_user_meta($match['player_' . $i]);
            $wins = (!isset($user_meta['wins'])) ? 0 : $user_meta['wins'][0];
            $losses = (!isset($user_meta['losses'])) ? 0 : $user_meta['losses'][0];
            $player_name = $user_meta['first_name'][0] . ' (' . $wins . '-' . $losses . ')';

            if ($user_meta['rematch_flag'][0] == 'waiting') {

                $player_status = 'waiting...';
            } else {

                $player_status = 'Accepted Rematch';
                $player_count++;
            }
        } else {

            $declined = true;
            $player_name = 'Declined Rematch';
            $player_status = '';
        }
        ?>

            <tr>
                <td class="player-<?php echo $i ?>"><?php echo $i ?></td>
                <td><?php echo $player_name ?></td>
                <td><?php echo $player_status ?></td>
            </tr>
        <?php
    }
    ?>
    </table>
    <div id="wait-status">
        <input id="current-status" type="hidden" name="current_status" value="<?php echo $current_status ?>" />

    <?php
    if ($player_count == 4) {

        $marbles = json_encode(array(0, 0, 0, 0));

        $wpdb->update(
                'match', array(
            'marbles_1' => $marbles,
            'marbles_2' => $marbles,
            'marbles_3' => $marbles,
            'marbles_4' => $marbles,
            'status' => 'in_progress',
            'modified' => time()
                ), array('ID' => $id), '%s', array('%d')
        );
        ?>

            <p id="start-game" rel="yes">4 players have joined the game! Starting game...</p>

            <p><button onclick="window.location = 'game?<?php echo $id ?>'">Start Game</button></p>

        <?php
    } elseif ($open == 'yes') {

        //zero out players whos status is still waiting

        for ($i = 1; $i <= 4; $i++) {

            $player = get_user_by('id', $match['player_' . $i]);
            $user_meta = get_user_meta($match['player_' . $i]);

            if ($user_meta['rematch_flag'][0] == 'waiting') {

                $match['player_' . $i] = 0;
            }
        }

        $wpdb->update(
                'match', array(
            'player_1' => $match['player_1'],
            'player_2' => $match['player_2'],
            'player_3' => $match['player_3'],
            'player_4' => $match['player_4'],
            'status' => 'new'
                ), array('ID' => $id), '%s', array('%d')
        );
        ?>

            <p>Updating game status...</p>

        <?php
    } elseif ($declined) {
        ?>

            <p>One or more players have declined the rematch.</p>
            <p>Allow other logged in users to join? (If any of the remaining players choose this option the game will be opened, allowing other users to join.)</p>
            <p><input type="radio" name="open" value="yes" />Yes&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="open" value="no" checked />No</p>


    <?php } elseif ($match_age > 90) { ?>

            <p>Still waiting for other players to accept the rematch.</p>
            <p>Allow other logged in users to join? (If any of the remaining players choose this option the game will be opened, allowing other users to join.)</p>
            <p><input id="open-game" type="radio" name="open" value="yes" />Yes<input type="radio" name="open" value="no" checked />No</p>

    <?php } ?>

    </div>

    <?php
    $wpdb->flush();
    die();

}

add_action('wp_ajax_game_timeout', 'game_timeout_callback');
add_action('wp_ajax_nopriv_game_timeout', 'game_timeout_callback');

function game_timeout_callback() {

    ob_clean();

    global $the_game;

    $match_id = $_POST['MatchID'];
    $player = $_POST['player'];

    $the_game->timeout($match_id, $player);


    die();

}

add_action('wp_ajax_game_sync_timeout', 'game_sync_timeout_callback');
add_action('wp_ajax_nopriv_game_sync_timeout', 'game_sync_timeout_callback');

function game_sync_timeout_callback() {

    ob_clean();

    global $the_game;

    $match_id = $_POST['MatchID'];

    $the_game->sync_timeout($match_id);


    die();

}
?>