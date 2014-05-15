<?php
require('wp-includes/wp-db.php');
require('wp-config.php');

$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);

//get the new games

$query = "SELECT * 
FROM  `match` 
WHERE STATUS =  'new'";

$new_games = $wpdb->get_results($query, ARRAY_A);
?>

<table>

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

            $query = "SELECT name
                      FROM  `player` 
                      WHERE id = $match_owner";

            $owner_name = $wpdb->get_var($query);
            ?>

            <td><?php echo $owner_name ?>'s Game</td>
            <td>
                <table>
                    <?php
                    for ($i = 1; $i <= 4; $i++) {
                        if ($value['player_' . $i] != 0) {
                            $query = "SELECT name
                                            FROM  `player` 
                                            WHERE id = {$value['player_' . $i]}";
                            $player_name = $wpdb->get_var($query);
                        } else {
                            $player_name = 'empty spot';
                        }
                        ?>

                        <tr>
                            <td><?php echo $i ?></td>
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
<?php $wpdb->flush(); ?>