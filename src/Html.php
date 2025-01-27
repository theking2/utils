<?php declare(strict_types=1);

namespace Kingsoft\Utils;

if( !defined( 'DEBUG' ) ) {
  define( 'DEBUG', true );
}

class Html
{

  // MARK: - param

  /**
   * Check if all required parameters are set
   * @deprecated version 1.1.0
   * Use checkParams instead
   */
  static public function check_request_params( array $params, ?bool $die = true ): bool
  {
    $params_check = array_intersect( array_keys( $_REQUEST ), $params );
    $result       = count( $params_check ) !== count( $params );
    if( $die and !$result )
      trigger_error( "wrong number of parameters", E_USER_ERROR );
    return $result;
  }

  /**
   * Check if all required parameters are set
   *
   * @param $required this params should be in the $request. Side effect: dies with trigger_error
   * @param $request the request to test ($_POST, $_GET, $_REQUEST)
   * @param $exact true if a exact parameter match
   * @param $die die if wrong number

   */
  static public function checkParams( array $required, array $request, ?bool $exact = true, bool $die = true ): bool
  {
    $check_count_exact = fn( $required, $intersect ) => ( count( $required ) === count( $intersect ) );
    $check_count       = fn( $required, $intersect ) => ( count( $required ) <= count( $intersect ) );

    $intersect = array_intersect( array_keys( $request ), $required );
    if( !$die ) {
      return $exact && $check_count_exact( $required, $intersect ) ||
        $check_count( $required, $intersect );
    }
    if( ( $exact && !$check_count_exact( $required, $intersect ) ) ||
      !$check_count( $required, $intersect ) ) {
      http_response_code( 403 );
      trigger_error( "Request incomplete", E_USER_ERROR );
    }

    return true;
  }

  // MARK: - Encoding

  /**
   * convert bin to url friendly base64
   * translating + to - and / to _
   * @param $data
   */
  static public function base64url_encode( $data ): string
  {
    return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
  }
  /**
   * convert url friendly base64 to bin
   * translating - to + and _ to /
   */
  static public function base64url_decode( $data ): string
  {
    return base64_decode( strtr( $data, '-_', '+/' ) . str_repeat( '=', 3 - ( 3 + strlen( $data ) ) % 4 ) );
  }

  // MARK: - Wrappers
  /**
   * Wrap a text in a tag
   * @param $tag Tag to wrap arount text
   * @param $text Text to wrap tag around
   * @param $class optional class or classes string
   * @param $id optional id string
   */
  static public function wrap_tag( string $tag, string $text, ?string $class = null, ?string $id = null ): string
  {
    return
      "<$tag" .
        // if class is set include a class="" section
      ( $class ? " class=\"$class\"" : '' ) .

        // if id is set include a id="" section
      ( $id ? " id=\"$id\"" : '' ) .

      ">$text</$tag>";
  }

  /**
   * Create option entry setting the selected value
   */
  static public function option_tag( string $text, mixed $value, string $var ): string
  {
    return
      '<option ' .
        // set attribute selected when $var has value $value
      ( $var === $value ? 'selected ' : '' ) .
      'value="' . $value . '">' . $text .
      '</option>' . PHP_EOL;
  }

  // MARK: - Session

  static public function setSession(): void
  {
    if( !defined( 'NO_SESSION' ) ) {

      session_start( [ 
        'name' => DEBUG ? 'SessionId' : '__Secure-SessionId',
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'referer_check' => $_SERVER['HTTP_HOST'],
      ] );
    }
  }
}