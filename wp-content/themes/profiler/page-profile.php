<?php
if (!is_user_logged_in()) {
    wp_redirect(site_url());
    exit;
}

global $wpdb;

$current_user = wp_get_current_user();

$message = '';
$message_class = 'message-green';

if (isset($_POST['upload'])) {

    if ($_FILES['userfile']['error'] == 0) {

        if (!function_exists('wp_handle_upload'))
            require_once( ABSPATH . 'wp-admin/includes/file.php' );


        $uploadedfile = $_FILES['userfile'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile) {
            $image_info = getimagesize($movefile['file']);
            $img_width = $image_info[0];
            $img_height = $image_info[1];

            switch ($image_info[2]) {
                case IMAGETYPE_GIF:
                    $img = imagecreatefromgif($movefile['file']);
                    break;

                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    $img = imagecreatefromjpeg($movefile['file']);
                    break;

                case IMAGETYPE_PNG:
                    $img = imagecreatefrompng($movefile['file']);
                    break;
                default:
                    $message = 'Image type is not correct';
                    $message_class = 'message-red';
                    break;
            }

            if ($img) {

                $src_x = ($img_width - $img_height) / 2;
                $src_y = ($img_height - $img_width) / 2;

                if ($src_x < 0) {
                    $src_x = 0;
                    $square = $img_width;
                } else {
                    $src_y = 0;
                    $square = $img_height;
                }

                $img_new = imagecreatetruecolor(100, 100);
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefill($img_new, 0, 0, $white);

                imagecopyresampled($img_new, $img, 0, 0, $src_x, $src_y, 100, 100, $square, $square);
                imagejpeg($img_new, $movefile['file']);

                update_user_meta($current_user->ID, 'profile_pic', $movefile['url']);
            }



            $message = 'Profile picture updated successfully';
            $message_class = 'message-green';
        } else {
            $message = "Uplaod error. Please try a different picture file.";
            $message_class = 'message-red';
        }
    } else {
        $message = "You must choose a picture file.";
        $message_class = 'message-red';
    }
}

if (isset($_POST['update'])) {

    $success = true;

    $data = array(
        'user_name' => $current_user->data->user_login,
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'user_email' => $_POST['e_mail'],
        'pwd' => $_POST['pwd']
    );

    if ($data['first_name'] == '') {

        $message .= 'First name cannot be blank<br />';
        $message_class = 'message-red';
        $success = false;
    } elseif (!preg_match('/^[\w\.-]+@[\w\.-]+\.\w+$/i', $data['user_email'])) {

        $message .= 'email address format is incorrect<br />';
        $message_class = 'message-red';
        $success = false;
    } elseif ($_POST['pwd'] != $_POST['re_pwd']) {

        //check pwd match
        $message = 'Passwords do not match';
        $message_class = 'message-red';
        $success = false;
    }

    if ($success) {

        //update user

        $update_data = array(
            'ID' => $current_user->ID,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'user_email' => $data['user_email']
        );
        
        if ($data['pwd'] != '') {
            wp_set_password($data['pwd'], $current_user->ID);
        }

        wp_update_user($update_data);

        $wpdb->update(
                'player', array('name' => $data['first_name']), array('wp_user' => $current_user->ID), '%s', array('%s')
        );

        $message = 'Profile updated successfully';

        $user_meta = get_user_meta($current_user->ID);
        $data['profile_pic'] = $user_meta['profile_pic'][0];
    }
} else {

    $user_meta = get_user_meta($current_user->ID);
    $data = array(
        'user_name' => $current_user->data->user_login,
        'first_name' => $user_meta['first_name'][0],
        'last_name' => $user_meta['last_name'][0],
        'user_email' => $current_user->data->user_email,
        'profile_pic' => $user_meta['profile_pic'][0]
    );
}
?>
<?php get_header() ?>
<body>
    <div id="wrapper">
        <div id="leftSidebar">
            <h5 style="position: absolute;text-align: center;width: 180px;top: 82px;font-family: Arial;font-size: 23px;color: #D8E0E4;">Multi-Player</h5>
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_logo.jpg" width="180" height="80" alt="Wahoo" /><br />
            <img src="<?php echo get_bloginfo('template_directory') ?>/images/wahoo_dice.jpg" width="180" height="149" alt="Six" />
            <div id="left-menu">
                <p id="playlink" class="active" onclick="showpage(this)">Play Wahoo</p>
                <p id="guidelink" class="inactive" onclick="showpage(this)">Quick Start Guide</p>
                <p id="aboutlink" class="inactive" onclick="showpage(this)">About</p>
            </div>

            <p id="copyright">&copy;<?php echo date('Y'); ?>&nbsp;<a href="http://bagtoons.com" target="_blank">Bagtoons</a></p>

        </div>
        <div id="game">
            <div id="header">
                <div id="menu">

                    <ul>
                        <li>Welcome <?php echo $user_meta['first_name'][0] ?></li>
                        <li>&bigcirc;</li>
                        <li><a href="<?php echo get_site_url() ?>">Home</a></li>
                        <li><a href="<?php echo get_site_url() . '?logout' ?>">Log Out</a></li>
                    </ul>

                </div>
            </div>
            <div id="profile">
                <h5>username: <?php echo $data['user_name'] ?> (usernames cannot be changed)</h5>
                <h1>Profile</h1>

                <?php if ($message != '') { ?>

                    <div class="<?php echo $message_class ?>"><?php echo $message ?></div>

                <?php } ?>


                <div id="profile-pic-uplaod">
                    <p>Profile Pic</p>

                    <p><img src="<?php echo $data['profile_pic'] ?>" width="100" height="100" /></p>

                    <script type="text/javascript">

                        function uploadfile() {
                            $('#button').hide();
                            $('#uploading').show('fast');
                            $('#fileupload').submit();
                        }

                    </script>

                    <form id="fileupload" action="" enctype="multipart/form-data" method="post">
                        <input type="hidden" name="upload" value="1" /> 
                        <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                        <table>

                            <tr>
                                <td>
                                    (jpeg, png or gif)<br />
                                    <input id="userfile" type="file" name="userfile" />
                                </td>
                            </tr>
                            <tr id="button">
                                <td>
                                    <button type="button" onclick="uploadfile()">Upload</button>
                                </td>
                            </tr>
                            <tr id="uploading">
                                <td>
                                    Uploading...
                                </td>
                            </tr>



                        </table>
                    </form>


                </div>


                <form id="info" action="" method="post">
                    <input type="hidden" name="update" value="1" />
                    <table>
                        <tr>
                            <td class="right">First Name:</td>
                            <td><input type="text" name="first_name" value="<?php echo $data['first_name'] ?>" /></td>
                        </tr>
                        <tr>
                            <td class="right">Last Name:</td>
                            <td><input type="text" name="last_name" value="<?php echo $data['last_name'] ?>" /></td>
                        </tr>
                        <tr>
                            <td class="right">email:</td>
                            <td><input type="text" name="e_mail" value="<?php echo $data['user_email'] ?>" size="40" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><br/>If you want to change your password, enter it here. Otherwise leave it blank to keep your current password:</td>
                        </tr>
                        <tr>
                            <td class="right">password:</td>
                            <td><input type="password" name="pwd" value="" /></td>
                        </tr>
                        <tr>
                            <td class="right">Re-type password:</td>
                            <td><input type="password" name="re_pwd" value="" /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" value="Update" /></td>
                        </tr>
                    </table>

                </form>
            </div>
        </div>
    </div>
</body>
</html>
