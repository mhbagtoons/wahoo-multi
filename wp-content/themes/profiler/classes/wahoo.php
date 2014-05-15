<?php

class Wahoo {

    public function is_valid_game() {
        //code

    }

    public function get_game($match_id) {

        ob_clean();

        global $wpdb;

        $query = "SELECT * FROM `match` where id = $match_id";
        $match = $wpdb->get_row($query, ARRAY_A);

        for ($i = 1; $i <= 4; $i++) {
            $key = "marbles_$i";
            $response[$key] = json_decode($match[$key]);
        }

        $response['turn'] = $match['turn'];
        $response['last_roll'] = $match['last_roll'];
        $response['turn_status'] = $match['turn_status'];
        $response['roll_count'] = $match['roll_count'];
        $response['winner'] = $match['winner'];
        $response['status'] = $match['status'];

        
        $last_modified = time() - $match['modified'];



        if (substr($match['status'], 0, 7) == 'timeout') {

            //if the timeout is over update the match status 

            if ($last_modified > 290) {

                $response['status'] = 'in_progress';

                $wpdb->update('match',
                        array(
                            'status' => 'in_progress',
                            'modified' => time()
                            ),
                        array('id' => $match_id),
                        '%s',
                        array('%s'));
            } else {

                //generate the timeout value and timeout player

                $response['timeout_value'] = 300 - $last_modified;
                $response['timeout_player'] = substr($match['status'], 8);
                $response['status'] = 'timeout';
            }
        } else {

            //check for abandoned players

            if ($last_modified > 120) {

                $this->forfeit($match_id, $match['turn']);
                $response['winner'] = (string) ($match['turn'] % 2 + 1);
                $response['status'] = 'forfeit';
            }
        }

        echo json_encode($response);

        $wpdb->flush();
        die();

    }

    public function roll($match_id) {

        ob_clean();

        global $wpdb;

        $query = "SELECT * FROM `match` where id = $match_id";
        $match = $wpdb->get_row($query, ARRAY_A);

        if ($match['status'] != 'forfeit') {

            $roll_value = rand(1, 6);

            $update_array = array(
                'last_roll' => $roll_value,
                'turn_status' => 'waiting_for_move',
                'roll_count' => $match['roll_count'] + 1
            );

            if (substr($match['status'], 0, 7) != 'timeout') {
                
                $update_array['modified'] = time();
                
            }

            $wpdb->update(
                    'match', $update_array, array('ID' => $match_id), '%s', array('%d')
            );
        }

        $wpdb->flush();
        die();

    }

