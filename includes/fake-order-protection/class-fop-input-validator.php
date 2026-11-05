<?php
/**
 * Fake Order Protection - Input Validator & Phone Normalizer
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_Input_Validator {

    /**
     * Map of Bengali digits to ASCII digits.
     */
    const BN_DIGITS = [ '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ];
    const EN_DIGITS = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];

    /**
     * Convert Bengali numerals to ASCII digits and normalize BD phone number.
     *
     * Normalizes formats:
     * - +88017XXXXXXXX -> 017XXXXXXXX
     * - 88017XXXXXXXX  -> 017XXXXXXXX
     * - 0088017XXXXXXX -> 017XXXXXXXX
     * - 17XXXXXXXX     -> 017XXXXXXXX (10-digit without leading 0)
     * - ০১৭১২৩৪৫৬৭৮     -> 01712345678
     *
     * @param string $phone Raw phone string.
     * @return string Normalized phone number or digits string.
     */
    public static function normalize_phone( $phone ) {
        if ( empty( $phone ) || ! is_scalar( $phone ) ) {
            return '';
        }

        $phone = (string) $phone;

        // 1. Convert Bengali numerals to ASCII numerals
        $phone = str_replace( self::BN_DIGITS, self::EN_DIGITS, $phone );

        // 2. Strip spaces, hyphens, brackets, dots, plus signs, and any non-digit character
        $digits = preg_replace( '/\D+/', '', $phone );

        if ( empty( $digits ) ) {
            return '';
        }

        // 3. Normalize international prefixes
        // 00880XXXXXXXXXX -> 0XXXXXXXXXX
        if ( strpos( $digits, '00880' ) === 0 ) {
            $digits = '0' . substr( $digits, 5 );
        }
        // 880XXXXXXXXXX -> 0XXXXXXXXXX
        elseif ( strpos( $digits, '880' ) === 0 ) {
            $digits = '0' . substr( $digits, 3 );
        }
        // 10-digit number starting with [1-9] e.g. 17XXXXXXXX -> add leading 0
        elseif ( strlen( $digits ) === 10 && strpos( $digits, '1' ) === 0 ) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    /**
     * Validate whether a phone number matches standard Bangladesh mobile pattern:
     * 11 digits starting with 01[3-9] (Grameenphone, Banglalink, Robi, Airtel, Teletalk, Skitto).
     *
     * @param string $phone
     * @return bool
     */
    public static function is_valid_bd_phone( $phone ) {
        $normalized = self::normalize_phone( $phone );
        return (bool) preg_match( '/^01[3-9]\d{8}$/', $normalized );
    }

    /**
     * Parse configured junk keywords into a clean array.
     *
     * @param string|array $keywords
     * @return array
     */
    public static function parse_keywords( $keywords ) {
        if ( is_array( $keywords ) ) {
            return array_filter( array_map( 'mb_strtolower', array_map( 'trim', $keywords ) ) );
        }

        if ( ! is_string( $keywords ) || trim( $keywords ) === '' ) {
            return [];
        }

        $parts = preg_split( '/[\r\n,;|]+/u', $keywords );
        $clean = [];
        foreach ( $parts as $part ) {
            $t = mb_strtolower( trim( $part ) );
            if ( '' !== $t ) {
                $clean[] = $t;
            }
        }

        return array_unique( $clean );
    }

    /**
     * Check if a text contains any junk keywords.
     *
     * @param string $text
     * @param array  $keywords
     * @return bool|string The matched keyword or false.
     */
    public static function match_junk_keyword( $text, array $keywords ) {
        if ( empty( $keywords ) || trim( $text ) === '' ) {
            return false;
        }

        $lower_text = mb_strtolower( trim( $text ) );

        // Extract tokens/words from input
        $tokens = preg_split( '/[\s,.\-_#@!?\/\\\]+/u', $lower_text );
        $tokens = array_filter( array_map( 'trim', $tokens ) );

        foreach ( $keywords as $kw ) {
            if ( '' === $kw ) {
                continue;
            }

            // Exact match with full text
            if ( $lower_text === $kw ) {
                return $kw;
            }

            // Word token match
            if ( in_array( $kw, $tokens, true ) ) {
                return $kw;
            }

            // For longer keywords (4+ chars), substring match
            if ( mb_strlen( $kw ) >= 4 && mb_stripos( $lower_text, $kw ) !== false ) {
                return $kw;
            }
        }

        return false;
    }

    /**
     * Check if a string has 4 or more repeated consecutive characters (e.g. "aaaa", "1111", "....").
     *
     * @param string $text
     * @return bool
     */
    public static function has_repeated_chars( $text ) {
        return (bool) preg_match( '/(.)\1{3,}/u', (string) $text );
    }

    /**
     * Validate customer full name.
     *
     * @param string $name
     * @param array  $settings
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validate_name( $name, array $settings ) {
        $name = trim( (string) $name );
        $min_len = isset( $settings['min_name_length'] ) ? abs( intval( $settings['min_name_length'] ) ) : 3;
        $reject_repeated = ! empty( $settings['reject_repeated_chars'] );
        $keywords = self::parse_keywords( $settings['junk_keywords'] ?? '' );

        // 1. Min length
        if ( mb_strlen( $name ) < $min_len ) {
            $msg = ! empty( $settings['msg_invalid_name'] ) 
                ? $settings['msg_invalid_name'] 
                : esc_html__( 'Please enter a valid full name (minimum 3 characters).', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 2. Reject digits-only or symbols-only (must contain at least one letter)
        $has_letter = preg_match( '/[\p{L}]/u', $name );
        $is_digits_only = preg_match( '/^\d+$/', preg_replace( '/\s+/', '', $name ) );
        if ( ! $has_letter || $is_digits_only ) {
            $msg = ! empty( $settings['msg_invalid_name'] ) 
                ? $settings['msg_invalid_name'] 
                : esc_html__( 'Please enter a valid full name containing letters.', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 3. Reject repeated consecutive characters (4+)
        if ( $reject_repeated && self::has_repeated_chars( $name ) ) {
            $msg = ! empty( $settings['msg_repeated_chars'] ) 
                ? $settings['msg_repeated_chars'] 
                : esc_html__( 'Name cannot contain repeated characters (e.g. aaaa).', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 4. Reject junk keywords
        if ( ! empty( $keywords ) && self::match_junk_keyword( $name, $keywords ) ) {
            $msg = ! empty( $settings['msg_junk_keyword'] ) 
                ? $settings['msg_junk_keyword'] 
                : esc_html__( 'Name contains invalid or junk keywords.', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        return [ 'valid' => true, 'error' => '' ];
    }

    /**
     * Validate customer address.
     *
     * @param string $address
     * @param array  $settings
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validate_address( $address, array $settings ) {
        $address = trim( (string) $address );
        $min_len = isset( $settings['min_address_length'] ) ? abs( intval( $settings['min_address_length'] ) ) : 10;
        $min_words = isset( $settings['min_address_words'] ) ? max( 1, abs( intval( $settings['min_address_words'] ) ) ) : 2;
        $reject_repeated = ! empty( $settings['reject_repeated_chars'] );
        $keywords = self::parse_keywords( $settings['junk_keywords'] ?? '' );

        // 1. Min length
        if ( mb_strlen( $address ) < $min_len ) {
            $msg = ! empty( $settings['msg_invalid_address'] ) 
                ? $settings['msg_invalid_address'] 
                : esc_html__( 'Please enter a detailed delivery address (minimum 10 characters).', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 2. Min words (must contain at least N words)
        $words = array_filter( preg_split( '/[\s,.\-_#@!?\/\\\]+/u', $address ) );
        if ( count( $words ) < $min_words ) {
            $msg = ! empty( $settings['msg_invalid_address'] ) 
                ? $settings['msg_invalid_address'] 
                : esc_html__( 'Please provide a complete address with at least 2 words (e.g. House/Area and City).', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 3. Must contain at least one letter
        if ( ! preg_match( '/[\p{L}]/u', $address ) ) {
            $msg = ! empty( $settings['msg_invalid_address'] ) 
                ? $settings['msg_invalid_address'] 
                : esc_html__( 'Address must contain valid location text.', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 4. Reject repeated consecutive characters (4+)
        if ( $reject_repeated && self::has_repeated_chars( $address ) ) {
            $msg = ! empty( $settings['msg_repeated_chars'] ) 
                ? $settings['msg_repeated_chars'] 
                : esc_html__( 'Address contains repeated characters.', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        // 5. Reject junk keywords
        if ( ! empty( $keywords ) && self::match_junk_keyword( $address, $keywords ) ) {
            $msg = ! empty( $settings['msg_junk_keyword'] ) 
                ? $settings['msg_junk_keyword'] 
                : esc_html__( 'Address contains invalid or junk keywords.', 'on-page-checkout-for-elementor' );
            return [ 'valid' => false, 'error' => $msg ];
        }

        return [ 'valid' => true, 'error' => '' ];
    }
}
