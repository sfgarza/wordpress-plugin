<?php
namespace IDX\Widgets;

class Impress_Lead_Signup_Widget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {

        $this->idx_api = new \IDX\Idx_Api();

        if(isset($_GET['error'])){
            $this->error_message = $this->handle_errors($_GET['error']);
        } else {
            $this->error_message = '';
        }

        parent::__construct(
            'impress_lead_signup', // Base ID
            __('IMPress Lead Sign Up', 'idxbroker'), // Name
            array(
                'description' => __('Lead sign up form', 'idxbroker'),
                'classname' => 'impress-idx-signup-widget',
                'customize_selective_refresh' => true,
            )
        );
    }

    public $idx_api;
    public $error_message;
    public $defaults = array(
        'title' => 'Lead Sign Up',
        'custom_text' => '',
        'phone_number' => false,
        'styles' => 1,
        'new_window' => 0,
    );

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        $defaults = $this->defaults;

        $instance = wp_parse_args( (array) $instance, $defaults );
        
        extract($args);

        if (empty($instance)) {
            $instance = $this->defaults;
        }

        if (!empty($instance['styles'])) {
            wp_enqueue_style('impress-lead-signup', plugins_url('../assets/css/widgets/impress-lead-signup.css', dirname(__FILE__)));
        }

        if (!isset($instance['new_window'])) {
            $instance['new_window'] = 0;
        }

        $target = $this->target($instance['new_window']);

        $title = $instance['title'];
        $custom_text = $instance['custom_text'];


        //Validate fields
        wp_register_script('impress-lead-signup', plugins_url('../assets/js/idx-lead-signup.min.js', dirname(__FILE__)));
        wp_localize_script('impress-lead-signup', 'idxLeadLoginUrl', $this->lead_login_page());
        wp_enqueue_script('impress-lead-signup');

        echo $before_widget;

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        if (!empty($custom_text)) {
            echo '<p>', $custom_text, '</p>';
        }

        ?>
		<form action="<?php echo $this->idx_api->subdomain_url();?>ajax/usersignup.php" class="impress-lead-signup" method="post" target="<?php echo $target?>" name="LeadSignup" id="LeadSignup">
                        <?php echo $this->error_message; ?>
			<input type="hidden" name="action" value="addLead">
			<input type="hidden" name="signupWidget" value="true">
			<input type="hidden" name="contactType" value="direct">

			<label id="impress-widgetfirstName-label" class="ie-only" for="impress-widgetfirstName"><?php _e('First Name:', 'idxbroker');?></label>
			<input id="impress-widgetfirstName" type="text" name="firstName" placeholder="First Name" required>

			<label id="impress-widgetlastName-label" class="ie-only" for="impress-widgetlastName"><?php _e('Last Name:', 'idxbroker');?></label>
			<input id="impress-widgetlastName" type="text" name="lastName" placeholder="Last Name" required>

			<label id="impress-widgetemail-label" class="ie-only" for="impress-widgetemail"><?php _e('Email:', 'idxbroker');?></label>
			<input id="impress-widgetemail" type="email" name="email" placeholder="Email" required>

			<?php if ($instance['phone_number'] == true) {
            echo '
				<label id="impress-widgetphone-label" class="ie-only" for="impress-widgetphone">' . __('Phone:', 'idxbroker') . '</label>
				<input id="impress-widgetphone" type="tel" name="phone" placeholder="Phone">';
        }?>

			<input id="bb-IDX-widgetsubmit" type="submit" name="submit" value="Sign Up!">
		</form>
		<?php

        echo $after_widget;
    }

    public function target($new_window)
    {
        if (!empty($new_window)) {
            //if enabled, open links in new tab/window
            return '_blank';
        } else {
            return '_self';
        }
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['custom_text'] = htmlentities($new_instance['custom_text']);
        $instance['phone_number'] = $new_instance['phone_number'];
        $instance['styles'] = (int) $new_instance['styles'];
        $instance['new_window'] = strip_tags($new_instance['new_window']);

        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {

        $idx_api = $this->idx_api;

        $defaults = $this->defaults;

        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:');?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php esc_attr_e($instance['title']);?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('custom_text')?>"><?php _e('Custom Text', 'idxbroker');?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('custom_text');?>" name="<?php echo $this->get_field_name('custom_text');?>" value="<?php esc_attr_e($instance['custom_text']);?>" rows="5"><?php esc_attr_e($instance['custom_text']);?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('phone_number');?>"><?php _e('Show phone number field?', 'idxbroker');?></label>
			<input type="checkbox" id="<?php echo $this->get_field_id('phone_number');?>" name="<?php echo $this->get_field_name('phone_number')?>" value="1" <?php checked($instance['phone_number'], true);?>>
		</p>
        <p>
            <label for="<?php echo $this->get_field_id('styles');?>"><?php _e('Default Styling?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('styles');?>" name="<?php echo $this->get_field_name('styles')?>" value="1" <?php checked($instance['styles'], true);?>>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('new_window');?>"><?php _e('Open in a New Window?', 'idxbroker');?></label>
            <input type="checkbox" id="<?php echo $this->get_field_id('new_window');?>" name="<?php echo $this->get_field_name('new_window')?>" value="1" <?php checked($instance['new_window'], true);?>>
        </p>
		<?php

    }

    function lead_login_page()
    {
        $links = $this->idx_api->idx_api_get_systemlinks();
        if(empty($links)){
            return '';
        }
        foreach($links as $link){
            if(preg_match('/userlogin/i', $link->url)){
                return $link->url;
            }
        }
    }

    //For error handling since this is a cross domain request.
    public function handle_errors($error)
    {
        $output = '';

        //User already has an account.
        if(stristr($error, 'lead' )){
            //Redirect to lead login page.
            return wp_redirect($this->lead_login_page());
        //Other form error.
        } elseif(stristr($error, 'true')){
            $output .= '<div class="error">';
            $output .= 'There is an error in the form. Please double check that your email address is valid.';
            $output .= '</div>';
        } 

        return $output;
    }
}
