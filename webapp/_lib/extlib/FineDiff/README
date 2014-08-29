Files
-----

finediff.php    : where the FineDiff class is defined, standalone, no dependencies

viewdiff-ex.php : demo page, render diff in HTML, show opcodes and other stats
sample_from.txt : sample text file for demo
sample_to.txt   : sample text file for demo
[Text_Diff]     : required for viewdiff-ex.php demo page,
                  available at http://download.pear.php.net/package/Text_Diff-1.1.1.tgz

viewdiff.php    : demo page, plainly render diff in HTML

Demo can be seen online at:
http://www.raymondhill.net/finediff/


Main Class: FineDiff
--------------------

A class which implements a high-granularity (though selectable) diff engine.
Up to character-level diffs can be computed.

A diff is described by a string of opcodes, which can be stored
and combined later with the left hand string to re-create the
right-hand string.

The code was started from scratch, with particular attention to
performance. The key to the performance of the FineDiff engine is
to incrementally increase the granularity.


Usage
-----

The simplest way to create a diff of two strings is as follow:

  include 'finediff.php';
  $opcodes = FineDiff::getDiffOpcodes($from_text, $to_text /, default granularity is set to character */);
  // store opcodes for later use...

Later, $to_text can be re-created from $from_text using $opcodes as follow:

  include 'finediff.php';
  $to_text = FineDiff::renderToTextFromOpcodes($from_text, $opcodes);

If you wish a different granularity from the default one, you can use
one of the provided stock granularity stacks:

  FineDiff::$paragraphGranularity
  FineDiff::$sentenceGranularity
  FineDiff::$wordGranularity
  FineDiff::$characterGranularity (default)

A basic HTML renderer is provided:

  echo FineDiff::renderDiffToHTMLFromOpcodes($from_text, $opcodes);


Customize
---------

It is possible to customize the engine by providing a custom "granularity stack"
at your own risk.

It is also possible to provide a custom renderer through a user supplied callback
function/method:

  FineDiff::renderFromOpcodes($from, $opcodes, $callback);


FAQ
---

* Does it work with UTF-8?
  As of now, the code assume single-byte characters. To use UTF-8 text, you can
  always convert the encoding using mb_convert_encoding():
    ...
    $from_text = mb_convert_encoding($from_text_utf8, 'HTML-ENTITIES', 'UTF-8');
    $to_text = mb_convert_encoding($to_text_utf8, 'HTML-ENTITIES', 'UTF-8');
    $diff_opcodes = FineDiff::getDiffOpcodes($from_text, $to_text);
    ...

  If ever you want to re-generate the $to_text_utf8 from the $from_text_utf8:
    ...
    $from_text = mb_convert_encoding($from_text_utf8, 'HTML-ENTITIES', 'UTF-8');
    $to_text = FineDiff::renderToTextFromOpcodes($from_text, $diff_opcodes);
    $to_text_utf8 = mb_convert_encoding($to_text, 'UTF-8', 'HTML-ENTITIES');
    ....	


License
-------

Copyright (c) 2011 Raymond Hill (http://raymondhill.net/blog/?p=441)

Licensed under The MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

