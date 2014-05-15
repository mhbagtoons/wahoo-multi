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

    //update the match record

    $player_found = false;
    $empty_count = 0;


    for ($i = 1; $i <= 4; $i++) {

        $player = 'player_' . $i;

        if ($match[$player] == $user_id) {

            $match[$player] = 0;
            $player_found = true;
        }

        if ($match[$player] == 0) {

            $empty_count++;
        }
    }

    if ($player_found) {

        if ($empty_count == 4) {
            $result = $wpdb->delete( 'wp_wahoo.match', array( 'id' => $match_id ) );
        } else {

            $wpdb->update(
                    'match', array(
                'player_1' => $match['player_1'],
                'player_2' => $match['player_2'],
                'player_3' => $match['player_3'],
                'player_4' => $match['player_4']
                    ), array('id' => $match_id), '%s', array('%d')
            );
        }
    }
}

$wpdb->flush();

//redirect to home page
//regardless of update result
//user status will be sorted out
wp_redirect(site_url());
?>


