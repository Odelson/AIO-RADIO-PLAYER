<?php

    /* Function to display header of admin pages
    ================================================================================== */
    function head( $settings = array() ) {

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=1024">

            <title><?php echo( ( empty( $settings[ 'title' ] ) ) ? 'AIO - Radio Player' : $settings[ 'title' ] ); ?> :: Control Panel</title>
            <link rel="shortcut icon" href="./assets/favicon.ico">
            <link rel="icon" type="image/png" href="./assets/favicon.png" sizes="32x32" />

            <!-- AIO Radio Control Panel Style Sheet -->
            <link rel="stylesheet" href="./assets/style.css" type="text/css">
            <link rel="stylesheet" href="./assets/panel.style.css" type="text/css">

            <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css">
            <link href="//fonts.googleapis.com/css?family=Quicksand:400,500,600" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css?family=Mali:400,500" rel="stylesheet">

            <script src="./../assets/js/jquery-1.11.2.min.js"></script>
            <script src="./../assets/js/jquery.selectbox.min.js"></script>
            <script src="./assets/bootstrap.min.js"></script>
        </head>
        <body>
        <?php if ( $_SESSION[ 'a-login' ] === true ) { ?>
            <section class="intro small">
                <div class="container content">
                    <a href="?logout" class="pull-right btn btn-danger"><i class="fa fa-sign-out"></i> Logout</a>
                    <h2>AIO - Radio Station Player</h2>
                    <h3>Control Panel</h3>
                </div>
            </section>
            <?php
        }

    }

    /* Function to display footer of admin pages
    ================================================================================== */
    function footer( $script = '' ) {

        global $item, $start;
        echo '</section><br><br>';
        ?>

                <!-- Scripts -->
                <script><?php echo "var version = '" . trim( rtrim( file_get_contents( 'version.txt' ) ) ) . "', itemID='" . $item . "';"; ?></script>
                <script type="text/javascript" src="./assets/panel.min.js"></script>
                <script type="text/javascript">
                    if ( typeof (window.loadinit) == "function" ) { window.loadinit(); }
                    $( "select" ).selectbox();
                    console.log("Page generated in <?php echo round( ( ( microtime( true ) - $start ) * 1000 ), 2 );?>ms");
                </script>
            </body>
        </html>
        <?php
    }

    /* Function to display tabs of admin pages
    ================================================================================== */
    function tabs() {

        ## Array of available Tab links
        $tabs = array(
            '<i class="fa fa-home"></i> Home'              => 'home',
            '<i class="fa fa-database"></i> Channels'      => 'channels',
            '<i class="fa fa-language"></i> Language'      => 'lang',
            '<i class="fa fa-wrench"></i> Tools'           => 'tools',
            '<i class="fa fa-cogs"></i> Settings'          => 'settings',
            '<i class="fa fa-cloud-download"></i> Updates' => 'updates'
        );


        ## Show version in nav
        $out = '<div class="menu"><div class="container"><span class="pull-right script-version">
		<b>v' . ( ( is_file( 'version.txt' ) ) ? file_get_contents( 'version.txt' ) : '' ) . '</b></span><ul class="tabs">';


        ## Loop
        foreach ( $tabs as $tab => $link ) {

            if ( ( !empty( $_GET[ 's' ] ) && $_GET[ 's' ] == $link ) OR ( empty( $_GET[ 's' ] ) && $link == 'home' ) ) $active = 'class="active"'; else $active = ''; ## Active state
            $out .= "<li><a id=\"tab-{$link}\" href=\"?s={$link}\" {$active}>{$tab}</a></li>";

        }


        echo $out . '</ul></div></div><section class="container main">';

    }

?>