<?php

    if ( !ob_get_level() ) ob_start();
    if ( is_file( 'inc/conf/channels.php' ) ) require 'inc/conf/channels.php'; else die( 'Unable to load channels configuration!' );
    if ( !is_array( $channels ) ) $channels = array();

    // Check if selected channel data exists
    foreach ( $channels as $key => $search ) {

        // Match requested channel in settings array
        if ( $search[ 'name' ] == $_GET[ 'c' ] ) {

            $chn_key = $key;
            break;

        }

    }

    ## Make sure that channel exists
    if ( !is_array( $channels[ $chn_key ] ) ) die( 'Selected channel does not exist. It may have been deleted or renamed. Please try again later.' );


    // Generate Playlist and send it as attachment.
    session_write_close();

    // Headers required for transfer
    header( "Content-Description: File Transfer" );
    header( "Content-Transfer-Encoding: binary" );
    header( "Pragma: public" );
    header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );

    // Replace some characters in title to get radio name out
    $radioName = str_ireplace( array( ' player' ), '', $settings[ 'title' ] );

    // Different playlist files, different headers / content
    switch ( $_GET[ 'pl' ] ) {

        case "wmp":

            // Windows Media Player ASX headers & filename
            header( "Content-Type: video/x-ms-asf" );
            header( "Content-Disposition: attachment; filename=\"Listen.asx\"" );

            // File content
            echo "<asx version=\"3.0\">\r\n";
            foreach ( $channels[ $key ][ 'streams' ] as $name => $link ) {
                if ( is_array( $link ) ) {
                    foreach ( $link as $k => $v ) {
                        echo "<title>{$radioName}</title>\r\n<entry>\r\n<title>{$name} (" . strtoupper( $k ) . ")</title>\r\n<ref href=\"" . preg_replace( "/;.*$/", '', $v ) . "\"/>\r\n</entry>\r\n";
                    }
                }
            }

            echo '</asx>';
            break;

        case "quicktime":

            // M3U (QuickTime, VLC, etc...) M3U Playlist headers & filename
            header( "Content-Type: application/x-mpegurl" );
            header( "Content-Disposition: attachment; filename=\"Listen.m3u\"" );

            // Content
            $i = 0;
            echo "#EXTM3U\r\n";
            foreach ( $channels[ $key ][ 'streams' ] as $name => $link ) {
                if ( is_array( $link ) ) {
                    foreach ( $link as $k => $v ) {
                        echo "#EXTINF:{$i},{$name} (" . strtoupper( $k ) . ")\r\n" . preg_replace( "/;.*$/", '', $v ) . "\r\n";
                        $i++;
                    }
                }
            }

            break;

        default:

            // Default PLS headers & filename
            header( "Content-Type: audio/x-scpls" );
            header( "Content-Disposition: attachment; filename=\"Listen.pls\"" );

            // Content
            $i = 0;
            echo "[playlist]\r\n";
            foreach ( $channels[ $key ][ 'streams' ] as $name => $link ) {
                if ( is_array( $link ) ) {
                    foreach ( $link as $k => $v ) {
                        $i++;
                        echo "File{$i}=" . preg_replace( "/;.*$/", '', $v ) . "\r\nTitle{$i}={$radioName} ({$name} (" . strtoupper( $k ) . "))\r\nLength{$i}=0\r\n\r\n";
                    }
                }
            }

            echo "NumberOfEntries={$i}\r\n\r\nVersion=2";
            break;
    }

    ob_end_flush();

?>