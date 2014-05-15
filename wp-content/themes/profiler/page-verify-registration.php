<?php
if (!isset($_GET['key'])) {
    echo "No access to this page";
    exit;
}

$success = true;

$user_ID = $_GET['ID'];
$ver_key = $_GET['key'];


$pending_meta = get_user_meta($user_ID);


if ($pending_meta['ver_key'][0] == $ver_key) {
    update_user_meta($user_ID, 'reg_pending', 'false');
    wp_redirect(site_url() . '?verify=success');
} else {
    $success = false;
    wp_redirect(site_url() . '?verify=error');
}


?>


