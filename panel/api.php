<?php

    // Some required functions
    require './../inc/functions.php';
    require './../inc/lib/image-resize.class.php';
    require './../inc/conf/general.php';

    // ERROR Reporting & Settings
    error_reporting( ( $settings[ 'debugging' ] != 'enabled' ) ? E_ALL & ~E_NOTICE : E_ALL );
    ini_set( 'display_errors', false ); // On API, always false!
    ini_set( 'error_reporting', ( $settings[ 'debugging' ] != 'enabled' ) ? E_ALL & ~E_NOTICE : E_ALL );
    ini_set( "log_errors", ( $settings[ 'debugging' ] != 'disabled' ) ? true : false );
    ini_set( "error_log", getcwd() . "/./../tmp/logs/errors.log" );

    // Verify authorization
    session_start();
    if ( $_SESSION[ 'a-login' ] !== true ) {

        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        exit;

    }

    // Avoid session locking
    session_write_close();

    // Set AIO Path (relative to root path)
    $path = realpath( './../' );

    // Create Default Array that we'll use through script
    $response = array();

    // Actions
    switch ( $_GET[ 'action' ] ) {

        // Get notices/warnings
        case 'checkWarnings':

            // First attempt to create missing folders
            $folders = array( 'tmp/cache', 'tmp/images', 'tmp/logs', 'tmp/updates' );
            foreach ( $folders as $folder ) {

                if ( !is_dir( "{$path}/{$folder}" ) ) {

                    if ( $folder === 'tmp/images' ) $copyArtwork = true;
                    mkdir( "{$path}/{$folder}", 0755, true );
                    $response[] = array( 'type' => 'info', 'message' => 'Folder with name "' . $folder . '" was missing, so we created it for you!' );

                }

            }

            // Find default artwork
            $extensions = array( 'jpg', 'jpeg', 'png', 'svg', 'webp' );
            $artwork    = false;
            foreach ( $extensions as $ext ) {
                if ( is_file( "{$path}/tmp/images/default.{$ext}" ) ) {
                    $artwork = "default.{$ext}";
                    break;
                }
            }

            // Copy default artwork
            if ( isset( $copyArtwork ) && $artwork === false ) copy( "{$path}/assets/img/default.jpg", "{$path}/tmp/images/default.jpg" );

            // Check if default artwork exists
            if ( $artwork === false && !isset( $copyArtwork ) )
                $response[] = array( 'type' => 'warning', 'message' => 'AIO Radio station player does not have **default ARTWORK** installed! Please use artwork manager and upload artist image called **"default"**.' );

            // PHP Version
            if ( phpversion() <= 5.2 )
                $response[] = array( 'type' => 'warning', 'message' => 'The server is running **PHP ' . phpversion() . '** while this script requires at least **PHP 5.3** or above!' );

            // Simple XML
            if ( !function_exists( 'simplexml_load_string' ) )
                $response[] = array( 'type' => 'warning', 'message' => 'PHP is not compiled with SimpleXML extension! This may cause some serious issues!' );

            // CURL
            if ( !function_exists( 'curl_version' ) )
                $response[] = array( 'type' => 'warning', 'message' => 'PHP **CURL extension** is not enabled! This script does not work without the extension!' );

            // Cache
            if ( !is_writable( "{$path}/tmp/cache" ) )
                $response[] = array( 'type' => 'warning', 'message' => 'Directory **/tmp/cache/** is not writable! This will cause extreme slow performance! You can fix this issue by setting **chmod** of folder **/tmp/cache/** to **755**.' );

            // Images folder
            if ( !is_writable( "{$path}/tmp/images" ) )
                $response[] = array( 'type' => 'warning', 'message' => 'Directory **/tmp/images/** is not writable! You will not be able to upload custom artist images or channel logo(s)! You can fix this issue by setting **chmod** of folder **/tmp/images/** to **755**.' );

            // Logs folder
            if ( !is_writable( "{$path}/tmp/logs" ) )
                $response[] = array( 'type' => 'warning', 'message' => 'Directory **/tmp/logs/** is not writable! This means that player will not be able to write error log! You can fix this issue by setting **chmod** of folder **/tmp/logs/** to **755**.' );

            // Present error logs
            if ( is_file( './../tmp/logs/errors.log' ) && $settings[ 'debugging' ] !== 'disabled' )
                $response[] = array( 'type' => 'log-warning', 'message' => 'ERROR log file is present, please check it out or delete it!' );

            // Upgrade not completed
            if ( is_file( "{$path}/post-update.php" ) && $settings[ 'development' ] !== true )
                $response[] = array( 'type' => 'finish-upgrade', 'message' => 'Upgrade was partially completed!' );

            break;

        // Get list of Artworks
        case 'getArtwork':

            // Read list of files
            $files = browse( './../tmp/images/' );

            // Check if its array
            if ( is_array( $files ) AND count( $files ) >= 1 ) {

                // Loop
                foreach ( $files as $file ) {

                    // Skip logo files
                    if ( preg_match( '/^logo\.[0-9]+/i', $file ) ) continue;

                    // Skip non image files
                    if ( !preg_match( '/\.(jpe?g|png|webp|svg)$/i', $file ) ) continue;

                    // Create array of files to respond with
                    $response[] = array(
                        'name' => extDel( basename( $file ) ),
                        'path' => "tmp/images/{$file}",
                        'size' => formatBytes( filesize( "./../tmp/images/{$file}" ) ),
                    );

                }

                ksort( $response );

            }

            break;

        // Delete artwork
        case 'deleteArtwork':
            $response = array( 'success' => deleteArtist( $_GET[ 'name' ] ) );
            break;

        case 'deleteLog':
            $response = array( 'success' => ( @unlink( './../tmp/logs/errors.log' ) ) );
            break;

        // Push log file to browser (whole)
        case 'getLog':

            $logFile = './../tmp/logs/errors.log';
            header( "Content-type: text/plain" );

            // If file exists, proceed.
            if ( is_file( $logFile ) ) {

                header( 'Content-Length: ' . filesize( $logFile ) );
                echo file_get_contents( $logFile );

            }

            exit;
            break;

        // Default
        default:
            $response = array( 'message' => "404 - Nothing here!" );
            break;

    }

    header( "Content-type: application/json" );
    echo json_encode( $response );

?>

