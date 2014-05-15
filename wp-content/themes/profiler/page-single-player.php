<?php
$ver = 'normal';
if (!empty($_GET)) {
    $ver = $_GET['v'];
}
if ($ver != 'full') {
    $ua = $_SERVER['HTTP_USER_AGENT'];
//echo $ua;exit;
//$ua = 'BlackBerry9700/5.0.0.862 Profile/MIDP-2.1 Configuration/CLDC-1.1 VendorID/120';
    $patterns = array(
        '/Android.+Mobile/',
        '/iPod/',
        '/iPhone/',
        '/BlackBerry/'
    );
    $m_flag = false;
    foreach ($patterns as $value) {
        $result = preg_match($value, $ua);
        if ($result > 0) {
            $m_flag = true;
        }
    }
    if ($m_flag) {
        $url = $_SERVER['HTTP_HOST'];
        header('Location: http://' . $url . '/m');
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>The Game of Wahoo - HTML5 Version of the Classic Board Game</title>
        <meta name="description" content="Play a flash version the classic board game Wahoo. Wahoo rules and quick start guide provided." />
        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="icon" href="favicon.ico" />


        <script type="text/javascript">
            <!--
            function showpage(e) {
                var toDeactivate;
                var menu = { playlink: '#game', guidelink: '#guide', aboutlink: '#about' };
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
        <?php wp_head() ?>
    </head>
    <body>

        <div id="wrapper">
            <div id="announcement">
                <p>Attention Android and iPhone users: there is now a mobile web version of the game that should open automatically in most smartphones. You can chose the mobile layout manually if it does not automatically open in your device or go back to the standard web layout if does not display correctly in your device.</p>
            </div>
            <div id="leftSidebar"> <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg" width="180" height="80" alt="Wahoo" /><br />
                <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_dice.jpg" width="180" height="149" alt="Six" />
                <div id="left-menu">
                    <p id="playlink" class="active" onclick="showpage(this)">Play Wahoo</p>
                    <p id="guidelink" class="inactive" onclick="showpage(this)">Quick Start Guide</p>
                    <p id="aboutlink" class="inactive" onclick="showpage(this)">About</p>
                </div>

                <p id="hearts"><a href="http://hearts.bagtoons.com/"><span class="red"><strong>!New!</strong></span> - Hearts Card Game</a></p>
                <p id="contact"><a href="mailto:info@bagtoons.com">Contact</a></p>
                <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

            </div>
            <div id="game">
                <div id="header">
                    <div id="menu">
                        <div id="g-restart">
                            <p>Restart Game</p>
                        </div>
                        <div>
                            <p>Game Speed</p>
                            <div id="gs">
                                <p id="gs-slow">Slow</p>
                                <p id="gs-med" class="selected">Medium</p>
                                <p id="gs-fast">Fast</p>
                            </div>
                        </div>
                        <div id="mob-link">
                            <a href="m/index.php">Mobile Version</a>
                        </div>
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
                    </div>
                </div>
                <div id="message">
                    <p></p>
                </div>
            </div>
            <div id="guide">
                <h2>Quick Start Wahoo Guide</h2>
                <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo-diagram-01.jpg" width="793" height="593" /><br />
                <p>(terms in bold are indicated in the game diagram)</p>
                <p>Just click &#8220;Roll&#8221; (in most browsers you can just hit the spacebar). Your marbles are the red marbles. The green marbles are your partner's, so try and protect them. The &#8220;Roll&#8221; button appears automatically when it is your turn and you have to roll your dice. The program will tell you when you have no valid moves and if you only have one possible move, it will be made automatically.</p>
                <p>If you have more than one valid move you must select which marble you want to move. If the marble can make a valid move it will have a yellow halo around it when the mouse pointer is over the marble, otherwise the marble can't be moved. Once you click the marble it will be automatically moved. The program will prompt you if a move involves entering or exiting the <strong>shortcut</strong> hole.</p>
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
                <p>Version 2.1.1</p>
                <p>
                    Welcome to version 2.1 of Wahoo. This version no longer uses Flash but has been built instead using javascript, jquery, CSS3 and HTML5. The game will not function properly using Internet Explorer 6 or 7. Feel free to <a href="mailto:info@bagtoons.com">report any bugs or problems</a>.
                </p>
                <p>
                    Version 2.1 added feature - the progress of your current game will automatically be saved for 1 week using cookies, so if you unintentionally leave or close the page, you will be able to continue on from the point you last left the game. 
                </p>
                <p>
                    Version 2.1.1 - Messages that appear will remain visible for a longer period of time.
                </p>
                <p>
                    Version 2.1.2 - Fixed bug that was not allowing the player to kick an opponent from the center hole to all corners.
                </p>
            </div>
        </div>

    </body>
</html>
