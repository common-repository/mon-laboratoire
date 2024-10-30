<?php

/*--------------------------------------------------------------
 *             Functions for compatibility
 * 
 * For compatibility with previous php versions
 *      array_key_first( array $arr ) 
 *      str_starts_with(string $haystack, string $needle)
 * 
 * If librairy php-mbstring is not installed
 *      
 * 
 *-------------------------------------------------------------*/

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

//For compatibility with php < 7.3
if ( ! function_exists( 'array_key_first' ) ) {
	/**
	* @phan-suppress PhanRedefineFunctionInternal
	* @phan-suppress PhanUnreferencedFunction
	* @param mixed[] $arr array
	* @return int|string|null
	*/
	function array_key_first( array $arr ) {
		foreach ( array_keys( $arr ) as $key ) {
			return $key;
		}
		return null;
	}
}

//For compatibility with php < 8
if ( ! function_exists( 'str_starts_with' ) ) {
	/**
	* @phan-suppress PhanRedefineFunctionInternal
	* @param string $haystack
	* @param string $needle
	* @return bool
	*/	
	function str_starts_with(string $haystack, string $needle): bool {
	    return \strncmp($haystack, $needle, \strlen($needle)) === 0;
	}
}

//If librairy php-mbstring is not installed
if ( ! function_exists( 'mb_strtoupper' ) ) {
	/**
	* @phan-suppress PhanRedefineFunctionInternal
	* @phan-suppress PhanUnusedGlobalFunctionParameter
	* @param string $string The string being uppercased.
	* @param ?string $encoding encoding parameter (not used in polyfill)
	* @return string
	*/	
	function  mb_strtoupper(string $string, $encoding = null): string {
	    return strtoupper( $string );
	}
}

//If librairy php-mbstring is not installed
if ( ! function_exists( 'mb_strtolower' ) ) {
	/**
	* @phan-suppress PhanRedefineFunctionInternal
	* @phan-suppress PhanUnusedGlobalFunctionParameter
	* @param string $string The string being lowercased.
	* @param ?string $encoding encoding parameter (not used in polyfill)
	* @return string
	*/	
	function  mb_strtolower(string $string, $encoding = null): string {
	    return strtolower( $string );
	}
}

//If librairy php-mbstring is not installed
if ( ! function_exists( 'mb_substr' ) ) {
	/**
	* @phan-suppress PhanRedefineFunctionInternal
	* @phan-suppress PhanUnusedGlobalFunctionParameter
	* @param string $string The string to extract the substring from. 
	* @param int $start If start is non-negative, the returned string will start at the start'th position. 
	* @param ?int $length Maximum number of characters to use from string.
	* @param ?string $encoding encoding parameter (not used in polyfill)
	* @return string
	*/	
	function   mb_substr(string $string, int $start, $length = null, $encoding = null ): string {
	    return substr( $string, $start, intval( $length ) );
	}
}
?>
