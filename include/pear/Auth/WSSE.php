<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * WSSE authentication class
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy
 * the PHP License and are unable to obtain it through the web,
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Auth
 * @package   Auth_WSSE
 * @author    Hideyuki SHIMOOKA <shimooka@doyouphp.jp>
 * @copyright 2009 Hideyuki SHIMOOKA
 * @license   http://www.php.net/license/3_01.txt The PHP License, version 3.01
 * @version   SVN:$Id: WSSE.php 977 2009-06-29 15:15:02Z shimooka $
 * @link      http://openpear.org/package/Auth_WSSE
 */

/**
 * Auth_WSSE class
 *
 * @category  Auth
 * @package   Auth_WSSE
 * @author    Hideyuki SHIMOOKA <shimooka@doyouphp.jp>
 * @copyright 2009 Hideyuki SHIMOOKA
 * @license   http://www.php.net/license/3_01.txt The PHP License, version 3.01
 * @version   Release: @package_version@
 * @link      http://openpear.org/package/Auth_WSSE
 */
class Auth_WSSE
{
    /**
     * user name
     */
    private $username;

    /**
     * password digest
     */
    private $digest;

    /**
     * nonce
     */
    private $nonce;

    /**
     * created time in UTC
     */
    private $created;

    /**
     * constructor
     *
     * @param string user     name
     * @param string password
     * @param string nonce    without Base64 encode
     * @param string create   time (UTC) in RFC3339 format
     */
    public function __construct($username, $password, $nonce = null, $created = null) {
        $this->username = $username;
        $this->nonce = is_null($nonce) ? $this->generateNonce() : $nonce;
        $this->created = is_null($created) ? $this->generateCreated() : $created;

        $this->digest = sha1($this->nonce . $this->created . $password, true);
    }

    /**
     * return the current user name
     *
     * @return the user name
     */
    public function getUserName() {
        return $this->username;
    }

    /**
     * return the current password digest
     *
     * @param  boolean return in Base64 encoded or not. default is true.
     * @return the     password digest
     */
    public function getDigest($encode = true) {
        return $encode ? base64_encode($this->digest) : $this->digest;
    }

    /**
     * return the current nonce
     *
     * @param  boolean return in Base64 encoded or not. default is true.
     * @return the     nonce
     */
    public function getNonce($encode = true) {
        return $encode ? base64_encode($this->nonce) : $this->nonce;
    }

    /**
     * return created time (UTC) in RFC3339 format
     *
     * @return the created time (UTC) in RFC3339 format
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * return X-WSSE header
     *
     * @return the X-WSSE header
     */
    public function getHeader() {
        return sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $this->username,
            $this->getDigest(),
            base64_encode($this->nonce),
            $this->created);
    }

    /**
     * generate nonce
     *
     * @return binary  return new nonce
     * @access private
     */
    private function generateNonce() {
        return pack('H*', sha1(md5(microtime() . mt_rand() . uniqid(mt_rand(), true))));
    }

    /**
     * return created time (UTC) in RFC3339 format
     *
     * @return string  create time (UTC) in RFC3339 format
     * @access private
     */
    private function generateCreated() {
        return gmdate('Y-m-d\TH:i:s\Z');
    }

    /**
     * parse X-WSSE header and return in assosiative array
     *
     * the keys of returing array are as following:
     *
     * -username
     * -digest
     * -nonce
     * -created
     *
     * @param  string X-WSSE header
     * @return array  the result in assosiative array
     * @throw  RuntimeException if parse failed
     */
    public static function parseHeader($header) {
        if (preg_match('#UsernameToken Username="(?<username>[^"]+)", PasswordDigest="(?<digest>[^"]+)", Nonce="(?<nonce>[^"]+)", Created="(?<created>[^"]+)"#', $header, $matches) > 0) {
            return $matches;
        }
        throw new RuntimeException('parsing X-WSSE header failed');
    }
}
