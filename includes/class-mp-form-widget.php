<?php
/**
 * MultiPay Form Widget
 *
 * @since   1.5
 * @class   MP_Form_Widget
 */

defined( 'ABSPATH' ) || exit;

add_action( 'widgets_init', function() {
    register_widget( 'MP_Form_Widget' );
});

class MP_Form_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'qp_widget',
            'MultiPay',
            array( 'description' => __( 'MultiPay', 'Add payment form to your sidebar' ), )
        );
    }
    
    public function widget( $args, $instance ) {
        if ( is_null( MP()->form ) ) {
            return '';
        }
        
        extract($args, EXTR_SKIP);
        $id=$instance['id'];
        $amount=$instance['amount'];
        $form=$instance['form'];
        MP()->form->output($instance);
    }
    
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['id'] = $new_instance['id'];
        $instance['amount'] = $new_instance['amount'];
        $instance['form'] = $new_instance['form'];
        return $instance;
    }
    
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'amount' => '' , 'id' => '','form' => '' ) );
        $id = $instance['id'];
        $amount = $instance['amount'];
        $form=$instance['form'];
        $qp_setup = MP()->get_stored_setup();
        ?>
        <h3>Select Form:</h3>
        <select class="widefat" name="<?php echo $this->get_field_name('form'); ?>">
        <?php
        $arr = explode(",",$qp_setup['alternative']);
        foreach ($arr as $item) {
            if ($item == '') {$showname = 'default'; $item='';} else $showname = $item;
            if ($showname == $form || $form == '') $selected = 'selected'; else $selected = '';
            ?><option value="<?php echo $item; ?>" id="<?php echo $this->get_field_id('form'); ?>" <?php echo $selected; ?>><?php echo $showname; ?></option><?php } ?>
        </select>
        <h3>Presets:</h3>
        <p><label for="<?php echo $this->get_field_id('id'); ?>">Payment Reference: <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo attribute_escape($form); ?>" /></label></p>

        <p><label for="<?php echo $this->get_field_id('amount'); ?>">Amount: <input class="widefat" id="<?php echo $this->get_field_id('amount'); ?>" name="<?php echo $this->get_field_name('amount'); ?>" type="text" value="<?php echo attribute_escape($amount); ?>" /></label></p>

        <p>Leave blank to use the default settings.</p>
        <p>To configure the payment form use the <a href="admin.php?page=multipay-settings">Settings</a> page</p>
        <?php
    }
}