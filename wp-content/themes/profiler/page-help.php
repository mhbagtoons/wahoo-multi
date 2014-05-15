<?php
if (isset($_POST['support'])) {

    if ($_POST['email'] == "") {

        if (!preg_match('/^[\w\.-]+@[\w\.-]+\.\w+$/i', $_POST['usermail'])) {

            $message = 'email address is missing or format is incorrect';
            $message_class = 'message-red';
        } else {

            $subject = 'Wahoo Support Request';
            $e_mess = "user - {$_POST['usermail']} \n\r"
                    . "subject - {$_POST['subject']} \n\r"
                    . "details - {$_POST['details']}";

            wp_mail('mhbagtoons@gmail.com', $subject, $e_mess);

            $message = 'Your request has been sent';
            $message_class = 'message-green';
        }
    }
}

if (isset($_POST['lost_pwd'])) {

    if ($_POST['email'] == "") {

        if (!preg_match('/^[\w\.-]+@[\w\.-]+\.\w+$/i', $_POST['resend_email'])) {

            $re_message = 'email address is missing or format is incorrect';
            $message_class = 'message-red';
        } else {

            $user = get_user_by('email', $_POST['resend_email']);

            if ($user) {

                $new_pwd = wp_generate_password(8, false);
                wp_set_password($new_pwd, $user->ID);

                $subject = 'Wahoo Password Reset Request';
                $e_mess = "<html><head><title>Wahoo Password Reset Request</title></head>"
                        . "<body>"
                        . "<p>user - {$user->data->user_login}</p>"
                        . "<p>your new password - {$new_pwd}</p>"
                        . '<p>After logging in you can go to your profile and change your password. You can log in <a href="' . site_url() . '">here</a>.</p>'
                        . "</body></html>";

                $headers[] = 'From: Multi-Player Wahoo <info@bagtoons.com>';
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';

                wp_mail($_POST['resend_email'], $subject, $e_mess, $headers);

                $message = 'Your request has been sent';
                $message_class = 'message-green';
            } else {

                $re_message = 'There is no user registered with that email address';
                $message_class = 'message-red';
            }
        }
    }
}
?>

<?php get_header() ?>
<body>
    <div id="wrapper">
        <div id="leftSidebar">

            <a href="<?php echo site_url(); ?>"><div id="home-link"></div></a>
            <h5 style="position: absolute;text-align: center;width: 180px;top: 82px;font-family: Arial;font-size: 23px;color: #D8E0E4;">Multi-Player</h5>

            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg" width="180" height="80" alt="Wahoo" /><br />
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_dice.jpg" width="180" height="149" alt="Six" />



            <ul id="pages">
                <li><a href="<?php echo site_url(); ?>">Home</a></li>
            </ul>

            <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

        </div>
        <div id="game">

            <div id="content">

                <?php if ($message) { ?>

                    <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>

                <?php } ?>

                <?php while (have_posts()) : the_post(); ?>

                    <h1><?php the_title(); ?></h1>

                    <?php the_content(); ?>

                <?php endwhile; ?>

                <form id="help-form" method="post">
                    <input type="hidden" name="support" value="1" />
                    <input id="email-1" type="text" name="email" size="30" value="" />
                    <table>
                        <tr>
                            <td class="right"><span class="red">*</span> - Required Field</td>
                            <td></td>
                        </tr>

                        <tr>
                            <td class="right"><span class="red">*</span>email:</td>
                            <td><input type="text" name="usermail" size="40" value="" /></td>
                        </tr>

                        <tr>
                            <td class="right">Subject:</td>
                            <td><select name="subject">
                                    <option value="registration">Can't verify registration</option>
                                    <option value="other">Other</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="right">Details:</td>
                            <td>
                                <textarea name="details" rows="5" cols="40"></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td></td><td><input type="submit" value="Send" /></td>
                        </tr>
                    </table>

                </form>

                <h2>Reset Password</h2>

                <?php if ($re_message) { ?>

                    <div class="<?php echo $message_class; ?>"><?php echo $re_message; ?></div>

                <?php } ?>

                <p>If you forgot your password enter the email address you used to create your account and a new random password will be sent to you. After logging in you can go to your profile and change your password.</p>

                <form id="change-pwd" method="post">
                    <input type="hidden" name="lost_pwd" value="1" />
                    <input id="email-2" type="text" name="email" size="30" value="" />
                    <p>email: <input type="text" name="resend_email" size="40" value="" /><br />
                        <br />
                        <input type="submit" value="Sumbit" /></p>
                </form>


            </div>
        </div>
    </div>
</body>
</html>
