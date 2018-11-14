<?php
$qp_setup = qp_get_stored_setup();
$tabs = explode(",",$qp_setup['alternative']);
$firsttab = reset($tabs);
echo '<div class="wrap">';
echo '<h1>MultiPay</h1>';
$qppkey = get_option('qpp_key');
if ($qppkey['authorised']) {
    
    if ( isset ($_GET['tab'])) {
        qp_messages_admin_tabs($_GET['tab']); $tab = $_GET['tab'];
    } else {
    qp_messages_admin_tabs($firsttab); $tab = $firsttab;
    }
    qp_show_messages($tab);
} else {
    echo '<p>'.__('All payment detaile are saved to the database and you will receive an notification email of each payment','multipay').' '.__('To see all payments and be able to download the payment record you need to','multipay').' <a href="?page=multipay/settings.php&tab=upgrade">'.__('Upgrade to Pro','multipay').'</a>. '.__('It\'s only $10','multipay').'<p>';
}
echo '</div>';

function qp_messages_admin_tabs($current = 'default') { 
    $qp_setup = qp_get_stored_setup();
    $tabs = explode(",",$qp_setup['alternative']);
    sort($tabs);
    $message = get_option( 'qp_message' );
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        if ($tab) echo "<a class='nav-tab$class' href='?page=multipay/messages.php&tab=".$tab."'>$tab</a>";
    }
    echo '</h2>';
}

function qp_show_messages($form) {
    $qp_setup = qp_get_stored_setup(); 
    $qp = qp_get_stored_options($form);
	
    if( isset($_POST['qp_emaillist'])) {
        $message = get_option('qp_messages'.$form);
        $messageoptions = qp_get_stored_msg();
        $content = qp_messagetable ($form,'checked');
        $title = 'Payment List for '.$form.' as at '.date('j M Y'); 
        global $current_user;
        get_currentuserinfo();
        $qp_email = $current_user->user_email;
        $headers = "From: {<{$qp_email}>\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/html; charset=\"utf-8\"\r\n";	
        wp_mail($qp_email, $title, $content, $headers);
        qp_admin_notice(__('Message list has been sent to','multipay').' '.$qp_email.'.');
    }
    
    if (isset($_POST['qp_reset_message'])) delete_option('qp_messages'.$form);
	
    if( isset( $_POST['Submit'])) {
        $options = array( 'messageqty','messageorder','showaddress');
        foreach ( $options as $item) $messageoptions[$item] = stripslashes($_POST[$item]);
        update_option( 'qp_messageoptions', $messageoptions );
        qp_admin_notice(__('The messages options have been updated.','multipay'));
    }
	
    if( isset($_POST['qp_delete_selected'])) {
        $id = $_POST['formname'];
        $message = get_option('qp_messages'.$form);
        $count = count($message);
        for($i = 0; $i <= $count; $i++) {
            if ($_POST[$i] == 'checked') {
                unset($message[$i]);
            }
        }
        $message = array_values($message);
        update_option('qp_messages'.$form, $message ); 
        qp_admin_notice(__('Selected payments have been deleted.','multipay'));
    }
    
    $messageoptions = qp_get_stored_msg();
    $fifty = $hundred = $all = $oldest = $newest = '';
    $showthismany = '9999';
    if ($messageoptions['messageqty'] == 'fifty') $showthismany = '50';
    if ($messageoptions['messageqty'] == 'hundred') $showthismany = '100';
    $$messageoptions['messageqty'] = "checked";
    $$messageoptions['messageorder'] = "checked";
    $dashboard = '
    <p><a href="?page=multipay/settings.php">'.__('Go to the MultiPay Settings page','multipay').'</a></p>
    <form method="post" action="">
    <p><b>Show</b> <input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="fifty" ' . $fifty . ' /> 50 
    <input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="hundred" ' . $hundred . ' /> 100 
    <input style="margin:0; padding:0; border:none;" type="radio" name="messageqty" value="all" ' . $all . ' /> '.__('all payments','multipay').'.&nbsp;&nbsp;
    <b>List</b> <input style="margin:0; padding:0; border:none;" type="radio" name="messageorder" value="oldest" ' . $oldest . ' /> '.__('oldest first','multipay').' 
    <input style="margin:0; padding:0; border:none;" type="radio" name="messageorder" value="newest" ' . $newest . ' /> '.__('newest first','multipay').'
    &nbsp;&nbsp;
    <input style="margin:0; padding:0; border:none;" type="checkbox" name="showaddress" value="checked" ' . $messageoptions['showaddress'] . ' /> '.__('Show Addresses','multipay').'
    &nbsp;&nbsp;
    <input type="submit" name="Submit" class="button-secondary" value="'.__('Update options','multipay').'" />
    </form></p>';
    $dashboard .='<form method="post" id="download_form" action="">';
    $dashboard .= qp_messagetable($form,'');
    $dashboard .='<input type="hidden" name="formname" value = "'.$form.'" />
    <input type="submit" name="qp_emaillist" class="button-primary" value="'.__('Email List','multipay').'" />
    <input type="submit" name="qp_reset_message" class="button-secondary" value="Delete All" onclick="return window.confirm( \'Are you sure you want to delete all the payment details?\' );"/>
    <input type="submit" name="qp_delete_selected" class="button-secondary" value="Delete Selected" onclick="return window.confirm( \'Are you sure you want to delete the selected payment details?\' );"/>
    </form></div></div>';
    
    echo $dashboard;
}