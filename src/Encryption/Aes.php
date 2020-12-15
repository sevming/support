<?php

namespace Sevming\Support\Encryption;

use \InvalidArgumentException;

class Aes
{
    /**
     * Encrypt the given plaintext.
     *
     * @param string      $plaintext
     * @param string      $key
     * @param string|null $iv
     * @param int         $options
     * @param string      $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function encrypt(
        string $plaintext,
        string $key,
        ?string $iv = null,
        int $options = OPENSSL_RAW_DATA,
        string $pad = 'pkcs7',
        ?string $code = 'base64'
    ) {
        self::validateKey($key);
        self::validateIv($iv);
        is_null($iv) && ($iv = self::getIv($key));
        $result = openssl_encrypt(self::addPad($plaintext, $pad), self::getMethod($key), $key, $options, $iv);

        return false !== $result ? self::encode($result, $code) : false;
    }

    /**
     * Decrypt the given encrypted text.
     *
     * @param string      $encryptedText
     * @param string      $key
     * @param string|null $iv
     * @param int         $options
     * @param string      $pad
     * @param string|null $code
     *
     * @return string|false
     */
    public static function decrypt(
        string $encryptedText,
        string $key,
        ?string $iv = null,
        int $options = OPENSSL_RAW_DATA,
        string $pad = 'pkcs7',
        ?string $code = 'base64'
    ) {
        self::validateKey($key);
        self::validateIv($iv);
        is_null($iv) && ($iv = self::getIv($key));
        $encryptedText = self::decode($encryptedText, $code);
        $result = openssl_decrypt($encryptedText, self::getMethod($key), $key, $options, $iv);

        return false !== $result ? self::unPad($result, $pad) : false;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public static function validateKey(string $key)
    {
        if (!in_array(mb_strlen($key, '8bit'), [16, 24, 32], true)) {
            throw new InvalidArgumentException(sprintf('Key length must be 16, 24, or 32 bytes; got key len (%s).', strlen($key)));
        }
    }

    /**
     * @param string|null $iv
     *
     * @throws InvalidArgumentException
     */
    public static function validateIv(?string $iv)
    {
        if (!empty($iv) && 16 !== strlen($iv)) {
            throw new InvalidArgumentException('IV length must be 16 bytes.');
        }
    }

    /**
     * @param string $pad
     *
     * @throws InvalidArgumentException
     */
    public static function validatePad(string $pad)
    {
        if (!empty($pad) && !in_array($pad, ['pkcs7'])) {
            throw new InvalidArgumentException(sprintf('Unsupported %s pad.', $pad));
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private static function getIv(string $key)
    {
        return substr(md5($key), 0, 16);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function getMethod(string $key)
    {
        return 'aes-' . (8 * strlen($key)) . '-cbc';
    }

    /**
     * @param string $plaintext
     * @param string $pad
     *
     * @return string
     */
    public static function addPad(string $plaintext, string $pad = 'pkcs7')
    {
        self::validatePad($pad);
        if (empty($pad)) {
            return $plaintext;
        }

        $method = 'add' . ucfirst($pad) . 'Pad';

        return self::$method($plaintext);
    }

    /**
     * @param string $plaintext
     * @param string $pad
     *
     * @return string
     */
    public static function unPad(string $plaintext, string $pad = 'pkcs7')
    {
        self::validatePad($pad);
        if (empty($pad)) {
            return $plaintext;
        }

        $method = 'un' . ucfirst($pad) . 'Pad';

        return self::$method($plaintext);
    }

    /**
     * @param string $plaintext
     * @param int    $blockSize
     *
     * @return string
     */
    public static function addPkcs7Pad(string $plaintext, int $blockSize = 16)
    {
        $padding = $blockSize - (strlen($plaintext) % $blockSize);
        return $plaintext . str_repeat(chr($padding), $padding);
    }

    /**
     * @param string $plaintext
     *
     * @return string
     */
    public static function unPkcs7Pad(string $plaintext)
    {
        if (!empty($plaintext)) {
            $lastA = ord(substr($plaintext, -1));
            $lastC = chr($lastA);
            if (preg_match("/{$lastC}{{$lastA}}$/", $plaintext)) {
                return substr($plaintext, 0, strlen($plaintext) - $lastA);
            }
        }

        return '';
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
