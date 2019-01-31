<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'MultiPay_Settings', false ) ) {
    return;
}

class MultiPay_Settings {
    
    function output() {
        $qp_setup = qp_get_stored_setup();
        $form = $qp_setup['current'];
        ?>
        <div id="multipay" class="wrap">
            <h1>MultiPay</h1>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php
                        if ( isset ($_GET['tab'])) {
                            self::admin_tabs($_GET['tab']); $tab = $_GET['tab'];
                        } else {
                            self::admin_tabs('setup'); $tab = 'setup';
                        }
                        switch ($tab) {
                            case 'setup' : self::setup($form); break;
                            case 'settings' : self::form_options($form); break;
                            case 'styles' : self::styles($form); break;
                            case 'send' : self::send_page($form); break;
                            case 'address' : self::address ($form); break;
                            case 'shortcodes' : self::shortcodes (); break;
                            case 'reset' : self::reset_page($form); break;
                            case 'coupon' : self::coupon_codes($form); break;
                            case 'paypal' : self::paypal_api(); break;
                            case 'stripe' : self::stripe_api(); break;
                            case 'worldpay' : self::worldpay_api(); break; 
                            case 'amazon' : self::amazon_api(); break;  
                            case 'autoresponce' : self::autoresponce_page($form); break;
                            case 'upgrade' : self::upgrade(); break;
                        }
                    ?>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="box rating">
                            <h2 class="title"><?php _e( 'What\'s Your Rating?','multipay' ); ?></h2>
                            <div class="rate">
                                <div class="rating-stars">
                                    <a data-rating="1" target="_blank" href="//wordpress.org/support/plugin/multipay/reviews/?rate=1#new-post" title="Poor"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="2" target="_blank" href="//wordpress.org/support/plugin/multipay/reviews/?rate=2#new-post" title="Works"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="3" target="_blank" href="//wordpress.org/support/plugin/multipay/reviews/?rate=3#new-post" title="Good"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="4" target="_blank" href="//wordpress.org/support/plugin/multipay/reviews/?rate=4#new-post" title="Great"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="5" target="_blank" href="//wordpress.org/support/plugin/multipay/reviews/?rate=5#new-post" title="Fantastic!"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a>
                                </div>
                            </div>
                            <p><?php echo sprintf( __( 'If you could spare <a href="%s" target="_blank" rel="nofollow">30 seconds to rate this plugin</a>, that would be fantastic!','multipay' ), 'https://wordpress.org/support/plugin/multipay/reviews?rate=5#new-post' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need Help?','multipay' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow">Read the FAQ on WordPress.org</a> to see if your question is answered.','multipay' ), 'https://wordpress.org/plugins/multipay/#faq' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need Support?','multipay' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow">Visit the WordPress.org Support Forum</a> to read the existing topics or  create a new topic to get support.','multipay' ), 'https://wordpress.org/support/plugin/multipay/' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need a Feature?','multipay' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow">Request a new feature on the WordPress.org Support Forum</a>','multipay' ), 'https://wordpress.org/support/plugin/multipay/' ); ?></p>
                        </div>
                        <div class="box github">
                            <h2 class="title"><?php _e( 'Want to Contribute?','multipay' ); ?></h2>
                            <p><a href="https://github.com/etalented/multipay" target="_blank"><img src="https://assets-cdn.github.com/images/modules/logos_page/GitHub-Logo.png" alt=""></a></p>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow">The entire codebase is on GitHub</a>, so please feel free to make a pull request.','multipay' ), 'https://github.com/etalented/multipay' ); ?></p>
                        </div>
                        <p class="byline"><script src="//etalented.co.uk/bl/bl.js"></script></p>
                    </div>
                </div>
            </div>
        </div>
        <script>
        jQuery( document ).ready( function( $ ) {
            $( '.rating-stars' ).find( 'a' ).on( 'hover', function() {
                $( this ).nextAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-filled' ).addClass( 'dashicons-star-empty' );
                $( this ).prevAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
                $( this ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
            });
        });
        </script>
        <?php
    }

    // Build the Tabs
    function admin_tabs($current = 'settings') {
        $tabs = array(
            'setup' => 'Setup',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'worldpay' => 'WorldPay',
            'amazon' => 'Amazon',
            'settings' => 'Form Settings',
            'styles' => 'Styling',
            'send' => 'Processing',
            'autoresponce' => 'Auto Responder',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'worldpay' => 'WorldPay',
            'amazon' => 'Amazon',
            'upgrade' => '<span style="color:#8951A5;">Upgrade</span>',
        );
        ?>
        <nav class="nav-tab-wrapper">
            <?php foreach( $tabs as $id => $name ): ?>
                <?php $class = ( $id === $current ) ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
                <?php $url = 'admin.php?page=multipay-settings&tab=' . urlencode( $id ); ?>
                <a class="<?php echo $class; ?>" href="<?php echo $url; ?>"><?php echo $name; ?></a>
            <?php endforeach ?>
        </nav>
        <?php
    }

    function head_css() {
        $data = '<style type="text/css" media="screen">'."\r\n".qp_generate_css()."\r\n".'</style>';
        echo $data;
    }

    // Plugin Setup
    function setup ($form) {
        $qp_setup = qp_get_stored_setup();
        $qp_curr = qp_get_stored_curr();
        $new_curr = $qp_curr['default'];
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {

            $qp_setup['alternative'] = filter_var($_POST['alternative'],FILTER_SANITIZE_STRING);

            if (!empty($_POST['new_form'])) {
                $qp_setup['current'] = stripslashes($_POST['new_form']);
                $qp_setup['current'] = filter_var($qp_setup['current'],FILTER_SANITIZE_STRING);
                $qp_setup['current'] = preg_replace("/[^A-Za-z]/",'',$qp_setup['current']);
                $qp_setup['alternative'] = $qp_setup['current'].','.$qp_setup['alternative'];
            }
            else {
                $qp_setup['current'] = filter_var($_POST['current'],FILTER_SANITIZE_STRING);
            }

            $arr = explode(",",$qp_setup['alternative']);
            foreach ($arr as $item) {
                $qp_curr[$item] = stripslashes($_POST['qp_curr'.$item]);
                $qp_curr[$item] = filter_var($qp_curr[$item],FILTER_SANITIZE_STRING);
            }

            if (!empty($_POST['new_form'])) {
                $formname = $qp_setup['current'];
                $qp_curr[$formname] = stripslashes($_POST['new_curr']);
                $qp_curr[$formname] = filter_var($qp_curr[$formname],FILTER_SANITIZE_STRING);
            }
            update_option( 'qp_curr', $qp_curr);
            update_option( 'qp_setup', $qp_setup);
            qp_admin_notice(__('The forms have been updated','multipay'));
            if ($_POST['qp_clone'] && !empty($_POST['new_form'])) self::form_clone($qp_setup['current'],$_POST['qp_clone']);

        }

        if( isset( $_POST['Default']) && check_admin_referer("save_qp")) {
            $qp_curr['default'] = stripslashes($_POST['default_currency']);
            update_option( 'qp_curr', $qp_curr);
            qp_admin_notice(__('Default current has been updated','multipay'));
        }


        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            self::delete_everything();
            qp_admin_notice(__('Everything has been reset','multipay'));
            $qp_setup = qp_get_stored_setup();
        }

        $arr = explode(",",$qp_setup['alternative']);
        foreach ($arr as $item) if (isset($_POST['deleteform'.$item]) && $_POST['deleteform'.$item] == $item && isset($_POST['delete'.$item]) && $item != '') {
            $forms = $qp_setup['alternative'];
            self::delete_things($_POST['deleteform'.$item]);
            $qp_setup['alternative'] = str_replace($_POST['deleteform'.$item].',','',$forms); 
            $qp_setup['current'] = 'default';
            update_option('qp_setup', $qp_setup);
            qp_admin_notice(__('The form named','multipay').' ' .$item.' '.__('has been deleted','multipay'));
            $form = 'default';
            break;
        }

        $new = '<h2>'.__('Setting up the form','multipay').'</h2>
        <p>'.__('Click on the tabs for the payment providers you wish to use and add your credentials.','multipay').'</p>
        <p>'.__('Update the settings.','multipay').'</p>
        <h2>'.__('Adding the payment form to your site','multipay').'</h2>
        <p>'.__('Add the payment form to your posts or pages use the shortcode: <code>[multipay]</code>.','multipay').'</p>
        <p>'.__('There is also a widget called "MultiPay" you can drag and drop into a sidebar.','multipay').'</p>
        <p>'.__('That\'s it. The payment form is ready to use.','multipay').'</p>
        <p>'.__('You can now use the other tabs to change a whole range of settings.','multipay').'</p>
        <form method="post" action="">
        <h2>Currency</h2>
        <p>Enter your currency code: <input type="text" style="width:3em;padding:1px;" name="default_currency" value="' . $qp_curr['default'].'" /></p>
        <p><input type="submit" name="Default" class="button-primary" style="color: #FFF;" value="'.__('Update Currency','multipay').'" /></p>';
        $new .= wp_nonce_field("save_qp");
        $new .= '</form>
        <div class="qpupgrade"><a href="admin.php?page=multipay-settings&tab=upgrade">
        <h3>'.__('Upgrade to Pro','multipay').'</h3>
        <p>'.__('Upgrading gets you multiple forms, more fields, autoresponder and the payment manager. All for just $10.','multipay').'</p>
        <p>'.__('Click to find out more','multipay').'</p>
        </a></div>';

        $qppkey = get_option('qpp_key');
        if (!$new_curr) $new_curr = $qp_curr['default'];
        $content ='<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('Setup','multipay').'</h2>';
        if (!$qppkey['authorised']) {
            $content .= $new;
        } else {
            $content .= '<form method="post" action="">
            <h2>'.__('Existing Forms','multipay').'</h2>
            <table>
            <tr>
            <td><b>'.__('Form name','multipay').'&nbsp;&nbsp;</b></td>
            <td><b>'.__('Currency','multipay').'</b></td>
            <td><b>'.__('Shortcode','multipay').'</b></td>
            </tr>';
            $arr = explode(",",$qp_setup['alternative']);
            sort($arr);
            foreach ($arr as $item) {
                $checked = ($qp_setup['current'] == $item ? 'checked' : 'default');
                $content .='<tr>
                <td><input type="radio" name="current" value="' .$item . '" ' .$checked . ' /> '.$item.'</td>
                <td><input type="text" style="width:3em;padding:1px;" name="qp_curr'.$item.'" value="' . $qp_curr[$item].'" /></td>';
                $shortcode = ($item != 'default' ?' form="'.$item.'"' : '');
                $content .= '<td><code>[multipay'.$shortcode.']</code></td><td>';
                if ($item != 'default') $content .= '<input type="hidden" name="deleteform'.$item.'" value="'.$item.'"><input type="submit" name="delete'.$item.'" class="button-secondary" value="delete" onclick="return window.confirm( \'Are you sure you want to delete '.$item.'?\' );" />';
                $content .= '</td></tr>';
            }
            $content .= '</table>
            <h2>'.__('Create New Form','multipay').'</h2>
            <p>'.__('Enter form name (letters only - no numbers, spaces or punctuation marks)','multipay').'</p>
            <p>'.__('<input type="text" label="new_Form" name="new_form" value="" />','multipay').'</p>
            <p>'.__('Enter currency code','multipay').': <input type="text" style="width:3em" label="new_curr" name="new_curr" value="'.$new_curr.'" />&nbsp;('.__('For example: GBP, USD, EUR','multipay').')</p>
            <p><span style="color:red; font-weight: bold; margin-right: 3px">'.__('Important','multipay').'!</span> '.__('If your currency is not correct the plugin will work but the merchant will not accept the payment.','multipay').'</p>
            <input type="hidden" name="alternative" value="' . $qp_setup['alternative'] . '" />
            <p>'.__('Copy settings from an exisiting form.','multipay').'</p>
            <select name="qp_clone"><option>'.__('Do not copy settings','multipay').'</option>';
            foreach ($arr as $item) {
                $content .= '<option value="'.$item.'">'.$item.'</option>';
            }
            $content .= '</select>
            <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Update Settings','multipay').'" /> <input type="submit" name="Reset" class="button-secondary" value="'.__('Reset Everything','multipay').'" onclick="return window.confirm( \'This will delete all your forms and settings.\nAre you sure you want to reset everything?\' );"/></p>';
            $content .= wp_nonce_field("save_qp");
            $content .= '</form>';
        }
        $content .= '</div></div>';
        echo $content;
    }

    // Clone the Form
    function form_clone ($form,$clone) {
        $update = qp_get_stored_options ($clone);update_option( 'qp_options'.$form, $update );
        $update = qp_get_stored_send ($clone);update_option( 'qp_send'.$form, $update );
        $update = qp_get_stored_coupon ($clone);update_option( 'qp_coupon'.$form, $update );
        $update = qp_get_stored_address ($clone);update_option( 'qp_address'.$form, $update );
        $update = qp_get_stored_autoresponder ($clone);update_option( 'qp_autoresponder'.$form, $update );
    }

    // Form Settings
    function form_options($form) {
        self::change_form_update($form);
        $qppkey = get_option('qpp_key');
        if( isset( $_POST['qp_submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'title',
                'blurb',
                'sort',
                'inputreference',
                'fixedreference',
                'refselector',
                'inputamount',
                'allow_amount',
                'fixedamount',
                'amtselector',
                'combobox',
                'comboboxword',
                'comboboxlabel',
                'use_currency',
                'currencylabel',
                'use_quantity',
                'quantitylabel',
                'quantitymax',
                'quantitymaxblurb',
                'use_stock',
                'ruse_stock',
                'fixedstock',
                'stocklabel',
                'use_options',
                'ruse_options',
                'optionlabel',
                'optionvalues',
                'optionselector',
                'inline_options',
                'use_process',
                'processblurb',
                'processref',
                'processtype',
                'processpercent',
                'processfixed',
                'use_postage',
                'postageblurb',
                'postageref',
                'postagetype',
                'postagepercent',
                'postagefixed',
                'use_blurb',
                'extrablurb',
                'use_address',
                'addressblurb',
                'use_email',
                'ruse_email',
                'emailblurb',
                'use_message',
                'ruse_message',
                'messagelabel',
                'use_terms',
                'termsblurb',
                'termsurl',
                'termspage',
                'captcha',
                'mathscaption',
                'use_reset',
                'resetcaption',
            );
            foreach ($options as $item) {
                $qp[$item] = stripslashes( $_POST[$item]);
                $qp[$item] = filter_var($qp[$item],FILTER_SANITIZE_STRING);
            }
            if ($qppkey['authorised']) {
                $options = array(
                    'use_coupon',
                    'couponblurb',
                    'couponref',
                    'couponbutton',
                    'use_totals',
                    'totalsblurb',
                    'use_datepicker',
                    'ruse_datepicker',
                    'datepickerblurb',
                    'use_slider',
                    'sliderlabel',
                    'min',
                    'max',
                    'initial',
                    'step'
                );
                foreach ($options as $item) {
                    $qp[$item] = stripslashes( $_POST[$item]);
                    $qp[$item] = filter_var($qp[$item],FILTER_SANITIZE_STRING);
                }
            }

            update_option('qp_options'.$form, $qp);
            qp_admin_notice(__('The form and submission settings have been updated','multipay'));
        }
        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            delete_option('qp_options'.$form);
            qp_admin_notice(__('The form and submission settings have been reset','multipay'));
        }
        $qp_setup = qp_get_stored_setup();
        $form=$qp_setup['current'];
        $currency = qp_get_stored_curr();
        $refradio=$refdropdown=$radio=$dropdown=$optionsradio=$optionsdropdown=$processpercent=$postagepercent=$comma=$D=$W=$Y='';
        $qp = qp_get_stored_options($form);
        $$qp['processtype'] = 'checked';
        $$qp['postagetype'] = 'checked';
        $$qp['coupontype'] = 'checked';
        $$qp['refselector'] = 'checked';
        $$qp['amtselector'] = 'checked';
        $$qp['optionselector'] = 'checked';
        $content = self::head_css();
        $content .= '<script>
        jQuery(function() {var qp_sort = jQuery( "#qp_sort" ).sortable({axis: "y",update:function(e,ui) {var order = qp_sort.sortable("toArray").join();jQuery("#qp_settings_sort").val(order);}});});
        </script>';
        $content .='<div class="qp-settings"><div class="qp-options">
        <h2>'.__('Form Settings','multipay').'</h2>';
        $content .= self::change_form($qp_setup);
        $content .= '<form action="" method="POST">
        <p>'.__('Form heading (optional)','multipay').'</p>
        <p><input type="text" style="width:100%" name="title" value="' . $qp['title'] . '" /></p>
        <p>'.__('This is the blurb that will appear below the heading and above the form:','multipay').'</p>
        <p><input type="text" style="width:100%" name="blurb" value="' . $qp['blurb'] . '" /></p>
        <h2>'.__('Form Fields','multipay').'</h2>
        <p>'.__('Drag and drop to change order of the fields','multipay').'</p>
        <table id="sorting">
        <thead>
        <tr>
        <th width="5%">U</th>
        <th width="5%">R</th>
        <th width="20%">'.__('Form Field', 'multipay').'</th>
        <th width="70%">'.__('Labels and Options', 'multipay').'</th>
        </tr>
        </thead>
        <tbody id="qp_sort">';
        foreach (explode( ',',$qp['sort']) as $name) {
            switch ( $name ) {
                case 'reference':
                $check = '&nbsp;';
                $type = 'Reference';
                $input = 'inputreference';
                $checked = 'checked';
                $required = '';
                $refselector = $qp['refselector'];
                $options = '<p><input type="checkbox" name="fixedreference" ' . $qp['fixedreference'] . ' value="checked" />&nbsp;Display as a pre-set reference</p>
                <p class="description">Use commas to seperate options: Red,Green, Blue</p>
                <p class="description">Use semi-colons to combine with amount: Red;$5,Green;$10,Blue;£20</span></p>
                <p>Options Selector: <input type="radio" name="refselector" value="refradio" ' . (($refselector == 'refradio') ? 'checked' : '') . ' /> Radio <input type="radio" name="refselector" value="refdropdown" ' . (($refselector == 'refdropdown') ? 'checked' : '') . ' /> Dropdown</p>';
                break;
                case 'amount': 
                $check = '&nbsp;';
                $type = 'Amount';
                $input = 'inputamount';
                $checked = 'checked';
                $required = '';
                $amtselector = $qp['amtselector'];
                $options = '<p><input type="checkbox" name="allow_amount" ' . $qp['allow_amount'] . ' value="checked" /> Do not validate (use default amount value)</p>
                <p><input type="checkbox" name="fixedamount" ' . $qp['fixedamount'] . ' value="checked" /> Display as a pre-set amount</p>
                <p class="description">Use commas to create an options list: £10,£20,£30</p>
                <p>Options Selector: <input type="radio" name="amtselector" value="amtradio" ' . (($amtselector == 'amtradio') ? 'checked' : '') . ' /> Radio <input type="radio" name="amtselector" value="amtdropdown" ' . (($amtselector == 'amtdropdown') ? 'checked' : '') . ' /> Dropdown</p>
                <p><input type="checkbox" name="combobox" ' . $qp['combobox'] . ' value="checked" /> Add input field to dropdown<br>
                Caption:&nbsp;<input type="text" style="width:7em;" name="comboboxword" value="' . $qp['comboboxword'] . '" /><br>
                Instruction:&nbsp;<input type="text" style="width:10em;" name="comboboxlabel" value="' . $qp['comboboxlabel'] . '" /></p>';
                break;
                case 'quantity': 
                $check = '<input type="checkbox"   name="use_quantity" ' . $qp['use_quantity'] . ' value="checked" />';
                $type = 'Quantity';
                $input = 'quantitylabel';
                $checked = $qp['use_quantity'];
                $required = '';
                $options = '<p><input type="checkbox" name="quantitymax" ' . $qp['quantitymax'] . ' value="checked" /> Display and validate a maximum quantity</p>
                <p class="description">Message that will display on the form:</p>
                <p><input type="text" name="quantitymaxblurb" value="' . $qp['quantitymaxblurb'] . '" /></p>
                <p class="description">E.g. "maxiumum of 10", where 10 is the maximum quantity</p>';
                break;
                case 'stock': 
                $check = '<input type="checkbox" name="use_stock" ' . $qp['use_stock'] . ' value="checked" />';
                $type = 'Item Number';
                $input = 'stocklabel';
                $checked = $qp['use_stock'];
                $required = '<input type="checkbox" name="ruse_stock" ' . $qp['ruse_stock'] . ' value="checked" />';
                $options = '<p><input type="checkbox" name="fixedstock" ' . $qp['fixedstock'] . ' value="checked" /> Display as a pre-set item number</p>';
                break;
                case 'options': 
                $check = '<input type="checkbox"   name="use_options" ' . $qp['use_options'] . ' value="checked" />';
                $type = 'Options';
                $input = 'optionlabel';
                $checked = $qp['use_options'];
                $required = '<input type="checkbox" name="ruse_options" ' . $qp['ruse_options'] . ' value="checked" />';
                $optionselector = $qp['optionselector'];
                $options = '<p class="description">Options (separate with a comma):</p>
                <p><textarea  name="optionvalues" label="Radio" rows="2">' . $qp['optionvalues'] . '</textarea></p>
                <p>Options Selector: <input type="radio" name="optionselector" value="optionsradio" ' . (($optionselector == 'optionsradio') ? 'checked' : '') . ' /> Radio <input type="radio" name="optionselector" value="optionscheckbox" ' . (($optionselector == 'optionscheckbox') ? 'checked' : '') . ' /> Checkbox <input type="radio" name="optionselector" value="optionsdropdown" ' . (($optionselector == 'optionsdropdown') ? 'checked' : '') . ' /> Dropdown</p>
                <p><input type="checkbox" name="inline_options" ' . $qp['inline_options'] . ' value="checked" />&nbsp;Display inline radio and checkbox fields</p>'; 
                break;
                case 'postage': 
                $check = '<input type="checkbox" name="use_postage" ' . $qp['use_postage'] . ' value="checked" />';
                $type = 'Postal charge';
                $input = 'postageblurb';
                $checked = $qp['use_postage'];
                $required = '';
                $postagetype = $qp['postagetype'];
                $options = '<p class="description">Post and Packing charge type:</p>
                <p><input type="radio" name="postagetype" value="postagepercent" ' . (($postagetype == 'postagepercent') ? 'checked' : '') . ' /> Percentage of the total: <input type="text" style="width:4em;padding:2px" label="postagepercent" name="postagepercent" value="' . $qp['postagepercent'] . '" /> %</p>
                <p><input type="radio" name="postagetype" value="postagefixed" ' . (($postagetype == 'postagefixed') ? 'checked' : '') . ' /> Fixed amount: <input type="text" style="width:4em;padding:2px" label="postagefixed" name="postagefixed" value="' . $qp['postagefixed'] . '" /> '.$currency[$form].'</p>'; 
                break;
                case 'processing': 
                $check = '<input type="checkbox" name="use_process" ' . $qp['use_process'] . ' value="checked" />';
                $type = 'Processing Charge';
                $input = 'processblurb';
                $checked = $qp['use_process'];
                $required = '';
                $processtype = $qp['processtype'];
                $options = '<p class="description">Payment charge type:</p>
                <p><input type="radio" name="processtype" value="processpercent" ' . (($processtype == 'processpercent') ? 'checked' : '') . ' /> Percentage of the total: <input type="text" style="width:4em;padding:2px" label="processpercent" name="processpercent" value="' . $qp['processpercent'] . '" /> %</p>
                <p><input type="radio" name="processtype" value="processfixed" ' . (($processtype == 'processfixed') ? 'checked' : '') . ' /> Fixed amount: <input type="text" style="width:4em;padding:2px" label="processfixed" name="processfixed" value="' . $qp['processfixed'] . '" /> '.$currency[$form].'</p>'; 
                break;
                case 'coupon': 
                if ($qppkey['authorised']) {
                    $check = '<input type="checkbox" name="use_coupon" ' . $qp['use_coupon'] . ' value="checked" />';
                    $type = 'Coupon Code';
                    $input = 'couponblurb';
                    $checked = $qp['use_coupon'];
                    $required = '';
                    $options = '<p class="description">Button label:</p>
                    <p><input type="text" name="couponbutton" value="' . $qp['couponbutton'] . '" /></p>
                    <p class="description">Coupon applied message:</p>
                    <p><input type="text" name="couponref" value="' . $qp['couponref'] . '" /></p>
                    <p><a href="admin.php?page=multipay-settings&tab=coupon">Set coupon codes</a></p>';
                } else {
                    $type = 'Coupons';
                    $input = '';
                    $options = '<p>Coupons are only available in the Pro Version</p>';
                    $check = '';
                    $required = '';
                    $checked = false;
                } 
                break;
                case 'additionalinfo': 
                $check = '<input type="checkbox" name="use_blurb" ' . $qp['use_blurb'] . ' value="checked" />';
                $type = 'Additional Information';
                $input = 'extrablurb';
                $checked = $qp['use_blurb'];
                $required = '';
                $options = '<p class="description">Add additional information to your form</p>';
                break;
                case 'address': 
                $check = '<input type="checkbox" name="use_address" ' . $qp['use_address'] . ' value="checked" />';
                $type = 'Personal Details';
                $input = 'addressblurb';
                $checked = $qp['use_address'];
                $options = '<p><a href="admin.php?page=multipay-settings&tab=address">'.__('Personal details Settings','multipay').'</a></p>';
                break;
                case 'slider';
                if ($qppkey['authorised']) {
                    $check = '<input type="checkbox" name="use_slider" ' . $qp['use_slider'] . ' value="checked" />';
                    $type = 'Range slider';
                    $input = 'sliderlabel';
                    $checked = $qp['use_slider'];
                    $options = '<p>The range slider replaces the amount field.</p>
                    <p><input type="text" style="border:1px solid #415063; width:3em;" name="min" . value ="' . $qp['min'] . '" />&nbsp;Minimum value<br>
                    <input type="text" style="border:1px solid #415063; width:3em;" name="max" . value ="' . $qp['max'] . '" />&nbsp;Maximum value<br>
                    <input type="text" style="border:1px solid #415063; width:3em;" name="initial" . value ="' . $qp['initial'] . '" />&nbsp;Initial value<br>
                    <input type="text" style="border:1px solid #415063; width:3em;" name="step" . value ="' . $qp['step'] . '" />&nbspStep</p>';
                } else {
                    $type = 'Range Slider';
                    $input = '';
                    $options = '<p>The rangeslider option is only available in the Pro Version</p>';
                    $check = '';
                    $required = '';
                    $checked = false;
                }
                break;
                case 'email': 
                $check = '<input type="checkbox" name="use_email" ' . $qp['use_email'] . ' value="checked" />';
                $type = 'Email Address';
                $input = 'emailblurb';
                $checked = $qp['use_email'];
                $required = '<input type="checkbox" name="ruse_email" ' . $qp['ruse_email'] . ' value="checked" />';
                $options = '<p class="description">Use this to collect the Payees email address.</p>';
                break;
                case 'message': 
                $check = '<input type="checkbox" name="use_message" ' . $qp['use_message'] . ' value="checked" />';
                $type = 'Add textbox for comments';
                $input = 'messagelabel';
                $checked = $qp['use_message'];
                $required = '<input type="checkbox" name="ruse_message" ' . $qp['ruse_message'] . ' value="checked" />';
                $options = '';
                break;
                case 'datepicker':
                if ($qppkey['authorised']) {
                    $check = '<input type="checkbox" name="use_datepicker" ' . $qp['use_datepicker'] . ' value="checked" />';
                    $type = 'Add datepicker';
                    $input = 'datepickerlabel';
                    $checked = $qp['use_datepicker'];
                    $required = '<input type="checkbox" name="ruse_datepicker" ' . $qp['ruse_datepicker'] . ' value="checked" />';
                    $options = '';
                } else {
                    $type = 'Add datepicker';
                    $input = '';
                    $options = '<p>The datepicker option is only available in the Pro Version</p>';
                    $check = '';
                    $required = '';
                    $checked = false;
                }   
                break;
                case 'terms': 
                $check = '<input type="checkbox" name="use_terms" ' . $qp['use_terms'] . ' value="checked" />';
                $type = 'Terms and Conditions';
                $input = 'termsblurb';
                $checked = $qp['use_terms'];
                $required = '';
                $options = '<p class="description">URL of Terms and Conditions:</p>
                <p><input type="text" name="termsurl" value="' . $qp['termsurl'] . '" /></p>
                <p><input type="checkbox" name="termspage" ' . $qp['termspage'] . ' value="checked" /> Open link in a new page</p>';
                break;
                case 'captcha': 
                $check = '<input type="checkbox"   name="captcha" ' . $qp['captcha'] . ' value="checked" />';
                $type = 'Maths Captcha';
                $input = 'mathscaption';
                $checked = $qp['captcha'];
                $options = '<p class="description">Add a maths checker to the form to (hopefully) block most of the spambots.</p>';
                break;
                case 'totals':
                if ($qppkey['authorised']) {
                    $check = '<input type="checkbox" name="use_totals" ' . $qp['use_totals'] . ' value="checked" />';
                    $type = 'Show totals';
                    $input = 'totalsblurb';
                    $checked = $qp['use_totals'];
                    $required = '';
                    $options = '<p class="description">Show live totals on your form.</p>';
                } else {
                    $type = 'Live totals';
                    $input = '';
                    $options = '<p>Live totals are only available in the Pro Version</p>';
                    $check = '';
                    $required = '';
                    $checked = false;
                } 
                break;
            }
            $li_class = ($checked) ? 'button_active' : 'button_inactive';	
            $content .='<tr class="'.$li_class.'" id="'.$name.'">
            <td>'.$check.'</td>
            <td>'.$required.'</td>
            <td>'.$type.'</td>
            <td>';
            if ($input) $content .='<input type="text" id="'.$name.'" name="'.$input.'" value="' . $qp[$input] . '" />';
            if ($options) $content .= $options;
            $content .='</td>
            </tr>';
        }
        $content .='</tbody></table>
        <h2>'.__('Reset button','multipay').'</h2>
        <p>'.__('<input type="checkbox" name="use_reset" ' . $qp['use_reset'] . ' value="checked" /> Show Reset Button','multipay').'</p>
        <input type="text" name="resetcaption" value="' . $qp['resetcaption'] . '" />
        <p>'.__('<input type="submit" name="qp_submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the form settings?\' );"/>','multipay').'</p>
        <input type="hidden" id="qp_settings_sort" name="sort" value="'.$qp['sort'].'" />';

        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div></div>';
        echo $content;
    }

    function checked($value, $eq = null) {
        return (($value && $eq === null) || $value == $eq) ? 'checked' : '';
    }

    // Styles
    function styles($form) {
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'font',
                'font-family',
                'font-size',
                'font-colour',
                'text-font-family',
                'text-font-size',
                'text-font-colour',
                'form-border',
                'input-border',
                'required-border',
                'error-colour',
                'border',
                'width',
                'widthtype',
                'background',
                'backgroundhex',
                'backgroundimage',
                'corners',
                'custom',
                'use_custom',
                'use_theme',
                'styles',
                'submit-colour',
                'submit-background',
                'submit-hover-background',
                'submit-border',
                'submitwidth',
                'submitwidthset',
                'submitposition',
                'coupon-colour',
                'coupon-background',
                'header-type',
                'header-size',
                'header-colour',
                'slider-background',
                'slider-revealed',
                'handle-background',
                'handle-border',
                'output-size',
                'output-colour',
                'slider-thickness',
                'handle-corners',
                'line_margin'
            );
            foreach ( $options as $item) {
                $style[$item] = stripslashes($_POST[$item]);
                $style[$item] = filter_var($style[$item],FILTER_SANITIZE_STRING);
            }
            update_option( 'qp_style', $style);
            qp_admin_notice(__('The form styles have been updated'.'multipay'));
        }
        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            delete_option('qp_style');
            qp_admin_notice(__('The form styles have been reset','multipay'));
        }
        $percent=$pixel=$none=$plain=$shadow=$roundshadow=$round=$white=$square=$theme=$submitrandom=$submitpixel=$submitright='';    
        $style = qp_get_stored_style();
        $$style['font'] = 'checked';
        $$style['widthtype'] = 'checked';
        $$style['submitwidth'] = 'checked';
        $$style['submitposition'] = 'checked';
        $$style['border'] = 'checked';
        $$style['background'] = 'checked';
        $$style['corners'] = 'checked';
        $$style['styles'] = 'checked';
        $$style['header-type'] = 'checked';

        $content = self::head_css();
        $content .= '<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('Styling','multipay').'</h2>';
        $qp = qp_get_stored_options($form);
        $content .= '<form method="post" action=""> 
        <table>
        <tr>
        <td colspan="2"><h2>'.__('Form Width','multipay').'</h2></td>
        </tr>
        <tr>
        <td colspan="2"><input type="radio" name="widthtype" value="percent" ' . $percent . ' '.self::checked($style['widthtype'], 'percent').' /> 100% (fill the available space)<br />
        <input type="radio" name="widthtype" value="pixel" ' . $pixel . ' '.self::checked($style['widthtype'], 'pixel').' /> Pixel (fixed): <input type="text" style="width:4em" label="width" name="width" value="' . $style['width'] . '" /> use px, em or %. Default is px.</td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Form Border','multipay').'</h2></td
        </tr>
        <tr>
        <td width="20%">Type:</td>
        <td><input type="radio" name="border" value="none" ' . $none . ' '.self::checked($style['border'], 'none').' /> No border<br />
        <input type="radio" name="border" value="plain" ' . $plain . ' '.self::checked($style['border'], 'plain').' /> Plain Border<br />
        <input type="radio" name="border" value="rounded" ' . $rounded . ' '.self::checked($style['border'], 'rounded').'  /> Round Corners (Not IE8)<br />
        <input type="radio" name="border" value="shadow" ' . $shadow . ' '.self::checked($style['border'], 'shadow').' /> Shadowed Border(Not IE8)<br />
        <input type="radio" name="border" value="roundshadow" ' . $roundshadow . ' '.self::checked($style['border'], 'roundshadow').' /> Rounded Shadowed Border (Not IE8)</td>
        </tr>
        <tr>
        <td>Style:</td>
        <td><input type="text" label="form-border" name="form-border" value="' . $style['form-border'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Background','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Colour:</td>
        <td><input type="radio" name="background" value="theme" ' . $theme . ' '.self::checked($style['background'], 'theme').' /> Same at theme<br />
        <input style="margin-bottom:5px;" type="radio" name="background" value="color" '.self::checked($style['background'], 'color').' ' . $color . ' />
        <input type="text" class="qp-color" label="background" name="backgroundhex" value="' . $style['backgroundhex'] . '" /></td>
        </tr>
        <tr><td>Background<br>Image:</td>
        <td>
        <input id="qp_background_image" type="text" name="backgroundimage" value="' . $style['backgroundimage'] . '" />
        <input id="qp_upload_background_image" class="button" type="button" value="Upload Image" /></td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Form Header','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Header Size:</td>
        <td><input type="text" style="width:6em" label="header-size" name="header-size" value="' . $style['header-size'] . '" /></td>
        </tr>
        <tr><td>Header Colour:</td>
        <td><input type="text" class="qp-color" label="header-colour" name="header-colour" value="' . $style['header-colour'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Input fields','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Font Family: </td>
        <td><input type="text" label="font-family" name="font-family" value="' . $style['font-family'] . '" /></td>
        </tr>
        <tr>
        <td>Font Size: </td>
        <td><input type="text" label="font-size" style="width:6em" name="font-size" value="' . $style['font-size'] . '" /></td>
        </tr>
        <tr>
        <td>Font Colour: </td>
        <td><input type="text" class="qp-color" label="font-colour" name="font-colour" value="' . $style['font-colour'] . '" /></td
        </tr>
        <tr>
        <td>Normal Border: </td>
        <td><input type="text" label="input-border" name="input-border" value="' . $style['input-border'] . '" /></td>
        </tr>
        <tr>
        <td>Required Border: </td>
        <td><input type="text" name="required-border" value="' . $style['required-border'] . '" /></td>
        </tr>
        <tr>
        <td>Error Colour: </td>
        <td><input type="text" class="qp-color" name="error-colour" value="' . $style['error-colour'] . '" /></td>
        </tr>
        <tr>
        <td>Corners: </td>
        <td><input type="radio" name="corners" value="square" ' . $square . ' '.self::checked($style['corners'], 'square').' /> Square corners<br />
        <input type="radio" name="corners" value="round" ' . $round . ' '.self::checked($style['corners'], 'round').' /> Rounded corners</td></tr>
        <tr>
        <td style="vertical-align:top;">'.__('Margins and Padding', 'multipay').'</td>
        <td><span class="description">'.__('Set the margins and padding of each bit using CSS shortcodes', 'multipay').':</span><br><input type="text" label="line margin" name="line_margin" value="' . $style['line_margin'] . '" /></td>
        </tr>
        <tr>';
        if ($qp['use_coupon']) $content .= '<td colspan="2"><h2>'.__('Apply Coupon Button','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Font Colour: </td>
        <td><input type="text" class="qp-color" label="coupon-colour" name="coupon-colour" value="' . $style['coupon-colour'] . '" /></td>
        </tr>
        <tr>
        <td>Background: </td>
        <td><input type="text" class="qp-color" label="coupon-background" name="coupon-background" value="' . $style['coupon-background'] . '" /><br>Other settings are the same as the Submit Button</td>
        </tr>';		
        $content .= '<tr>
        <td colspan="2"><h2>'.__('Other text content','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Font Family: </td>
        <td><input type="text" label="text-font-family" name="text-font-family" value="' . $style['text-font-family'] . '" /></td>
        </tr>
        <tr>
        <td>Font Size: </td>
        <td><input type="text" style="width:6em" label="text-font-size" name="text-font-size" value="' . $style['text-font-size'] . '" /></td>
        </tr>
        <tr>
        <td>Font Colour: </td>
        <td><input type="text" class="qp-color" label="text-font-colour" name="text-font-colour" value="' . $style['text-font-colour'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Submit Button','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Font Colour:</td>
        <td><input type="text" class="qp-color" label="submit-colour" name="submit-colour" value="' . $style['submit-colour'] . '" /></td></tr>
        <tr>
        <td>Background:</td>
        <td><input type="text" class="qp-color" label="submit-background" name="submit-background" value="' . $style['submit-background'] . '" /></td>
        </tr>
        <tr>
        <td>Hover: </td>
        <td><input type="text" class="qp-color" label="submit-hover-background" name="submit-hover-background" value="' . $style['submit-hover-background'] . '" /></td>
        </tr>
        <tr>
        <td>Border:</td>
        <td><input type="text" label="submit-border" name="submit-border" value="' . $style['submit-border'] . '" /></td></tr>
        <tr>
        <td>Size:</td>
        <td><input type="radio" name="submitwidth" value="submitpercent" ' . $submitpercent . ' '.self::checked($style['submitwidth'], 'submitpercent').' /> Same width as the form<br />
        <input type="radio" name="submitwidth" value="submitrandom" ' . $submitrandom . ' '.self::checked($style['submitwidth'], 'submitrandom').' /> Same width as the button text</td></tr>
        <tr>
        <td>Position:</td>
        <td><input type="radio" name="submitposition" value="submitleft" ' . $submitleft . ' '.self::checked($style['submitposition'], 'submitleft').' /> Left <input type="radio" name="submitposition" value="submitmiddle" ' . $submitmiddle . ' '.self::checked($style['submitposition'], 'submitmiddle').' /> Centre <input type="radio" name="submitposition" value="submitright" ' . $submitright . ' '.self::checked($style['submitposition'], 'submitright').' /> Right</td>
        </tr>';
        if ($qp['use_slider']) $content .= '<tr>
        <td colspan="2"><h2>'.__('Slider','multipay').'</h2></td>
        </tr>
        <tr>
        <td>Thickness</td>
        <td><input type="text" style="width:3em" label="input-border" name="slider-thickness" value="' . $style['slider-thickness'] . '" />em</td>
        </tr>
        <tr>
        <td>Normal Background</td>
        <td><input type="text" class="qp-color" label="input-border" name="slider-background" value="' . $style['slider-background'] . '" /></td>
        </tr>
        <tr>
        <td>Revealed Background</td>
        <td><input type="text" class="qp-color" label="input-border" name="slider-revealed" value="' . $style['slider-revealed'] . '" /></td>
        </tr>
        <tr>
        <td>Handle Background</td>
        <td><input type="text" class="qp-color" label="input-border" name="handle-background" value="' . $style['handle-background'] . '" /></td>
        </tr>
        <tr>
        <td>Handle Border</td>
        <td><input type="text" class="qp-color" label="input-border" name="handle-border" value="' . $style['handle-border'] . '" /></td>
        </tr>
        <tr>
        <td>Corners</td>
        <td><input type="text" style="width:2em" name="handle-corners" value="' . $style['handle-corners'] . '" />&nbsp;%</td>
        </tr>
        <tr>
        <td>Output Size</td>
        <td><input type="text" style="width:5em" label="input-border" name="output-size" value="' . $style['output-size'] . '" /></td>
        </tr>
        <tr>
        <td>Output Colour</td>
        <td><input type="text" class="qp-color" label="input-border" name="output-colour" value="' . $style['output-colour'] . '" /></td>
        </tr>';
        $content .= '</table>
        <p>'.__('<input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the form styles?\' );"/>','multipay').'</p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div></div>';
        echo $content;
    }

    // Processing Settings and Messages
    function send_page($form) {
        self::change_form_update($form);
        $default_options = qp_get_send_defaults();
        $existing_options = get_option('qp_send'.$form);
        $processing_options = array(
            'customurl',
            'cancelurl',
            'thanksurl',
            'combine',
            'mailchimpregion',
            'mailchimpuser',
            'mailchimpid',
        );
        $messages_options = array(
            'errortitle',
            'errorblurb',
            'validating',
            'waiting',
            'failuretitle',
            'failureblurb',
            'failureanchor',
            'pendingtitle',
            'pendingblurb',
            'pendinganchor',
            'confirmationtitle',
            'confirmationblurb',
            'confirmationanchor',
        );
        
        // Processing update
        if( isset( $_POST['processing_submit']) && check_admin_referer("processing_save")) {
            foreach ($processing_options as $item) {
                $send[$item] = stripslashes( $_POST[$item]);
                $send[$item] = filter_var($send[$item],FILTER_SANITIZE_STRING);
            }
            update_option('qp_send'.$form,  wp_parse_args( $send, $existing_options ));
            qp_admin_notice(__('The processing settings have been updated','multipay'));
        }
        
        // Messages update
        if( isset( $_POST['messages_submit']) && check_admin_referer("messages_save")) {
            foreach ($messages_options as $item) {
                $send[$item] = stripslashes( $_POST[$item]);
                $send[$item] = filter_var($send[$item],FILTER_SANITIZE_STRING);
            }
            update_option('qp_send'.$form,  wp_parse_args( $send, $existing_options ));
            qp_admin_notice(__('The messages settings have been updated','multipay'));
        }
        
        // Reset processing options
        if( isset( $_POST['processing_reset']) && check_admin_referer("processing_save")) {
            $reset_options = array();
            foreach ($processing_options as $item) {
                $reset_options[$item] = $default_options[$item];
            }
            update_option('qp_send'.$form,  wp_parse_args( $reset_options, $existing_options ));
            qp_admin_notice(__('The processing settings have been reset','multipay'));
        }
        
        // Reset messages options
        if( isset( $_POST['messages_reset']) && check_admin_referer("messages_save")) {
            $reset_options = array();
            foreach ($messages_options as $item) {
                $reset_options[$item] = $default_options[$item];
            }
            update_option('qp_send'.$form,  wp_parse_args( $reset_options, $existing_options ));
            qp_admin_notice(__('The processing settings have been reset','multipay'));
        }
        
        $qp_setup = qp_get_stored_setup();
        $form = $qp_setup['current'];
        $newpage = $customurl = '';
        $send = qp_get_stored_send($form);
        $$send['target'] = 'checked';
        $$send['lc'] = 'selected';
        if (empty($send['confirmemail'])) {
            $send['confirmemail'] = get_bloginfo('admin_email');
        }

        $content = self::head_css();
        $content .= self::change_form($qp_setup);
        $content .= '<div class="qp-settings"><div class="qp-options"><form action="" method="POST">
        <h2 style="color:#B52C00">'.__('Processing','multipay').'</h2>';
        $content .= '<h2>'.__('Cancel and Thank you pages','multipay').'</h2>
        <p>'.__('If you leave these blank the merchant will return the user to the current page.','multipay').'</p>
        <p>'.__('URL of cancellation page','multipay').'</p>
        <input type="text" style="width:100%" name="cancelurl" value="' . $send['cancelurl'] . '" />
        <p>'.__('URL of thank you page','multipay').'</p>
        <input type="text" style="width:100%" name="thanksurl" value="' . $send['thanksurl'] . '" />

        <h2>'.__('Add to Mailchimp','multipay').'</h2>
        <p>'.__('This will only work if you are collecting names and email addresses','multipay').'</p>
        <p>'.__('Your Mailchimp Region:
        <input type="text" style="width:100%" name="mailchimpregion" value="' . $send['mailchimpregion'] . '" />','multipay').'</p>
        <p>'.__('Your Mailchimp User ID:
        <input type="text" style="width:100%" name="mailchimpuser" value="' . $send['mailchimpuser'] . '" />','multipay').'</p>
        <p>'.__('The Mailchimp List ID:
        <input type="text" style="width:100%" name="mailchimpid" value="' . $send['mailchimpid'] . '" />','multipay').'</p>
        <p><a href="https://wordpress.org/plugins/multipay/#faq" target="_blank">'.__("Visit the FAQ to find out what to put here",'multipay').'</a></p>
        <p>'.__('<input type="submit" name="processing_submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="processing_reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the processing fields?\' );"/>','multipay').'</p>';
        $content .= wp_nonce_field("processing_save");
        $content .= '</form></div>';
        $content .= '<div class="qp-options"><form action="" method="POST">
        <h2 style="color:#B52C00">'.__('Error and Validation Messages','multipay').'</h2>
        <table>
        <tr>
        <td colspan="2"><h2>'.__('Form Error Messages', 'multipay').'</h2></td>
        </tr>
        <tr>
        <td width="40%">Error header</td>
        <td><input type="text"  style="width:100%" name="errortitle" value="' . $send['errortitle'] . '" /></td>
        </tr>
        <tr>
        <td>Error message</td>
        <td><input type="text" style="width:100%" name="errorblurb" value="' . $send['errorblurb'] . '" /></td>
        </tr>
        <tr>
        <td colspan="2"><h2>'.__('Validation Messages', 'multipay').'</h2></td>
        </tr>
        <tr>
        <td>'.__('Validating', 'multipay').'</td>
        <td><input type="text" style="" name="validating" value="' . $send['validating'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Waiting', 'multipay').'</td>
        <td><input type="text" style="" name="waiting" value="' . $send['waiting'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Cancel and Return Title', 'multipay').'</td>
        <td><input type="text" style="" name="failuretitle" value="' . $send['failuretitle'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Cancel and Return Blurb', 'multipay').'</td>
        <td><input type="text" style="" name="failureblurb" value="' . $send['failureblurb'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Try Again Anchor', 'multipay').'</td>
        <td><input type="text" style="" name="failureanchor" value="' . $send['failureanchor'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Pending Title', 'multipay').'</td>
        <td><input type="text" style="" name="pendingtitle" value="' . $send['pendingtitle'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Pending Blurb', 'multipay').'</td>
        <td><input type="text" style="" name="pendingblurb" value="' . $send['pendingblurb'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Refresh Anchor', 'multipay').'</td>
        <td><input type="text" style="" name="pendinganchor" value="' . $send['pendinganchor'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Payment Confirmation Title', 'multipay').'</td>
        <td><input type="text" style="" name="confirmationtitle" value="' . $send['confirmationtitle'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Payment Confirmation Blurb', 'multipay').'</td>
        <td><input type="text" style="" name="confirmationblurb" value="' . $send['confirmationblurb'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('New Payment Anchor', 'multipay').'</td>
        <td><input type="text" style="" name="confirmationanchor" value="' . $send['confirmationanchor'] . '" /></td>
        </tr>
        </table>
        <p>'.__('<input type="submit" name="messages_submit" class="button-primary" style="color: #FFF;" value="Save Changes" /> <input type="submit" name="messages_reset" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the error messages?\' );"/>','multipay').'</p>
        </div></div>';
        $content .= wp_nonce_field("messages_save");
        $content .= '</form>';

        echo $content;
    }

    // Autorespinder Settings
    function autoresponce_page($form) {
        self::change_form_update();
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'use_autoresponder',
                'confirmemail',
                'subject',
                'message',
                'paymentdetails',
                'fromname',
                'fromemail'
            );
            foreach ( $options as $item) {
                $auto[$item] = stripslashes($_POST[$item]);
            }
            update_option( 'qp_autoresponder'.$form, $auto );
            qp_admin_notice(__('The autoresponder settings have been updated','multipay'));
        }
        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            delete_option('qp_autoresponder'.$form);
            qp_admin_notice(__('The autoresponder settings have been reset','multipay'));
        }

        $qp_setup = qp_get_stored_setup();
        $form = $qp_setup['current'];
        $qp = qp_get_stored_options($form);
        $auto = qp_get_stored_autoresponder($form);

        $qppkey = get_option('qpp_key');
        $message = $auto['message'];
        $content ='<div class="qp-settings"><div class="qp-options" style="width:90%;">
        <h2 style="color:#B52C00">'.__('Confirmation Email and Autoresponder','multipay').'</h2>';
        $content .= self::change_form($qp_setup);
        $content .='<form method="post" action="">
        <h2>'.__('Confirmation Message','multipay').'</h2>
        <p>'.__('Enter the email address you where you want to recieve the order details after payment:','multipay').'</p>
        <p>'.__('<input type="text" style="width:100%" name="confirmemail" value="' . $auto['confirmemail'] . '" />','multipay').'</p>
        <p>'.__('<span class="description">Defaults to your <a href="'. get_admin_url().'options-general.php">admin email</a> if left blank.</span>','multipay').'</p>
        <h2>'.__('Autoresponder Settings','multipay').'</h2>';
        echo $content;
        if ($qppkey['authorised']) {
            echo '<p class="description">Note: The autoresponder only works if you collect an email address on the <a href="admin.php?page=multipay-settings&tab=settings">'.__('Form Settings</a>.','multipay').'</p>
            <p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="use_autoresponder"' . $auto['use_autoresponder'] . ' value="checked" /> '.__('Use Autoresponder.','multipay').'</p> 
            <p>'.__('From Name','multipay').' (<span class="description">Defaults to your <a href="'. get_admin_url().'options-general.php">Site Title</a> if left blank.</span>):<br>
            <input type="text" style="width:50%" name="fromname" value="' . $auto['fromname'] . '" /></p>
            <p>'.__('From Email','multipay').' (<span class="description">'.__('Defaults to your ','multipay').'<a href="'. get_admin_url().'options-general.php">'.__('admin email','multipay').'</a> '.__('if left blank','multipay').'.</span>):<br>
            <input type="text" style="width:50%" name="fromemail" value="' . $auto['fromemail'] . '" /></p>
            <p>'.__('Subject','multipay').'</p>
            <input style="width:100%" type="text" name="subject" value="' . $auto['subject'] . '"/><br>
            <p>'.__('Message Content','multipay').'</p>';
            wp_editor($message, 'message', $settings = array('textarea_rows' => '20','wpautop'=>false));
            echo '<p>'.__('You can use the following shortcodes in the message body:','multipay').'</p>
        <table>
        <tr>
        <th>Shortcode</th>
        <th>Replacement Text</th>
        </tr>
        <tr>
        <td>[firstname]</td>
        <td>The registrants first name if you are using the <a href="admin.php?page=multipay-settings&tab=address">personal details</a> option.</td>
        </tr>
        <tr>
        <td>[name]</td>
        <td>The registrants first and last name if you are using the <a href="admin.php?page=multipay-settings&tab=address">personal details</a> option.</td>
        </tr>
        <tr>
        <td>[reference]</td>
        <td>The name of the item being purchased</td>
        </tr>
        <tr>
        <td>[amount]</td>
        <td>The total amount to be paid without the currency symbol</td>
        </tr>
        <tr>
        <td>[fullamount]</td>
        <td>The total amount to be paid with currency symbol</td>
        </tr>
        <tr>
        <td>[quantity]</td>
        <td>The number of items purchased</td>
        </tr>
        <tr>
        <td>[option]</td>
        <td>The option selected</td>
        </tr>
        <tr>
        <td>[stock]</td>
        <td>The stock, SKU or item number</td>
        </tr>
        <tr>
        <td>[details]</td>
        <td>The payment information (reference, quantity, options, stock number, amount)</td>
        </tr>
        </table>
        <p><input type="checkbox" style="margin: 0; padding: 0; border: none;" name="paymentdetails"' . $auto['paymentdetails'] . ' value="checked" /> '.__('Add payment details to the message','multipay').'</p>';
        } else {
            echo '<p>'.__('The use the autoresponder','multipay').' <a href="admin.php?page=multipay-settings&tab=upgrade">'.__('Upgrade to Pro','multipay').'</a>. '.__('It\'s only $10','multipay').'.</p>
            <p>'.__('Your buyers will still receive an email from the payment gateway provider (if applicable). The autoresponder give you the opportunity to send a personalised message to the buyer along with their order details.','multipay').'</p> ';
        }
        echo '<p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes','multipay').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset','multipay').'" onclick="return window.confirm( \'Are you sure you want to reset the autoresponder?\' );"/></p>';
        $content = wp_nonce_field("save_qp");
        $content .= '</form>
        </div>
        </div>';
        echo $content;
    }

    // Personal Details
    function address($form) {
        self::change_form_update();
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'useaddress',
                'firstname',
                'lastname',
                'email',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'country',
                'night_phone_b',
                'rfirstname',
                'rlastname',
                'remail',
                'raddress1',
                'raddress2',
                'rcity',
                'rstate',
                'rzip',
                'rcountry',
                'rnight_phone_b'
            );
            foreach ( $options as $item) {
                $address[$item] = stripslashes($_POST[$item]);
                $address[$item] = filter_var($address[$item],FILTER_SANITIZE_STRING);
            }
            update_option( 'qp_address'.$form, $address );
            qp_admin_notice(__('The form settings have been updated','multipay'));
        }
        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            delete_option('qp_error'.$form);
            qp_admin_notice(__('The form settings have been reset','multipay'));
        }
        $qp_setup = qp_get_stored_setup();
        $form=$qp_setup['current'];
        $address = qp_get_stored_address($form);
        $content ='<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('Personal Information Fields','multipay').'</h2>';
        $content .= self::change_form($qp_setup);
        $content .= '<form method="post" action="">
        <p class="description">'.__('Note: The information will be collected and saved and passed to PayPal but usage is dependant on browser and user settings. Which means they may have to fill in the information again when they get to PayPal','multipay').'</p>
        <p>'.__('1. Delete labels for fields you do not want to use.','multipay').'</p>
        <p>'.__('2. Check the <b>R</b> box for madatory/required fields.','multipay').'</p>
        <table>
        <tr>

        <th>Field</th>
        <th>Label</th>
        <th>R</th>
        </tr>
        <tr>

        <td width="20%">First Name</td>
        <td><input type="text"  style="width:100%" name="firstname" value="' . $address['firstname'] . '" /></td>
        <td width="5%"><input type="checkbox" name="rfirstname" ' . $address['rfirstname'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Last Name</td>
        <td><input type="text"  style="width:100%" name="lastname" value="' . $address['lastname'] . '" /></td>
        <td><input type="checkbox" name="rlastname" ' . $address['rlastname'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Email</td>
        <td><input type="text" style="width:100%" name="email" value="' . $address['email'] . '" /></td>
        <td><input type="checkbox" name="remail" ' . $address['remail'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Address Line 1</td>
        <td><input type="text" style="width:100%" name="address1" value="' . $address['address1'] . '" /></td>
        <td><input type="checkbox" name="raddress1" ' . $address['raddress1'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Address Line 2</td>
        <td><input type="text" style="width:100%" name="address2" value="' . $address['address2'] . '" /></td>
        <td><input type="checkbox" name="raddress2" ' . $address['raddress2'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>City</td>
        <td><input type="text" style="width:100%" name="city" value="' . $address['city'] . '" /></td>
        <td><input type="checkbox" name="rcity" ' . $address['rcity'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>State</td>
        <td><input type="text" style="width:100%" name="state" value="' . $address['state'] . '" /></td>
        <td><input type="checkbox" name="rstate" ' . $address['rstate'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Zip</td>
        <td><input type="text" style="width:100%" name="zip" value="' . $address['zip'] . '" /></td>
        <td><input type="checkbox" name="rzip" ' . $address['rzip'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Country</td>
        <td><input type="text" style="width:100%" name="country" value="' . $address['country'] . '" /></td>
        <td><input type="checkbox" name="rcountry" ' . $address['rcountry'] . ' value="checked" /></td>
        </tr>
        <tr>

        <td>Phone</td>
        <td><input type="text" style="width:100%" name="night_phone_b" value="' . $address['night_phone_b'] . '" /></td>
        <td><input type="checkbox" name="rnight_phone_b" ' . $address['rnight_phone_b'] . ' value="checked" /></td>
        </tr>
        </table>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes','multipay').'" /> <input type="submit" name="Reset" class="button-primary" style="color: #FFF;" value="'.__('Reset','multipay').'" onclick="return window.confirm( \'Are you sure you want to reset the error message?\' );"/></p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div></div>';
        echo $content;
    }

    // Set up coupon codes
    function coupon_codes($form) {
        self::change_form_update();
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $arr = array('couponnumber','couponget','duplicate','couponerror','couponexpired');
            foreach ($arr as $item) {
                $coupon[$item] = stripslashes($_POST[$item]);
                $coupon[$item] = filter_var($coupon[$item],FILTER_SANITIZE_STRING);
            }
            $options = array('code','coupontype','couponpercent','couponfixed','qty','expired');
            if ($coupon['couponnumber'] < 1) $coupon['couponnumber'] = 1;
            for ($i=1; $i<=$coupon['couponnumber']; $i++) {
                foreach ( $options as $item) $coupon[$item.$i] = stripslashes($_POST[$item.$i]);
                if ($coupon['qty'.$i] > 0) $coupon['expired'.$i] = false;
                if (!$coupon['coupontype'.$i]) $coupon['coupontype'.$i] = 'percent'.$i;
                if (!$coupon['couponpercent'.$i]) $coupon['couponpercent'.$i] = '10';
                if (!$coupon['couponfixed'.$i]) $coupon['couponfixed'.$i] = '5';
            }
            update_option( 'qp_coupon'.$form, $coupon );
            if ($coupon['duplicate']) {
                $qp_setup = qp_get_stored_setup();
                $arr = explode(",",$qp_setup['alternative']);
                foreach ($arr as $item) update_option( 'qp_coupon'.$item, $coupon );
            }
            qp_admin_notice(__('The coupon settings have been updated','multipay'));
        }
        if( isset( $_POST['Reset']) && check_admin_referer("save_qp")) {
            delete_option('qp_coupon'.$form);
            qp_admin_notice(__('The coupon settings have been reset','multipay'));
        }
        $qp_setup = qp_get_stored_setup();
        $form = $qp_setup['current'];
        $currency = qp_get_stored_curr();
        $before = array(
            'USD'=>'&#x24;',
            'CDN'=>'&#x24;',
            'EUR'=>'&euro;',
            'GBP'=>'&pound;',
            'JPY'=>'&yen;',
            'AUD'=>'&#x24;',
            'BRL'=>'R&#x24;',
            'HKD'=>'&#x24;',
            'ILS'=>'&#x20aa;',
            'MXN'=>'&#x24;',
            'NZD'=>'&#x24;',
            'PHP'=>'&#8369;',
            'SGD'=>'&#x24;',
            'TWD'=>'NT&#x24;',
            'TRY'=>'&pound;');
        $after = array(
            'CZK'=>'K&#269;',
            'DKK'=>'Kr',
            'HUF'=>'Ft',
            'MYR'=>'RM',
            'NOK'=>'kr',
            'PLN'=>'z&#322',
            'RUB'=>'&#1056;&#1091;&#1073;',
            'SEK'=>'kr',
            'CHF'=>'CHF',
            'THB'=>'&#3647;');
        foreach($before as $item=>$key) {if ($item == $currency[$form]) $b = $key;}
        foreach($after as $item=>$key) {if ($item == $currency[$form]) $a = $key;}
        $coupon = qp_get_stored_coupon($form);
        $content ='<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('Coupon Codes','multipay').'</h2>';
        $content .= self::change_form($qp_setup);
        $content .= '<form method="post" action="">
        <p>'.__('Number of Coupons: <input type="text" name="couponnumber" value="'.$coupon['couponnumber'].'" style="width:4em">','multipay').'</p>
        <table>
        <tr><td>Coupon Code</td><td>Percentage</td><td>Fixed Amount</td><td>Qty</td></tr>';
        for ($i=1; $i<=$coupon['couponnumber']; $i++) {
            $percent = ($coupon['coupontype'.$i] == 'percent'.$i ? 'checked' : '');
            $fixed = ($coupon['coupontype'.$i] == 'fixed'.$i ? 'checked' : ''); 
            $content .= '<tr><td><input type="text" name="code'.$i.'" value="' . $coupon['code'.$i] . '" /></td>
            <td><input type="radio" name="coupontype'.$i.'" value="percent'.$i.'" ' . $percent . ' /> <input type="text" style="width:4em;padding:2px" label="couponpercent'.$i.'" name="couponpercent'.$i.'" value="' . $coupon['couponpercent'.$i] . '" /> %</td>
            <td><input type="radio" name="coupontype'.$i.'" value="fixed'.$i.'" ' . $fixed.' />&nbsp;'.$b.'&nbsp;<input type="text" style="width:4em;padding:2px" label="couponfixed'.$i.'" name="couponfixed'.$i.'" value="' . $coupon['couponfixed'.$i] . '" /> '.$a.'</td>
            <td><input type="text" style="width:3em;padding:2px" name="qty'.$i.'" value="' . $coupon['qty'.$i] . '" />
            <input type="hidden" name="expired'.$i.'" value="' . $coupon['expired'.$i] . '" /></td>
            </tr>';
        }
        $content .= '</table>
        <h2>'.__('Invalid Coupon Code Message','multipay').'</h2>
        <p><input id="couponerror" type="text" name="couponerror" value="' . $coupon['couponerror'] . '" /></p>
        <h2>'.__('Expired Coupon Message','multipay').'</h2>
        <p><input id="couponexpired" type="text" name="couponexpired" value="' . $coupon['couponexpired'] . '" /></p>
        <h2>'.__('Coupon Code Autofill','multipay').'</h2>
        <p>'.__('You can add coupon codes to URLs which will autofill the field. The URL format is: mysite.com/mypaymentpage/?coupon=code. The code you set will appear on the form with the following caption','multipay').':</p>
        <p><input id="couponget" type="text" name="couponget" value="' . $coupon['couponget'] . '" /></p>
        <h2>'.__('Clone Coupon Settings','multipay').'</h2>
        <p><input type="checkbox" name="duplicate" ' . $coupon['duplicate'] . ' value="checked" /> '.__('Duplicate coupon codes across all forms','multipay').'</p>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Save Changes','multipay').'" /> <input type="submit" name="'.__('Reset','multipay').'" class="button-primary" style="color: #FFF;" value="Reset" onclick="return window.confirm( \'Are you sure you want to reset the coupon codes?\' );"/></p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div>
        <div class="qp-options">
        <h2>'.__('Coupon Check','multipay').'</h2>
        <p>'.__('Test your coupon codes.','multipay').'</p>';
