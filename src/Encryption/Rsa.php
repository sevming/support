<?php

namespace Sevming\Support\Encryption;

use \InvalidArgumentException;
use Sevming\Support\Str;

class Rsa
{
    /**
     * Public encrypt the given plaintext.
     *
     * @param string      $plaintext
     * @param string      $publicKey
     * @param int         $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function publicEncrypt(string $plaintext, string $publicKey, int $pad = OPENSSL_PKCS1_PADDING, ?string $code = 'base64')
    {
        $publicKey = self::getPublicKey($publicKey);
        self::validatePad('publicEn', $pad);
        $result = openssl_public_encrypt($plaintext, $encryptedText, $publicKey, $pad);

        return $result ? self::encode($encryptedText, $code) : false;
    }

    /**
     * Private decrypt the given encrypted text.
     *
     * @param string      $encryptedText
     * @param string      $privateKey
     * @param int         $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function privateDecrypt(string $encryptedText, string $privateKey, int $pad = OPENSSL_PKCS1_PADDING, ?string $code = 'base64')
    {
        $privateKey = self::getPrivateKey($privateKey);
        self::validatePad('privateDe', $pad);
        $encryptedText = self::decode($encryptedText, $code);
        $result = openssl_private_decrypt($encryptedText, $decryptedText, $privateKey, $pad);

        return $result ? $decryptedText : false;
    }

    /**
     * Private encrypt the given plaintext.
     *
     * @param string      $plaintext
     * @param string      $privateKey
     * @param int         $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function privateEncrypt(string $plaintext, string $privateKey, int $pad = OPENSSL_PKCS1_PADDING, ?string $code = 'base64')
    {
        $privateKey = self::getPrivateKey($privateKey);
        self::validatePad('privateEn', $pad);
        $result = openssl_private_encrypt($plaintext, $encryptedText, $privateKey, $pad);

        return $result ? self::encode($encryptedText, $code) : false;
    }

    /**
     * Public decrypt the given encrypted text.
     *
     * @param string      $encryptedText
     * @param string      $publicKey
     * @param int         $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function publicDecrypt(string $encryptedText, string $publicKey, int $pad = OPENSSL_PKCS1_PADDING, ?string $code = 'base64')
    {
        $publicKey = self::getPublicKey($publicKey);
        self::validatePad('publicDe', $pad);
        $encryptedText = self::decode($encryptedText, $code);
        $result = openssl_public_decrypt($encryptedText, $decryptedText, $publicKey, $pad);

        return $result ? $decryptedText : false;
    }

    /**
     * Generate sign.
     *
     * @param string      $plaintext
     * @param string      $privateKey
     * @param string|null $code
     * @param int|string  $signAlg
     *
     * @return string|false
     */
    public static function generateSign(string $plaintext, string $privateKey, ?string $code = 'base64', $signAlg = OPENSSL_ALGO_SHA256)
    {
        $privateKey = self::getPrivateKey($privateKey);
        $result = openssl_sign($plaintext, $sign, $privateKey, $signAlg);

        return $result ? self::encode($sign, $code) : false;
    }

    /**
     * Verify sign.
     *
     * @param string      $data
     * @param string      $sign
     * @param string      $publicKey
     * @param string|null $code
     * @param int|string  $signAlg
     *
     * @return bool
     */
    public static function verifySign(string $data, string $sign, string $publicKey, ?string $code = 'base64', $signAlg = OPENSSL_ALGO_SHA256)
    {
        $publicKey = self::getPublicKey($publicKey);
        $sign = self::decode($sign, $code);

        return openssl_verify($data, $sign, $publicKey, $signAlg) === 1;
    }

    /**
     * Get public key.
     *
     * @param string $publicKey
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getPublicKey(string $publicKey)
    {
        if (!Str::endsWith($publicKey, '.pem')) {
            return "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($publicKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        }

        return openssl_pkey_get_public(self::readFile($publicKey));
    }

    /**
     * Get private key.
     *
     * @param string $privateKey
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getPrivateKey($privateKey)
    {
        if (!Str::endsWith($privateKey, '.pem')) {
            return "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($privateKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        }

        return openssl_pkey_get_private(self::readFile($privateKey));
    }

    /**
     * Read file.
     *
     * @param string $file
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function readFile(string $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf("%s not exists.", $file));
        }

        return file_get_contents($file);
    }

    /**
     * Validate pad.
     *
     * @param string $pad
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    public static function validatePad(string $type, string $pad)
    {
        switch ($type) {
            case 'publicEn':
            case 'privateDe':
                $result = in_array($pad, [
                    OPENSSL_PKCS1_PADDING,
                    OPENSSL_SSLV23_PADDING,
                    OPENSSL_PKCS1_OAEP_PADDING,
                    OPENSSL_NO_PADDING
                ]);
                break;
            case 'privateEn':
            case 'publicDe':
                $result = in_array($pad, [
                    OPENSSL_PKCS1_PADDING,
                    OPENSSL_NO_PADDING
                ]);
                break;
            default:
                $result = false;
                break;
        }

        if (!$result) {
            throw new InvalidArgumentException(sprintf('Unsupported %s pad.', $pad));
        }
    }

    /**
     * Encode.
     *
     * @param string      $content
     * @param string|null $code
     *
     * @return string
     */
    public static function encode(string $content, ?string $code)
    {
        if (is_null($code)) {
            return $content;
        }

        switch (strtolower($code)) {
            case 'base64':
                return base64_encode($content);
            case 'hex':
                return bin2hex($content);
            default:
                return '';
        }
    }

    /**
     * Decode.
     *
     * @param string      $content
     * @param string|null $code
     *
     * @return string|bool
     */
    public static function decode(string $content, ?string $code)
    {
        if (is_null($code)) {
            return $content;
        }

        switch (strtolower($code)) {
            case 'base64':
                return base64_decode($content);
            case 'hex':
                return preg_match('/^[0-9a-fA-F]+$/i', $content) ? pack('H*', $content) : '';
            default:
                return '';
        }
    }
}
