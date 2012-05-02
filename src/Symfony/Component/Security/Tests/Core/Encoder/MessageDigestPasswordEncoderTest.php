<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Tests\Core\Encoder;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class MessageDigestPasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsPasswordValid()
    {
        $encoder = new MessageDigestPasswordEncoder('sha256', false, 1);

        $this->assertTrue($encoder->isPasswordValid(hash('sha256', 'password'), 'password', ''));
    }

    public function testEncodePassword()
    {
        $encoder = new MessageDigestPasswordEncoder('sha256', false, 1);
        $this->assertSame(hash('sha256', 'password'), $encoder->encodePassword('password', ''));

        $encoder = new MessageDigestPasswordEncoder('sha256', true, 1);
        $this->assertSame(base64_encode(hash('sha256', 'password', true)), $encoder->encodePassword('password', ''));

        $encoder = new MessageDigestPasswordEncoder('sha256', false, 2);
        $this->assertSame(hash('sha256', hash('sha256', 'password', true).'password'), $encoder->encodePassword('password', ''));

        if (!function_exists('openssl_get_md_methods')) {
            $this->markTestSkipped('OpenSSL extension is not available.');
        }

        $encoder = new MessageDigestPasswordEncoder('DSA', false, 1);
        $this->assertSame(openssl_digest('password', 'DSA'), $encoder->encodePassword('password', ''));

        $encoder = new MessageDigestPasswordEncoder('DSA', true, 1);
        $this->assertSame(base64_encode(openssl_digest('password', 'DSA', true)), $encoder->encodePassword('password', ''));

        $encoder = new MessageDigestPasswordEncoder('DSA', false, 2);
        $this->assertSame(openssl_digest(openssl_digest('password', 'DSA', true).'password', 'DSA'), $encoder->encodePassword('password', ''));
    }

    /**
     * @expectedException LogicException
     */
    public function testEncodePasswordAlgorithmDoesNotExist()
    {
        $encoder = new MessageDigestPasswordEncoder('foobar');
        $encoder->encodePassword('password', '');
    }
}
