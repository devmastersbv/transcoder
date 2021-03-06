Transcoder
==========

Forked from ```ddeboer/transcoder```.

Introduction
------------

This is a wrapper around PHP’s `mb_convert_encoding` and `iconv` functions.
This library adds:

* fallback from `mb` to `iconv` for unknown encodings
* conversion of warnings to proper exceptions.

Installation
------------

The recommended way to install the Transcoder library is through 
[Composer](http://getcomposer.org):

```bash
$ composer require ddeboer/transcoder
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Usage
-----

### Basics

Create the right transcoder for your platform and translate some strings:

```php
use Ddeboer\Transcoder\Transcoder;

$transcoder = Transcoder::create();
$result = $transcoder->transcode('España');
```

You can also manually instantiate a transcoder of your liking:

```php
use Ddeboer\Transcoder\MbTranscoder;

$transcoder = new MbTranscoder();

```

Or:

```php
use Ddeboer\Transcoder\IconvTranscoder;

$transcoder = new IconvTranscoder();
```

### Source encoding

By default, the source encoding is detected automatically. However, you get 
much more reliable results when you specify it explicitly:

```php
$transcoder->transcode('España', 'iso-8859-1');
```

### Target encoding

Specify a default target encoding as the first argument to `create()`:
 

```php
use Ddeboer\Transcoder\Transcoder;

$isoTranscoder = Transcoder::create('iso-8859-1');
```
 
Alternatively, specify a target encoding as the third argument in a 
`transcode()` call:

```php
use Ddeboer\Transcoder\Transcoder;

$transcoder->transcode('España', null, 'UTF-8'); 
```

### Error handling

PHP’s `mv_convert_encoding` and `iconv` are inconvenient to use because they 
generate notices and warnings instead of proper exceptions. This library fixes
that:


```php
use Ddeboer\Transcoder\Exception\UndetectableEncodingException;
use Ddeboer\Transcoder\Exception\UnsupportedEncodingException;
use Ddeboer\Transcoder\Exception\IllegalCharacterException;

$input = 'España';
 
try {
    $transcoder->transcode($input);
} catch (UndetectableEncodingException $e) {
    // Failed to automatically detect $input’s encoding 
}

try {
    $transcoder->transcode($input, null, 'not-a-real-encoding');
} catch (UnsupportedEncodingException $e) {
    // ‘not-a-real-encoding’ is an unsupported encoding 
}

try {
    $transcoder->transcode('Illegal quotes: ‘ ’', null, 'iso-8859-1');
} catch (IllegalCharacterException $e) {
    // Curly quotes ‘ ’ are illegal in ISO-8859-1
}
```

To override the MbTranscoder or IconvTranscoder class (to include your own hacks) 
use the following before calling Transcoder::create() for the first time:

```
Transcoder::$iconvClass = "ddeboer\Transcoder\IconvTranscoder";
Transcoder::$mbClass = "ddeboer\Transcoder\MbTranscoder";
```

### Transcoder fallback

In general, `mb_convert_encoding` is faster than `iconv`. However, as `iconv`
supports more encodings than `mb_convert_encoding`, it makes sense to combine 
the two. 

So, the Transcoder returned from `create()`:

* uses `mb_convert_encoding` if the 
  [mbstring](http://php.net/manual/en/book.mbstring.php) PHP extension is 
  installed;
* if not, it uses `iconv` instead if the 
  [iconv](http://php.net/manual/en/book.iconv.php) extension is installed; 
* if both the mbstring and iconv extension are available, the Transcoder will 
  first try `mb_convert_encoding` and fall back to `iconv`.
  
