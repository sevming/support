<?php

namespace Sevming\Support;

class Xml
{
    /**
     * Parse xml.
     *
     * @param string $xml
     *
     * @return array
     */
    public static function parse(string $xml)
    {
        $backup = libxml_disable_entity_loader(true);
        $result = simplexml_load_string(self::sanitize($xml), 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS);
        libxml_disable_entity_loader($backup);

        return \json_decode(\json_encode($result), true, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Build xml.
     *
     * @param mixed        $data
     * @param string       $root
     * @param string       $item
     * @param string|array $attr
     * @param string       $id
     *
     * @return string
     */
    public static function build(array $data, string $root = 'xml', string $item = 'item', $attr = '', string $id = 'id')
    {
        if (is_array($attr)) {
            $attrArray = [];
            foreach ($attr as $key => $value) {
                $attrArray[] = "{$key}=\"{$value}\"";
            }

            $attr = implode(' ', $attrArray);
        }

        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $xml .= self::data2Xml($data, $item, $id);
        $xml .= "</{$root}>";

        return $xml;
    }

    /**
     * Array to xml.
     *
     * @param array  $data
     * @param string $item
     * @param string $id
     *
     * @return string
     */
    protected static function data2Xml(array $data, string $item = 'item', string $id = 'id')
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && ($attr = " {$id}=\"{$key}\"");
                $key = $item;
            }

            $xml .= "<{$key}{$attr}>";
            if ((is_array($val) || is_object($val))) {
                $xml .= self::data2Xml((array)$val, $item, $id);
            } else {
                $xml .= is_numeric($val) ? $val : "<![CDATA[{$val}]]>";
            }

            $xml .= "</{$key}>";
        }

        return $xml;
    }

    /**
     * Delete invalid characters in XML.
     *
     * @see https://www.w3.org/TR/2008/REC-xml-20081126/#charsets - XML charset range
     * @see http://php.net/manual/en/regexp.reference.escape.php - escape in UTF-8 mode
     *
     * @param string $xml
     *
     * @return string
     */
    public static function sanitize($xml)
    {
        return preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $xml);
    }
}
