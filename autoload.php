<?php

/*--------------------------------------------------------------
 *             Function to automatic load class files
 *-------------------------------------------------------------*/

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

spl_autoload_register(function ($class) {
        // project-specific namespace prefix
        $prefix = 'MonLabo';
        $base_dir = __DIR__;
        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if ( 0 !== strncmp($prefix, $class, $len) ) {
            // no, move to the next registered autoloader
            return;
        }

        $relative_class = substr($class, $len);
        $classNameParts = explode( '\\', $relative_class );
        $classNameLastPart = array_pop( $classNameParts );
        $dir = $base_dir . implode( '/', $classNameParts );
        $file = $dir . '/class-' . strtolower( str_replace('_', '-', $classNameLastPart ) ) . '.php';

        // if the file exists, require it
        if ( file_exists( $file ) ) {
            require_once $file;
        } else {
            //Retry with trait
            $file = $dir . '/trait-' . strtolower( str_replace('_', '-', $classNameLastPart ) ) . '.php';
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }
);

?>