    public function move($match_id, $player, $marbles) {

        ob_clean();

        global $wpdb;

        $query = "SELECT * FROM `match` where id = $match_id";
        $match = $wpdb->get_row($query, ARRAY_A);

        if ($match['status'] != 'forfeit') {

            //check kick

            $i = 1;
            $kick = false;

            while ($i <= 4 && !$kick) {

                if ($i != $player) {

                    $shift = ($player - $i) * 14;
                    $target = json_decode($match['marbles_' . $i]);

                    foreach ($marbles as $value) {

                        if ($value != 0) {

                            foreach ($target as $key_2 => $value_2) {

                                if ($value_2 < 56 || $value_2 == 60) {

                                    $target_marble = ($value_2 == 60) ? 60 : ($value_2 - $shift + 56) % 56;

                                    if ($value == $target_marble) {

                                        $kick = true;
                                        $kicked_player = $i;
                                        $target[$key_2] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                $i++;
            }

            //check for winner

            $all_home = array(0, 0, 0, 0);
            $winner = 0;


            for ($i = 1; $i <= 4; $i++) {

                if ($i == $player) {
                    $test = $marbles;
                } else {
                    $test = json_decode($match['marbles_' . $i]);
                }



                foreach ($test as $value) {

                    if ($value > 55 && $value < 60) {

                        $all_home[$i - 1] ++;
                    }
                }
            }


            if ($all_home[0] == 4 && $all_home[2] == 4) {

                $winner = 1;
            } elseif ($all_home[1] == 4 && $all_home[3] == 4) {

                $winner = 2;
            }

            //build update array

            $marbles = json_encode($marbles);
            $turn = ($match['last_roll'] == 6) ? $match['turn'] : ($match['turn'] % 4) + 1;

            $turn_status = 'waiting_for_roll';
            $update_array = array(
                'turn' => $turn,
                'turn_status' => $turn_status,
                'marbles_' . $player => $marbles
            );

            if ($kick) {

                $update_array['marbles_' . $kicked_player] = json_encode($target);
            }

            switch ($winner) {
                case 1:

                    //update the user meta

                    $this->updateWinLoss(array($match['player_1'], $match['player_2'], $match['player_3'], $match['player_4']));

                    $this->rematch($match);

                    //append the update array

                    $update_array['status'] = 'complete';
                    $update_array['winner'] = 1;

                    break;

                case 2:

                    //update the user meta

                    $this->updateWinLoss(array($match['player_2'], $match['player_3'], $match['player_4'], $match['player_1']));

                    $this->rematch($match);

                    //append the update array

                    $update_array['status'] = 'complete';
                    $update_array['winner'] = 2;

                    break;

                default:
                    break;
            }
            
            if (substr($match['status'], 0, 7) != 'timeout') {
                
                $update_array['modified'] = time();
                
            }

            $wpdb->update(
                    'match', $update_array, array('ID' => $match_id), '%s', array('%s')
            );
        }
        
        

        $wpdb->flush();
        die();

    }

    public function cant_move($match_id, $player) {

        ob_clean();

        global $wpdb;

        $query = "SELECT * FROM `match` where id = $match_id";
        $match = $wpdb->get_row($query, ARRAY_A);

        if ($match['status'] != 'forfeit') {

            $turn = ($match['last_roll'] == 6) ? $match['turn'] : ($match['turn'] % 4) + 1;
            $turn_status = 'waiting_for_roll';
            $update_array = array(
                'turn' => $turn,
                'turn_status' => $turn_status
            );
            
            if (substr($match['status'], 0, 7) != 'timeout') {
                
                $update_array['modified'] = time();
                
            }

            $wpdb->update(
                    'match', $update_array, array('ID' => $match_id), '%s', array('%s')
            );
        }

        $wpdb->flush();

        die();

    }

    public function forfeit($id, $player) {

        global $wpdb;

        //get the match

        $query = "SELECT * 
                    FROM  `match` 
                    WHERE id = $id";

        $match = $wpdb->get_row($query, ARRAY_A);

        if ($match != null) {


            //get the default winner
            //which player is this?


            $winner = $player % 2 + 1;

            //update the match record
            $wpdb->update(
                    'match', array(
                'turn' => $player,
                'winner' => $winner,
                'status' => 'forfeit'
                    ), array('id' => $id), '%s', array('%s')
            );

            switch ($winner) {
                case 1:

                    //update the user meta

                    $this->updateWinLoss(array($match['player_1'], $match['player_2'], $match['player_3'], $match['player_4']));
                    $this->rematch($match);

                    break;

                case 2:

                    //update the user meta

                    $this->updateWinLoss(array($match['player_2'], $match['player_3'], $match['player_4'], $match['player_1']));
                    $this->rematch($match);

                    break;

                default:
                    break;
            }
        }

        $wpdb->flush();

    }

    public function timeout($match_id, $player) {

        global $wpdb;

        //get the match

        $query = "SELECT * 
                    FROM  `match` 
                    WHERE id = $match_id";

        $match = $wpdb->get_row($query, ARRAY_A);

        $player_user = $match['player_' . $player];

        //double check the users timeout_flag

        $flag = get_user_meta($player_user, 'timeout_flag', true);

        if ($flag != 'no') {

            //update the players user meta

            update_user_meta($player_user, 'timeout_flag', 'no');


            //update the match status

            $wpdb->update('match',
                    array(
                        'status' => 'timeout_' . $player,
                        'modified' => time()
                    ),
                    array('id' => $match_id),
                    '%s',
                    array('%s'));
        }

    }

    public function sync_timeout($match_id) {

        global $wpdb;
        $query = "SELECT * FROM `match` where id = $match_id";
        $match = $wpdb->get_row($query, ARRAY_A);

        
        $last_modified = time() - $match['modified'];

        if ($match['status'] == 'in_progress') {

            $timeout_value = 0;
        } else {

            $timeout_value = 300 - $last_modified;
        }



        echo $timeout_value;

    }

    private function updateWinLoss($ids = array()) {


        //update the user meta

        $wins = get_user_meta($ids[0], 'wins', true);
        $wins = $wins + 1;
        update_user_meta($ids[0], 'wins', $wins);

        $losses = get_user_meta($ids[1], 'losses', true);
        $losses = $losses + 1;
        update_user_meta($ids[1], 'losses', $losses);

        $wins = get_user_meta($ids[2], 'wins', true);
        $wins = $wins + 1;
        update_user_meta($ids[2], 'wins', $wins);

        $losses = get_user_meta($ids[3], 'losses', true);
        $losses = $losses + 1;
        update_user_meta($ids[3], 'losses', $losses);

    }

    private function rematch($match) {

        //update the user meta

        update_user_meta($match['player_1'], 'rematch_flag', 'waiting');
        update_user_meta($match['player_2'], 'rematch_flag', 'waiting');
        update_user_meta($match['player_3'], 'rematch_flag', 'waiting');
        update_user_meta($match['player_4'], 'rematch_flag', 'waiting');

    }

}

?>