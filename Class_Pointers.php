<?php
/**
 * Based on Joost de Valk WPSEO_Pointers Class
 */

class HTML_Mode_Locker_Pointers {

    function __construct() {
    add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ) );
    }
  
    function enqueue() {
        $options = get_option('html_mode_lock');
        if ( isset( $_GET['html_mode_lock_restart_tour'] ) ) {
            unset( $options['ignore_tour'] );
            update_option( 'html_mode_lock', $options );
        }
        
        if ( !isset($options['ignore_tour']) || !$options['ignore_tour'] ) {
            wp_enqueue_style( 'wp-pointer' ); 
            wp_enqueue_script( 'jquery-ui' ); 
            wp_enqueue_script( 'wp-pointer' ); 
            wp_enqueue_script( 'utils' );
            add_action( 'admin_print_footer_scripts', array( &$this, 'print_scripts' ), 99 );
            add_action( 'admin_head', array( &$this, 'admin_head' ) );
        }                
    }

    function print_scripts() {
        global $pagenow, $current_user;

        //check if at least 1 post type is selected
        $options = get_option('html_mode_lock_post_types');
        $step_3_url = 'window.location="'.admin_url('post-new.php?post_type=post&hml_tour=alt-step-three').'";';        
        if( $options ){
            $post_type = array_keys($options);
            $step_3_url = 'window.location="' . admin_url( 'post-new.php?post_type=' . $post_type[0] .'&hml_tour=step-three' ) . '";';
        }

        $adminpages = array( 
            'step-one' => array(
                'content'  => '<h3>'.__( 'Initial Setup', 'html_mode_lock' ).'</h3><p><strong>'.__( 'Go to Settings => Writing.', 'html_mode_lock' ).'</strong></p><p style="color: #707070"><small><strong>'.__( 'About This Tour', 'html_mode_lock' ).'</strong><br/>'.__( 'Clicking Next below takes you to the next page of the tour. If you want to stop this tour, click "Close".', 'html_mode_lock' ).'</small></p>',
                'button2'  => __( 'Next', 'html_mode_lock' ),
                'function' => 'window.location="'.admin_url('options-writing.php?hml_tour=step-two&scroll=1').'";',
                'position_id' => 'icon-options-general',
            ),
            'step-two' => array(
                'content'  => "<h3>".__( "Activate HTML Mode Locker", 'html_mode_lock' )."</h3>"
                     ."<p>".__( "Select post types on which you wish to activate the HTML Mode Locker and click Save Changes.", 'html_mode_lock' )."</p>",                 
                'button2'  => __( 'Next', 'html_mode_lock' ),
                'function' => $step_3_url,
                'position_id' => 'html-mode-locker-settings',
            ),
            'step-three' => array(
                'content'  => '<h3>'.__( 'Lock the Editor in HTML View', 'html_mode_lock' ).'</h3><p>'.__( 'HTML Mode Locker metabox will appear on selected post types editor screen.', 'html_mode_lock' ).'</p><p>'.__( 'To lock the HTML View check the box and click Save Draft or Publish. After that the Vsual editor will be deactivated.', 'html_mode_lock' ).'</p>'.
                '<p><strong>'.__('Plugin Updates','html_mode_lock').'</strong><br/>'.
                __( 'If you would like to keep up to date regarding the HTML Mode Locker plugin and other plugins by SimpleRealtyTheme.com, subscribe to the updates:', 'html_mode_lock').'</p>'.
                '<form action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open(\'http://feedburner.google.com/fb/a/mailverify?uri=SimpleRealty\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');return true">'.
                '<p>'.
                '<label for="plugin-updates-email">'.__('Email','html_mode_lock').':</label><input style="color:#666" name="email" value="'.$current_user->user_email.'" id="plugin-updates-email" placeholder="'.__('Email','html_mode_lock').'"/><br/>'.
                '<input type="hidden" value="SimpleRealty" name="uri"/><input type="hidden" name="loc" value="en_US"/>'.
                '<button type="submit" class="button-primary">'.__('Subscribe','html_mode_lock').'</button>'.
                '</p></form>'.
                '<p>'.__( 'The tour ends here, good luck!', 'html_mode_lock' ).'</p>',
                'position_id' => 'html_mode_lock',
            ),
            //alternative step 3 in case they don't have post types selected
            'alt-step-three' => array(
                'content'  => '<h3>'.__( 'Plugin Usage', 'html_mode_lock' ).'</h3><p>'.__( 'HTML Mode Locker metabox will appear on selected post types editor screen <strong>above the <u>Publish</u></strong> metabox.', 'html_mode_lock' ).'</p><p><a href="' . admin_url('options-writing.php?hml_tour=step-two&scroll=1') . '"><strong>&#8592; ' .__( 'Go back', 'html_mode_lock') . '</strong></a> ' . __('to activate the HTML Locker on desired post types.', 'html_mode_lock' ).'</p>',
                'button2'  => '&#8592; ' . __( 'Go Back', 'html_mode_lock' ),
                'function' => 'window.location="'.admin_url('options-writing.php?hml_tour=step-two&scroll=1').'";',
                'position_id' => 'submitdiv',
            ),      
                
        );

        $step = '';
        if ( isset($_GET['hml_tour']) )
            $step = $_GET['hml_tour'];


        if ( 'admin.php' != $pagenow  && ( !isset($step) || !array_key_exists( $step, $adminpages ) ) ) {      
            $id       = 'adminmenu li.current a.current';
            $content    = '<h3>'.__( 'Congratulations!', 'html_mode_lock' ).'</h3>';
            $content    .= '<p>'.__( 'You\'ve just installed HTML Mode Locker! Click "Start Tour" to view a 3-step guide on how to use this plugin.', 'html_mode_lock' ).'</p>';
            $position_at  = 'left top';
            $button2    = __( "Start Tour", 'html_mode_lock' );
            $function     = 'document.location="'.admin_url('options-writing.php?hml_tour=step-one').'";';
        } else {
            if( 'step-two' == $step && isset($_GET['settings-update']) ){
                $content = 'false';
            }
            elseif ( '' != $step && in_array( $step, array_keys( $adminpages ) ) ) {
                $id       = ( isset($adminpages[$step]['position_id']) ) ? $adminpages[$step]['position_id'] : 'icon-options-general';
                $content    = $adminpages[$step]['content'];
                $position_at  = 'left top';
                $button2    = $adminpages[$step]['button2'];
                $function     = $adminpages[$step]['function'];
            }
        }

        $this->print_buttons( $id, $content, __( "Close", 'html_mode_lock' ), $position_at, $button2, $function );
    }
  
    function admin_head() {
    ?>
        <style type="text/css" media="screen">
          #pointer-primary, #tour-close {
            margin: 0 5px 0 0;
          }
        </style>
    <?php
    }

    function print_buttons( $id, $content, $button1, $position_at, $button2 = false, $button2_function = '' ) {
    ?>
    <script type="text/javascript"> 
    //<![CDATA[ 
        jQuery(document).ready( function() { 

        <?php
        if( isset($_GET['scroll']) ){
        ?>
          jQuery('html, body').animate({
            scrollTop: jQuery("#<?php echo $id; ?>").offset().top
          }, 2000);
        <?php
        }
        ?>

    function html_mode_lock_setIgnore( option, hide, nonce ) {
        jQuery.post(ajaxurl, { 
            action: 'html_mode_lock_set_ignore', 
            option: option,
            _wpnonce: nonce
        }, function(data) { 
            if (data) {
                jQuery('#'+hide).hide();
                jQuery('#hidden_ignore_'+option).val('ignore');
            }
            }
        );
    }

    jQuery('#<?php echo $id; ?>').pointer({ 
        content: '<?php echo addslashes( $content ); ?>', 
        buttons: function( event, t ) {
            button = jQuery('<a id="pointer-close" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
            button.bind( 'click.pointer', function() {
          t.element.pointer('close');
        });
        return button;
        },
        position: {
            my: 'left bottom', 
            at: '<?php echo $position_at; ?>', 
            offset: 0
        },
        arrow: {
            edge: 'left',
            align: 'bottom',
            offset: 0
        },
        close: function() { },
    }).pointer('open'); 

    <?php if ( $button2 ) { ?> 
        jQuery('#pointer-close').after('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
        jQuery('#pointer-primary').click( function() {
      <?php echo $button2_function; ?>
        });        
    <?php } ?>
        jQuery('#pointer-close').click( function() {
            html_mode_lock_setIgnore("tour","wp-pointer-0","<?php echo wp_create_nonce('html_mode_lock-ignore'); ?>");
        });
    }); 
    //]]> 
    </script>
    <?php
    }

} //END Class

$html_mode_lock_pointers = new HTML_Mode_Locker_Pointers;
