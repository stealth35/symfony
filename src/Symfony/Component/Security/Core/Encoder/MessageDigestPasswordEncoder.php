<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Encoder;

/**
 * MessageDigestPasswordEncoder uses a message digest algorithm.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class MessageDigestPasswordEncoder extends BasePasswordEncoder
{
    private $algorithm;
    private $encodeHashAsBase64;
    private $iterations;

    /**
     * Constructor.
     *
     * @param string  $algorithm          The digest algorithm to use
     * @param Boolean $encodeHashAsBase64 Whether to base64 encode the password hash
     * @param integer $iterations         The number of iterations to use to stretch the password hash
     */
    public function __construct($algorithm = 'sha512', $encodeHashAsBase64 = true, $iterations = 5000)
    {
        $this->algorithm = $algorithm;
        $this->encodeHashAsBase64 = $encodeHashAsBase64;
        $this->iterations = $iterations;
    }

    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt)
    {
        $inHash = in_array($this->algorithm, hash_algos(), true);
        $inOpenssl = function_exists('openssl_get_md_methods') ? in_array($this->algorithm, openssl_get_md_methods(true), true) : false;

        if (!$inHash && !$inOpenssl) {
            throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
        }

        $salted = $this->mergePasswordAndSalt($raw, $salt);
        $digest = $inHash ?  hash($this->algorithm, $salted, true) : openssl_digest($salted, $this->algorithm, true);

        // "stretch" hash
        for ($i = 1; $i < $this->iterations; $i++) {
            $digest = $inHash ?  hash($this->algorithm, $digest.$salted, true) : openssl_digest($digest.$salted, $this->algorithm, true);
        }

        return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }
}
