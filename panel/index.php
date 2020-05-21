<?php

    // Variables & functions
    $inc   = true;
    $item  = 'i2cjx7cx';
    $start = microtime( true );

    // Include general player settings
    require './../inc/conf/general.php';

    // ERROR Reporting & Settings
    error_reporting( ( $settings[ 'debugging' ] != 'enabled' ) ? E_ALL & ~E_NOTICE : E_ALL );
    ini_set( 'display_errors', ( ( $settings[ 'debugging' ] == 'enabled' ) ? true : false ) );
    ini_set( 'error_reporting', ( $settings[ 'debugging' ] != 'enabled' ) ? E_ALL & ~E_NOTICE : E_ALL );
    ini_set( "log_errors", ( $settings[ 'debugging' ] != 'disabled' ) ? true : false );
    ini_set( "error_log", getcwd() . "/./../tmp/logs/errors.log" );

    // Output buffer & PHP SESSION
    ob_start();
    session_start();

    // Required control panel files
    include 'template.php';
    include './../inc/functions.php';
    include './../inc/lib/forms.class.php';
    include './../inc/lib/image-resize.class.php';

    // Logout user
    if ( isset( $_GET[ 'logout' ] ) ) {
        unset( $_SESSION[ 'a-login' ] );
        header( "Location: ?s=login" );
    }

    // Create header and attempt login
    head( $settings );
    if ( $_SESSION[ 'a-login' ] !== true ) {

        include 'login.php';

    } else {

        // Safety feature, replaces anything but numbers or letters
        $_GET[ 's' ] = preg_replace( '/[^0-9a-z_]/i', '', ( ( !empty( $_GET[ 's' ] ) ) ? $_GET[ 's' ] : 'home' ) );

        // Now rest
        tabs();
        if ( !empty( $_GET[ 's' ] ) && is_file( "{$_GET['s']}.php" ) ) {

            include "{$_GET['s']}.php";

        } else {

            include 'home.php';

        }

    }

    footer();
?>