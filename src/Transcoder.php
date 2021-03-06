<?php

namespace Ddeboer\Transcoder;

use Ddeboer\Transcoder\Exception\ExtensionMissingException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;

class Transcoder implements TranscoderInterface
{

    private static $chain;
    public static $iconvClass = "Ddeboer\Transcoder\IconvTranscoder";
    public static $mbClass = "Ddeboer\Transcoder\MbTranscoder";
    public static $defaultEncoding = "UTF-8";

    /**
     * @var TranscoderInterface[]
     */
    private $transcoders = [];

    public function __construct(array $transcoders)
    {
        $this->transcoders = $transcoders;
    }

    /**
     * {@inheritdoc}
     */
    public function transcode($string, $from = null, $to = null)
    {
        foreach ($this->transcoders as $transcoder) {
            try {
                return $transcoder->transcode($string, $from, $to);
            } catch (UnsupportedEncodingException $e) {
                // Ignore as long as the fallback transcoder is all right
            }
        }

        throw $e;
    }

    /**
     * Create a transcoder
     * 
     * @param string $defaultEncoding
     *
     * @return TranscoderInterface
     *
     * @throws ExtensionMissingException
     */
    public static function create($defaultEncoding = null)
    {
        if (!$defaultEncoding) {
            $defaultEncoding = self::$defaultEncoding;
        }
        if (isset(self::$chain[$defaultEncoding])) {
            return self::$chain[$defaultEncoding];
        }

        $transcoders = [];
        if (self::$mbClass) {
            try {
                $transcoders[] = new self::$mbClass($defaultEncoding);
            } catch (ExtensionMissingException $mb) {
                // Ignore missing mbstring extension; fall back to iconv
            }
        }
        
        try {
            $transcoders[] = new self::$iconvClass($defaultEncoding);
        } catch (ExtensionMissingException $iconv) {
            // Neither mbstring nor iconv
            throw $iconv;
        }

        self::$chain[$defaultEncoding] = new self($transcoders);

        return self::$chain[$defaultEncoding];
    }

}
