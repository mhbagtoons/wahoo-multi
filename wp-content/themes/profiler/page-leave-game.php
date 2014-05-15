<?php

if (!is_user_logged_in()) {
    wp_redirect(site_url());
    exit;
}

$q_str = $_SERVER['QUERY_STRING'];


global $wpdb;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;



//get the match

$query = "SELECT * 
                    FROM  `match` 
                    WHERE id = $q_str";

$match = $wpdb->get_row($query, ARRAY_A);

if ($match != null) {

    $match_id = $match['id'];

    //get the default winner
    //which player is this?

    for ($i = 1; $i <= 4; $i++) {

        $player = 'player_' . $i;

        if ($match[$player] == $user_id) {

            $winner = ($i) % 2 + 1;
            $turn = $i;
        }
    }


//update the match record
    $wpdb->update(
            'match', array(
        'turn' => $turn,
        'winner' => $winner,
        'status' => 'forfeit'
            ), array('id' => $match_id), '%s', array('%d')
    );

    switch ($winner) {
        case 1:

            //update the user meta

            $wins = get_user_meta($match['player_1'], 'wins', true);
            $wins = $wins + 1;
            update_user_meta($match['player_1'], 'wins', $wins);

            $losses = get_user_meta($match['player_2'], 'losses', true);
            $losses = $losses + 1;
            update_user_meta($match['player_2'], 'losses', $wins);

            $wins = get_user_meta($match['player_3'], 'wins', true);
            $wins = $wins + 1;
            update_user_meta($match['player_3'], 'wins', $wins);

            $losses = get_user_meta($match['player_4'], 'losses', true);
            $losses = $losses + 1;
            update_user_meta($match['player_4'], 'losses', $wins);

            break;

        case 2:

            //update the user meta

            $losses = get_user_meta($match['player_1'], 'losses', true);
            $losses = $losses + 1;
            update_user_meta($match['player_1'], 'losses', $wins);

            $wins = get_user_meta($match['player_2'], 'wins', true);
            $wins = $wins + 1;
            update_user_meta($match['player_2'], 'wins', $wins);

            $losses = get_user_meta($match['player_3'], 'losses', true);
            $losses = $losses + 1;
            update_user_meta($match['player_3'], 'losses', $wins);

            $wins = get_user_meta($match['player_4'], 'wins', true);
            $wins = $wins + 1;
            update_user_meta($match['player_4'], 'wins', $wins);

            break;

        default:
            break;
    }
}

$wpdb->flush();

//redirect to home page
//regardless of update result
//user status will be sorted out
wp_redirect(site_url() . '?forfeit');
?>


