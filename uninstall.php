<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit ();

    delete_option( 'html_mode_lock' );
    delete_option( 'html_mode_lock_post_types' );