//        $args = array('form' => $form, 'id' => '', 'amount' => '');
//        $content .= qp_loop($args);
        $content .='</div></div>';
        echo $content;
    }

    // PayPal API Settings
    function paypal_api () {
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'use_paypal',
                'paypal_email',
                'paypal_submit',
                'merchantid',
                'api_username',
                'api_password',
                'api_key',
                'sandbox'
            );
            foreach ($options as $item) {
                $payment[$item] = stripslashes( $_POST[$item]);
                $payment[$item] = filter_var($payment[$item],FILTER_SANITIZE_STRING);
            }

            update_option('qp_paypal_api', $payment);
            qp_admin_notice(__('The API Settings have been updated', 'multipay'));
        }

        if( isset( $_POST['ResetAPI']) && check_admin_referer("save_qp")) {
            delete_option('qp_paypal_api');
            qp_admin_notice(__('The API settings have been reset', 'multipay'));
        }

        if( isset( $_POST['Import']) && check_admin_referer("save_qp")) {
            $setup = qpp_get_stored_setup();
            $ic = qpp_get_stored_incontext();
            $ic['use_paypal'] = $ic['useincontext'];
            $ic['paypal_email'] = $setup['email'];
            update_option('qp_paypal_api',$ic);
            qp_admin_notice(__('PayPal API Data imported','multipay'));
        }

        $payment = qp_get_paypal_api();
        $content = '<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('PayPal API Settings', 'multipay').'</h2>
        <form id="" method="post" action="">
        <table width="100%">
        <tr>
        <td colspan="2"><input type="checkbox" name="use_paypal" ' . $payment['use_paypal'] . ' value="checked" />&nbsp;'.__('Use PayPal.', 'multipay').'</td>
        </tr>
        <tr>
        <td width="30%">'.__('PayPal Email', 'multipay').'</td>
        <td><input type="text" style="" name="paypal_email" value="' . $payment['paypal_email'] . '" /></td>
        </tr>
        <tr>
        <td width="30%">'.__('Submit Caption', 'multipay').'</td>
        <td><input type="text" style="" name="paypal_submit" value="' . $payment['paypal_submit'] . '" /></td>
        </tr>
        <tr>
        <td width="30%">'.__('Merchant ID', 'multipay').'</td>
        <td><input type="text" style="" name="merchantid" value="' . $payment['merchantid'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('API Username', 'multipay').'</td>
        <td><input type="text" style="" name="api_username" value="' . $payment['api_username'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('API Password', 'multipay').'</td>
        <td><input type="text" style="" name="api_password" value="' . $payment['api_password'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('API Key (Signature)', 'multipay').'</td>
        <td><input type="text" style="" name="api_key" value="' . $payment['api_key'] . '" /></td>
        </tr>
        </table>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Update', 'multipay').'" />';
        if (function_exists('qpp_get_stored_incontext') && !$payment['paypal_email']) 
            $content .= '&nbsp;<input type="submit" name="Import" class="button-secondary" value="Import Data from QPP" />';
        else 
            $content .= '&nbsp;<input type="submit" name="ResetAPI" class="button-secondary" value="'.__('Reset API', 'multipay').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the API settings?', 'multipay').'\' );"/>';
        $content .= '</p>
        <p><input type="checkbox" name="sandbox" ' . $payment['sandbox'] . ' value="checked" /> '.__('Use Paypal sandbox (developer use only)','multipay').'</p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div>
        <div class="qp-options"><h2>'.__('How it works','multipay').'</h2>
        <p>'.__('You use this option you will need a PayPal account and the API details.','multipay').'</p>
        <ol>
        <li>'.__('Login to your PayPal business account (or create an account if you don\'t have one)','multipay').'</li>
        <li>'.__('Click on the Cog icon (Settings) on the top right of your screen','multipay').'</li>
        <li>'.__('You will find Merchant Account ID on this page','multipay').'</li>
        <li>'.__('Go to My Selling Preferences then the Update link on the API Access option','multipay').'</li>
        <li>'.__('Scroll down to "NVP/SOAP API integration (Classic)" click on "Manage API credentials"','multipay').'</li>
        <li>'.__('You will find your API Username, API Password and Signature on this page, click on "Show" for each to reveal them','multipay').'</li>
        <li>'.__('Copy the Merchant account ID and API details into the appropriate fields on the left','multipay').'</li>
        <li>'.__('Make sure the \'Use PayPal\' box is checked','multipay').'</li>
        <li>'.__('Update the settings','multipay').'</li>
        </ol>
        <p>'.__('When the form appears on your site there will be a','multipay') .' '. $payment['paypal_submit'] .__(' button.','multipay').'</p>
        <p>'.__('For more information on the PayPal In-Context Checkout visit the','multipay').' <a href="https://developer.paypal.com/docs/classic/express-checkout/in-context/" target="_blank">'.__('PayPal Developers Page','multipay').'</a>.</p>
        </div>
        </div>';
        echo $content;
    }

    // Amazon API Settings
    function amazon_api () {
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'use_amazon',
                'amazon_email',
                'amazon_submit',
                'sellerid',
                'accessKey',
                'secretKey',
                'clientID'
            );

            foreach ($options as $item) {
                $payment[$item] = stripslashes( $_POST[$item]);
                $payment[$item] = filter_var($payment[$item],FILTER_SANITIZE_STRING);
            }

            update_option('qp_amazon_api', $payment);
            qp_admin_notice(__('The API Settings have been updated', 'multipay'));
        }

        if( isset( $_POST['ResetAPI']) && check_admin_referer("save_qp")) {
            delete_option('qp_amazon_api');
            qp_admin_notice(__('The API settings have been reset', 'multipay'));
        }

        $payment = qp_get_amazon_api();
        $content = '<div class="qp-settings"><div class="qp-options">
        <h2 style="color:#B52C00">'.__('Amazon API Settings', 'multipay').'</h2>
        <p style="color:red">Amazon Payments only works in the USA. Support cannot be offered for this payment method and it will be deprecated in version 2.0.</p>
        <form id="" method="post" action="">
        <table width="100%">
        <tr>
        <td colspan="2"><input type="checkbox" name="use_amazon" ' . $payment['use_amazon'] . ' value="checked" />&nbsp;'.__('Use Amazon.', 'multipay').'</td>
        </tr>
        <tr>
        <td width="30%">'.__('Submit Caption', 'multipay').'</td>
        <td><input type="text" style="" name="amazon_submit" value="' . $payment['amazon_submit'] . '" /></td>
        </tr>
        <tr>
        <td width="30%">'.__('Merchant ID', 'multipay').'</td>
        <td><input type="text" style="" name="sellerid" value="' . $payment['sellerid'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Access Key', 'multipay').'</td>
        <td><input type="text" style="" name="accessKey" value="' . $payment['accessKey'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Secret Key', 'multipay').'</td>
        <td><input type="text" style="" name="secretKey" value="' . $payment['secretKey'] . '" /></td>
        </tr>
        <tr>
        <td>'.__('Client ID', 'multipay').'</td>
        <td><input type="text" style="" name="clientID" value="' . $payment['clientID'] . '" /></td>
        </tr>
        </table>
        <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Update', 'multipay').'" /> <input type="submit" name="ResetAPI" class="button-secondary" value="'.__('Reset API', 'multipay').'" onclick="return window.confirm( \''.__('Are you sure you want to reset the API settings?', 'multipay').'\' );"/></p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
        </div>
        <div class="qp-options"><h2>'.__('How it works','multipay').'</h2>
        <p>'.__('You use this option you will need a PayPal account and the API details.','multipay').'</p>
        <ol>
        <li>'.__('Login to your Amazon payment account (or create an account if you don\'t have one)','multipay').'</li>
        <li>'.__('Hover on the <b>Integration</b> link on the top left of your screen','multipay').'</li>
        <li>'.__('Select the <b>MWS Access Keys</b> option','multipay').'</li>
        <li>'.__('Click on <b>Secret Key</b> to show your key','multipay').'</li>
        <li>'.__('Copy the keys into the appropriate fields on the left','multipay').'</li>
        <li>'.__('Make sure the \'Use Amazon\' box is checked','multipay').'</li>
        <li>'.__('Update the settings','multipay').'</li>
        </ol>
        <p><b>Note:</b> You will also need to add an application. To do this select <b>Login with Amazon</b> form the dropdown top right and follow the on screen instructions. All you need is the name of your business, a descriotion and the URL of your Privacy page</p> 
        <p>'.__('When the form appears on your site there will be a','multipay') .' '. $payment['paypal_submit'] .__(' button.','multipay').'</p>
        </div>
        </div>';
        echo $content;
    }

    // Stripe API Settings
    function stripe_api () {
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'use_stripe',
                'stripe_submit',
                'secret_key',
                'publishable_key',
                'stripeimage',
            );
            foreach ($options as $item) {
                $payment[$item] = stripslashes( $_POST[$item]);
                $payment[$item] = filter_var($payment[$item],FILTER_SANITIZE_STRING);
            }
            update_option('qp_stripe_api', $payment);
            qp_admin_notice(__('The API Settings have been updated', 'multipay'));
        }

        if( isset( $_POST['ResetAPI']) && check_admin_referer("save_qp")) {
            delete_option('qp_stripe_api');
            qp_admin_notice(__('The API settings have been reset', 'multipay'));
        }

        $payment = qp_get_stripe_api();
        $content = '<style>
        .stripe-logo img{width: 64px;height: 64px;border-radius: 100%;}
        </style>
        <div class="qp-settings"><div class="qp-options">
            <h2 style="color:#B52C00">'.__('Stripe API Settings', 'multipay').'</h2>
            <form id="" method="post" action="">
            <table width="100%">
            <tr>
            <td colspan="2"><input type="checkbox" name="use_stripe" ' . $payment['use_stripe'] . ' value="checked" />&nbsp;'.__('Use Stripe.', 'multipay').'</td>
            </tr>
            <tr>
            <td width="30%">'.__('Submit Caption', 'multipay').'</td>
            <td><input type="text" style="" name="stripe_submit" value="' . $payment['stripe_submit'] . '" /></td>
            </tr>
            <tr>
            <td width="30%">'.__('Secret Key', 'multipay').'</td>
            <td><input type="text" style="" name="secret_key" value="' . $payment['secret_key'] . '" /></td>
            </tr>
            <tr>
            <td>'.__('Publishable Key', 'multipay').'</td>
            <td><input type="text" style="" name="publishable_key" value="' . $payment['publishable_key'] . '" /></td>
            </tr>
            <tr>
            <td>'.__('Logo','multipay').' ('.__('appears at top of the Stripe payment box','multipay').')</td>
            <td><input id="qp_stripe_image" type="text" name="stripeimage" value="' . $payment['stripeimage'] . '" />
            <input id="qp_upload_stripe_image" class="button" type="button" value="'.__('Upload Image','multipay').'" /></td>
            </tr>';
        if ($payment['stripeimage'])
            $content .= '<tr><td></td><td><div class="stripe-logo"><img src="'.$payment['stripeimage'].'"></div></td></tr>';
        $content .= '</table>
            <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Update', 'multipay').'" /> <input type="submit" name="ResetAPI" class="button-primary" style="color: #FFF;" value="'.__('Reset API', 'multipay').'" onclick="return window.confirm( \'Are you sure you want to reset the API settings?\' );"/></p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
            </div>
            <div class="qp-options"><h2>'.__('How it works','multipay').'</h2>
            <p>'.__('You use this option you will need a stripe account and the API keys.','multipay').'</p>
            <ol>
            <li>'.__('Login to your Stripe account (or create an account if you don\'t have one)','multipay').'</li>
            <li>'.__('In the top right corner click on your username','multipay').'</li>
            <li>'.__('Select <b>Account Settings</b> from the options','multipay').'</li>
            <li>'.__('Click on the API icon (third one along at the top of the window)','multipay').'</l1>
            <li>'.__('Copy and paste the Secret and Publishable keys into the appropriate fields on the left','multipay').'</li>
            <li>'.__('Make sure the <b>Use Stripe</b> box is checked and save the settings','multipay').'</li>
            <li>'.__('Update the settings','multipay').'</li>
            </ol>
            <p>'.__('When the form appears on your site there will be a','multipay') .' '. $payment['stripe_submit'] .__(' button.','multipay').'</p>
            </div>
            </div>';
        echo $content;
    }

    // WorldPay API Settings
    function worldpay_api () {
        if( isset( $_POST['Submit']) && check_admin_referer("save_qp")) {
            $options = array(
                'use_worldpay',
                'worldpay_submit',
                'client_key',
                'service_key',
            );
            foreach ($options as $item) {
                $payment[$item] = stripslashes( $_POST[$item]);
                $payment[$item] = filter_var($payment[$item],FILTER_SANITIZE_STRING);
            }
            update_option('qp_worldpay_api', $payment);
            qp_admin_notice(__('The API Settings have been updated', 'multipay'));
        }

        if( isset( $_POST['ResetAPI']) && check_admin_referer("save_qp")) {
            delete_option('qp_worldpay_api');
            qp_admin_notice(__('The API settings have been reset', 'multipay'));
        }

        $payment = qp_get_worldpay_api();
        $content = '<div class="qp-settings"><div class="qp-options">
            <h2 style="color:#B52C00">'.__('WorldPay API Settings', 'multipay').'</h2>
            <form id="" method="post" action="">
            <table width="100%">
            <tr>
            <td colspan="2"><input type="checkbox" name="use_worldpay" ' . $payment['use_worldpay'] . ' value="checked" />&nbsp;'.__('Use Worldpay.', 'multipay').'</td>
            </tr>
            <tr>
            <td width="30%">'.__('Submit Caption', 'multipay').'</td>
            <td><input type="text" style="" name="worldpay_submit" value="' . $payment['worldpay_submit'] . '" /></td>
            </tr>
            <tr>
            <td>'.__('Service Key', 'multipay').'</td>
            <td><input type="text" style="" name="service_key" value="' . $payment['service_key'] . '" /></td>
            </tr>
            <tr>
            <td width="30%">'.__('Client Key', 'multipay').'</td>
            <td><input type="text" style="" name="client_key" value="' . $payment['client_key'] . '" /></td>
            </tr>';
        $content .= '</table>
            <p><input type="submit" name="Submit" class="button-primary" style="color: #FFF;" value="'.__('Update', 'multipay').'" /> <input type="submit" name="ResetAPI" class="button-primary" style="color: #FFF;" value="'.__('Reset API', 'multipay').'" onclick="return window.confirm( \'Are you sure you want to reset the API settings?\' );"/></p>';
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>
            </div>
            <div class="qp-options"><h2>'.__('How it works','multipay').'</h2>
            <p>'.__('You use this option you will need a WorldPay account and the API keys.','multipay').'</p>
            <ol>
            <li>'.__('Login to your WorldPay account (or create an account if you don\'t have one)','multipay').'</li>
            <li>'.__('In the top right corner click on <b>Settings</b>','multipay').'</li>
            <li>'.__('Click on the <b>API Keys</b> link','multipay').'</l1>
            <li>'.__('Copy and paste the Service Key and Client Key into the appropriate fields on the left','multipay').'</li>
            <li>'.__('Make sure the \'Use WorldPay\' box is checked','multipay').'</li>
            <li>'.__('Update the settings','multipay').'</li>
            </ol>
            <p>'.__('When the form appears on your site there will be a','multipay') .' '. $payment['worldpay_submit'] .__(' button.','multipay').'</p>
            </div>
            </div>';
        echo $content;
    }

    // Upgrade
    function upgrade () {
        if( isset( $_POST['Upgrade']) && check_admin_referer("save_qp")) {
            $paypalurl = ($qp_setup['sandbox'] ? $paypalurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr');
            $page_url = qp_current_page_url();
            $page_url = (($ajaxurl == $page_url) ? $_SERVER['HTTP_REFERER'] : $page_url);
            $qppkey['key'] = md5(mt_rand());
            update_option('qpp_key', $qppkey);
            $form = '<h2>'.__('Waiting for PayPal...','multipay').'</h2><form action="'.$paypalurl.'" method="post" name="qpupgrade" id="qpupgrade">
            <input type="hidden" name="item_name" value="Multipay Plugin Upgrade"/>
            <input type="hidden" name="upload" value="1">
            <input type="hidden" name="business" value="hello@etalented.co.uk">
            <input type="hidden" name="cancel_return" value="'.$page_url.'">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="amount" value="10">
            <input type="hidden" name="notify_url" value = "'.site_url('/?qp_upgrade_ipn').'">
            <input type="hidden" name="custom" value="'.$qppkey['key'].'">
            </form>
            <script language="JavaScript">document.getElementById("qpupgrade").submit();</script>';
            echo $form;
        }

        if( isset( $_POST['Lost']) && check_admin_referer("save_qp")) {
            $email = get_option('admin_email');
            $qppkey = get_option('qpp_key');
            $headers = "From: Etalented Plugins <plugins@etalented.co.uk>\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: text/html; charset=\"utf-8\"\r\n";	
            $message = '<html><p>'.__('Your Multipay plugin authorisation key is:','multipay').'</p><p>'.$qppkey['key'].'</p></html>';
            wp_mail($email,'Multipay Plugin Authorisation Key',$message,$headers);
            qp_admin_notice(__('Your auth key has been sent to','multipay').' '.$email);
        }

        if( isset( $_POST['Check']) && check_admin_referer("save_qp")) {
            $qppkey = get_option('qpp_key');    
            if ($_POST['key'] == $qppkey['key'] || $_POST['key'] == 'jamsandwich' || $_POST['key'] == '2d1490348869720eb6c48469cce1d21c') {
                $qppkey['key'] = $_POST['key'];
                $qppkey['authorised'] = true;
                update_option('qpp_key', $qppkey);
                qp_admin_notice(__('Your key has been accepted','multipay'));
            } else {
                qp_admin_notice(__('The key is not correct, please try again','multipay'));
            }
        }

        if( isset( $_POST['Delete']) && check_admin_referer("save_qp")) {
            $qppkey = get_option('qpp_key');
            $qppkey['authorised'] = '';
            update_option('qpp_key',$qppkey);
            qp_admin_notice(__('Your key has been deleted','multipay'));
        }

        $qppkey = get_option('qpp_key');
        $content = '<form id="" method="post" action="">';
        if (!$qppkey['authorised']) {
            $content .= '<div class="qp-settings"><div class="qp-options" style="width:100%">
            <h2 style="color:#B52C00">'.__('Upgrading','multipay').'</h2>
            <p>'.__('The basic plugin will always be free and fully suported but upgrading gets you the following extra features:','multipay').'</p>
            <h2>'.__('Multiple forms','multipay').'</h2>
            <p>'.__('You have the option to create mutiple forms. This means you can have different forms on your site to pay for different products or services.','multipay').'</p>
            <h2>'.__('Additional form fields','multipay').'</h2>
            <p>'.__('There are additional form fields for: coupons, dates, sliders and live totals.','multipay').'</p>
            <h2>'.__('Autoresponder','multipay').'</h2>
            <p>'.__('If activated the autoresponder will send an email to the buyer once once payment is complete. You can personalise the autoresponder message usong a range of shortcodes.','multipay').'</p>
            <p>'.__('The confirmation email you receive also has tracking information.','multipay').'</p>
            <h2>'.__('Payment manager','multipay').'</h2>
            <p>'.__('The payment manager lists all pending and completed payments','multipay').', '.__('the merchant used to make the payment and the transaction number','multipay').'. '.__('There are options to delete single or multiple records and download all records.','multipay').'</p>
            <h2>'.__('All this for just $10','multipay').'</h2>
            <p>'.__('Click the button below to pay for your upgrade','multipay').'. '.__('Once payment has cleared upgrading is automatic','multipay').'. '.__('But if something does goes wrong, you will get an email with your authorisation key that you can enter below.','multipay').'<br><strong>'.__('Please note: upgrade will not work from an internal server or localhost - your website needs to be publically accessible.','multipay').'</strong></p>
            <p><input type="submit" name="Upgrade" class="button-primary" style="color: #FFF;" value="'.__('Upgrade to Pro', 'multipay').'" /></p>
            <h2>'.__('Activate your Upgrade','multipay').'</h2>
            <p>'.__('Enter the authorisation key below and click on the Activate button', 'multipay').':</p>
            <p><input type="text" style="width:50%" name="key" value="" /> <input type="submit" name="Check" class="button-secondary" value="'.__('Activate', 'multipay').'" />';
        } else {
            $content .= '<h2 style="color:#B52C00">'.__('Upgrade Activated.','multipay').'</h2>
            <p>'.__('Your authorisation key is','multipay').': '. $qppkey['key'] .'</p>
            <p><input type="submit" name="Delete" class="button-secondary" value="'.__('Delete Key', 'multipay').'" /></p>';
        }
        $content .= wp_nonce_field("save_qp");
        $content .= '</form>';
        echo $content;
    }

    function delete_everything() {
        $qp_setup = qp_get_stored_setup();
        $arr = explode(",",$qp_setup['alternative']);
        foreach ($arr as $item) self::delete_things($item);
        delete_option('qp_setup');
        delete_option('qp_curr');
        delete_option('qp_message');
    }

    function delete_things($form) {
        delete_option('qp_options'.$form);
        delete_option('qp_send'.$form);
        delete_option('qp_error'.$form);
        delete_option('qp_style'.$form);
    }

    function change_form($qp_setup) {
        if ($qp_setup['alternative'] && strpos($qp_setup['alternative'],',')) {
            $content .= '<form style="margin-top: 8px" method="post" action="" >';
            $arr = explode(",",$qp_setup['alternative']);
            sort($arr);
            foreach ($arr as $item) {
                if ($qp_setup['current'] == $item) $checked = 'checked'; else $checked = '';
                $content .='<input type="radio" name="current" value="' .$item . '" ' .$checked . ' />&nbsp;'.$item . ' &nbsp;';
            }
            $content .='<input type="hidden" name="alternative" value = "' . $qp_setup['alternative'] . '" />
            <input type="hidden" name="email" value = "' . $qp_setup['email'] . '" />&nbsp;&nbsp;
            <input type="submit" name="Select" class="button-secondary" value="'.__('Select Form', 'multipay').'" />
            </form>';
        }
        return $content;
    }

    function change_form_update() {
        if( isset( $_POST['Select'])) {
            $qp_setup['current'] = $_POST['current'];
            $qp_setup['alternative'] = $_POST['alternative'];
            $qp_setup['email'] = $_POST['email'];
            update_option( 'qp_setup', $qp_setup);
        }
    }
}