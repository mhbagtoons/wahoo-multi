<?php
if (!is_user_logged_in()) {
    wp_redirect(site_url());
    exit;
}

//$q_str = $_SERVER['QUERY_STRING'];

global $wpdb;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$query = "SELECT * FROM `match` where (player_1 = $user_id or player_2 = $user_id  or player_3 = $user_id  or player_4 = $user_id ) and (status = 'in_progress' or status like 'timeout%')";

$match = $wpdb->get_row($query, ARRAY_A);

//is this a valid game?
//TODO

if (is_null($match)) {
    wp_redirect(site_url());
    exit;
}


//what is the players board position?

$player_names = array();


for ($i = 1; $i <= 4; $i++) {
    $player = 'player_' . $i;

    if ($match[$player] == $user_id) {
        $player_position = $player;
    }

    $player_names[] = get_user_meta($match[$player], 'first_name', true);
}

$user_meta = get_user_meta($current_user->ID);

get_header();
?>
<body>

    <div id="wrapper">

        <div id="leftSidebar">
            <h5 style="position: absolute;text-align: center;width: 180px;top: 82px;font-family: Arial;font-size: 23px;color: #D8E0E4;">Multi-Player</h5>
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg" width="180" height="80" alt="Wahoo" /><br />
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_dice.jpg" width="180" height="149" alt="Six" />
            <div id="player-list-side">

                <h3>Players</h3>

                <ul>

                    <?php
                    $i = 1;
                    foreach ($player_names as $value) {
                        ?>

                        <li id="player-<?php echo $i ?>"><?php echo $value ?></li>

                        <?php
                        $i++;
                    }
                    ?>

                </ul>

            </div>

            <ul id="pages">
                <li><a href="<?php echo site_url('help') ?>">Help and Support</a></li>
            </ul>

            <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

        </div>

        <div id="game">

            <div id="header">
                <div id="menu">

                    <ul>
                        <li>Welcome <?php echo $user_meta['first_name'][0] ?></li>
                        <li>&bigcirc;</li>
                        
                        <?php if ($user_meta['timeout_flag'][0] != 'no') { ?>
                        
                        <li id="timeout"><a href="javascript:void(0)" onclick="timeout()">Timeout</a></li>
                        
                        <?php } ?>
                        
                        <li><a href="javascript:void(0)" onclick="leaveGame()">Leave Game</a></li>
                    </ul>

                </div>
            </div>

            <div id="board">
                <div id="exit-ne"></div>
                <div id="exit-se"></div>
                <div id="exit-sw"></div>
                <div id="exit-nw"></div>
                <div id="dice-1"> <img class="frame-1" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_1.png" width="77" height="89" /> <img class="frame-2" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_2.png" width="76" height="93" /> <img class="frame-3" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_3.png" width="83" height="108" /> <img class="final-1" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_one.png" width="53" height="62" /> <img class="final-2" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_two.png" width="47" height="58" /> <img class="final-3" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_three.png" width="50" height="59" /> <img class="final-4" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_four.png" width="54" height="62" /> <img class="final-5" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_five.png" width="52" height="61" /> <img class="final-6" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_six.png" width="50" height="60" /> </div>
                <div id="dice-2"> <img class="frame-3" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_1_left.png" width="77" height="89" /> <img class="frame-2" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_2_left.png" width="76" height="93" /> <img class="frame-1" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_3_left.png" width="83" height="108" /> <img class="final-1" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_one_left.png" width="53" height="62" /> <img class="final-2" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_two_left.png" width="47" height="58" /> <img class="final-3" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_three_left.png" width="50" height="59" /> <img class="final-4" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_four_left.png" width="54" height="62" /> <img class="final-5" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_five_left.png" width="52" height="61" /> <img class="final-6" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_six_left.png" width="50" height="60" /> </div>
                <div id="dice-3"> <img class="frame-3" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_1_left.png" width="77" height="89" /> <img class="frame-2" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_2_left.png" width="76" height="93" /> <img class="frame-1" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_3_left.png" width="83" height="108" /> <img class="final-1" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_one_left.png" width="53" height="62" /> <img class="final-2" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_two_left.png" width="47" height="58" /> <img class="final-3" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_three_left.png" width="50" height="59" /> <img class="final-4" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_four_left.png" width="54" height="62" /> <img class="final-5" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_five_left.png" width="52" height="61" /> <img class="final-6" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_six_left.png" width="50" height="60" /> </div>
                <div id="dice-4"> <img class="frame-1" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_1.png" width="76" height="88" /> <img class="frame-2" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_2.png" width="82" height="93" /> <img class="frame-3" src="<?php echo get_bloginfo('template_directory') ?>/images/dice_ani_top_3.png" width="81" height="102" /> <img class="final-1" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_one.png" width="53" height="62" /> <img class="final-2" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_two.png" width="47" height="58" /> <img class="final-3" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_three.png" width="50" height="59" /> <img class="final-4" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_four.png" width="54" height="62" /> <img class="final-5" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_five.png" width="52" height="61" /> <img class="final-6" src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_six.png" width="50" height="60" /> </div>
                <div id="marble-red-1"><img src="<?php echo get_bloginfo('template_directory') ?>/images/red_marble.png" width="26" height="28" />
                    <div id="active-red-1"></div>
                </div>
                <div id="marble-red-2"><img src="<?php echo get_bloginfo('template_directory') ?>/images/red_marble.png" width="26" height="28" />
                    <div id="active-red-2"></div>
                </div>
                <div id="marble-red-3"><img src="<?php echo get_bloginfo('template_directory') ?>/images/red_marble.png" width="26" height="28" />
                    <div id="active-red-3"></div>
                </div>
                <div id="marble-red-4"><img src="<?php echo get_bloginfo('template_directory') ?>/images/red_marble.png" width="26" height="28" />
                    <div id="active-red-4"></div>
                </div>
                <div id="marble-white-1"><img src="<?php echo get_bloginfo('template_directory') ?>/images/white_marble.png" width="26" height="28" />
                    <div id="active-white-1"></div>
                </div>
                <div id="marble-white-2"><img src="<?php echo get_bloginfo('template_directory') ?>/images/white_marble.png" width="26" height="28" />
                    <div id="active-white-2"></div>
                </div>
                <div id="marble-white-3"><img src="<?php echo get_bloginfo('template_directory') ?>/images/white_marble.png" width="26" height="28" />
                    <div id="active-white-3"></div>
                </div>
                <div id="marble-white-4"><img src="<?php echo get_bloginfo('template_directory') ?>/images/white_marble.png" width="26" height="28" />
                    <div id="active-white-4"></div>
                </div>
                <div id="marble-green-1"><img src="<?php echo get_bloginfo('template_directory') ?>/images/green_marble.png" width="26" height="28" />
                    <div id="active-green-1"></div>
                </div>
                <div id="marble-green-2"><img src="<?php echo get_bloginfo('template_directory') ?>/images/green_marble.png" width="26" height="28" />
                    <div id="active-green-2"></div>
                </div>
                <div id="marble-green-3"><img src="<?php echo get_bloginfo('template_directory') ?>/images/green_marble.png" width="26" height="28" />
                    <div id="active-green-3"></div>
                </div>
                <div id="marble-green-4"><img src="<?php echo get_bloginfo('template_directory') ?>/images/green_marble.png" width="26" height="28" />
                    <div id="active-green-4"></div>
                </div>
                <div id="marble-black-1"><img src="<?php echo get_bloginfo('template_directory') ?>/images/black_marble.png" width="26" height="28" />
                    <div id="active-black-1"></div>
                </div>
                <div id="marble-black-2"><img src="<?php echo get_bloginfo('template_directory') ?>/images/black_marble.png" width="26" height="28" />
                    <div id="active-black-2"></div>
                </div>
                <div id="marble-black-3"><img src="<?php echo get_bloginfo('template_directory') ?>/images/black_marble.png" width="26" height="28" />
                    <div id="active-black-3"></div>
                </div>
                <div id="marble-black-4"><img src="<?php echo get_bloginfo('template_directory') ?>/images/black_marble.png" width="26" height="28" />
                    <div id="active-black-4"></div>
                </div>
                <div id="move-highlight"></div>
                <div id="kick-highlight"></div>
                <canvas id="move-arrow" width="800" height="600"></canvas>
            </div>
            <div id="control">
                <div id="button-wrapper">
                    <button id="roll"></button>
                    <button id="yes"></button>
                    <button id="no"></button>
                    <button id="rs-yes"></button>
                    <button id="rs-no"></button>
                    <div id="end-game">
                        <button id="rematch">Rematch</button><button id="new-game">New Game</button><button id="log-out">Log Out</button>
                    </div>
                </div>
            </div>
            <div id="message">
                <p></p>
            </div>

        </div>

    </div>

</body>

<script>

    $(function() {
        theGame = new Game(<?php echo $match['id'] ?>, '<?php echo $player_position ?>');
    });

    function leaveGame() {
        theGame.leaveGame();
    }
    
    function timeout() {
        theGame.timeout();
    }
    
    function test() {
        alert('here');
    }


</script>

</html>