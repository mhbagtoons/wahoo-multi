<?php
if (is_user_logged_in()) {
    wp_redirect(site_url('profile'));
    exit;
}

$pre_pop = array(
    'log' => '',
    'email' => '',
    'pwd' => '',
    're_pwd' => ''
);

$success = true;

if (isset($_POST['register'])) {

    $pre_pop['log'] = $_POST['log'];
    $pre_pop['email'] = $_POST['email'];
    $pre_pop['pwd'] = $_POST['pwd'];
    $pre_pop['re_pwd'] = $_POST['re_pwd'];

    //check username exists?

    if (username_exists($_POST['log'])) {

        $message = 'User name already exists';
        $success = false;
    } elseif (email_exists($_POST['email'])) {
        
        $message = 'That email address is already in use';
        $success = false;
        
    } elseif ($_POST['pwd'] != $_POST['re_pwd']) {

        //check pwd match
        $message = 'Passwords do not match';
        $success = false;
        $pre_pop['pwd'] = $pre_pop['re_pwd'] = '';
    } elseif ($_POST['log'] == '') {

        $message = 'User cannot be empty';
        $success = false;
    } elseif (!preg_match('/^[\w\.-]+@[\w\.-]+\.\w+$/i', $_POST['email'])) {

        $message = 'email address format is incorrect';
        $success = false;
    } elseif ($_POST['pwd'] == '') {

        $message = 'Password cannot be empty';
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

        $subject = 'Registration Verification Link';
        $e_mess = 'To complete the registration process <a href="http://hearts-multi:8888/verify-registration/?ID=' . $user_id . '&key=' . $ver_key . '">click this link</a>';

        wp_mail('mhbagtoons@gmail.com', $subject, $e_mess);
    }
}
?>
<?php get_header() ?>
<body>
    <h1>Register</h1>

    <ul>
        <li><a href="<?php echo site_url() ?>">Home</a></li>
        <li><a href="<?php echo site_url('lgn') ?>">Login</a></li>

    </ul>

    <?php if ($success && isset($_POST['register'])) { ?>

        <p>Your registration has been processed.</p>
        <p>An email has been sent to the email address you provided.</p>
        <p>Click the link in the email and follow the instructions to complete the registration process</p>


    <?php } else { ?>

        <?php if ($message) { ?>

            <div class="message"><?php echo $message ?></div>

        <?php } ?>

        <form action="" method="post">
            <input type="hidden" name="register" value="1" />
            <table>
                <tr>
                    <td class="right">Username:</td>
                    <td><input type="text" name="log" value="<?php echo $pre_pop['log'] ?>" /></td>
                </tr>
                <tr>
                    <td class="right">email:</td>
                    <td><input type="text" name="email" size="40" value="<?php echo $pre_pop['email'] ?>" /></td>
                </tr>
                <tr>
                    <td class="right">Password:</td>
                    <td><input type="password" name="pwd" value="<?php echo $pre_pop['pwd'] ?>" /></td>
                </tr>
                <tr>
                    <td class="right">Re-Type Password:</td>
                    <td><input type="password" name="re_pwd" value="<?php echo $pre_pop['re_pwd'] ?>" /></td>
                </tr>
                <tr>
                    <td></td><td><input type="submit" value="Register" /></td>
                </tr>
            </table>

        </form> 

    <?php } ?>

</body>
</html>