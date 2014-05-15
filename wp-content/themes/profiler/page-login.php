<?php

if (is_user_logged_in()) {
    wp_redirect(site_url('profile'));
    exit;
}


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
            $message = $user->get_error_message();
            $message = preg_replace('/\s<a.+a>\?/', '', $message);
        } else {
            wp_redirect(site_url('profile'));
            exit;
        }
    } else {

        if ($pending_meta['ver_key'][0]) {
            $message = 'You must verify you registration before logging in. Check your email for the verification link message';
        } else {
            $message = 'The username you entered does not exist.';
        }
    }
}
get_header();
?>
<body>
    <h1>Log In</h1>
    
    <ul>
        <li><a href="<?php echo site_url() ?>">Home</a></li>
        <li><a href="<?php echo site_url('register') ?>">Register</a></li>
        
    </ul>

    <?php if ($message) { ?>

        <div class="<?php echo $message_class ?>"><?php echo $message ?></div>

    <?php } ?>

    <form method="post">
        <input type="hidden" name="process_login" value="1" />
        <p>Username: <input type="text" name="log" /></p>
        <p>Password: <input type="password" name="pwd" /></p>
        <p><input type="submit" value="login" /></p>


    </form> 



</body>
</html>