<?php

namespace Unit;

use LucentBlade\Minifier\Minify;
use PHPUnit\Framework\TestCase;

class CSSMinifierTest extends TestCase
{

    private Minify $minify;

    public function setUp(): void
    {
        $this->minify = new Minify();
    }

    public function test_css_file_minification(): void
    {
        $file = $this->generate_standard_test_css_file();
        $this->assertTrue(file_exists($file));

        $originalSize = filesize($file);
        $this->assertTrue($originalSize > 0 && $originalSize !== false);

        $output = $this->minify::minifyCSS(file_get_contents($file));
        $newFile = TEMP_INSTALL."/test_file.min.css";

        $this->assertTrue(file_put_contents($newFile,$output) !== false);

        $newSize = filesize($newFile);

        $this->assertLessThan($originalSize, $newSize);

        echo "Asserting ".$originalSize." bytes is greater than ".$newSize." bytes\n";
    }

    public function test_css_block_once(): void
    {
        $file =  $this->generate_once_css_test_html_file();

        $this->assertTrue(file_exists($file));

        $originalSize = filesize($file);
        $this->assertTrue($originalSize > 0 && $originalSize !== false);

        $output = $this->minify->minifyHtmlDocument(file_get_contents($file));

        $newFile = TEMP_INSTALL."/test_once_file.min.html";

        $this->assertTrue(file_put_contents($newFile,$output) !== false);

        $newSize = filesize($newFile);

        $this->assertLessThan($originalSize, $newSize);

        echo "Asserting ".$originalSize." bytes is greater than ".$newSize." bytes\n";
    }

    public function test_css_block_scope() :void
    {
        $file =  $this->generate_scope_css_test_html_file();

        $this->assertTrue(file_exists($file));

        $originalSize = filesize($file);
        $this->assertTrue($originalSize > 0 && $originalSize !== false);

        $output = $this->minify->minifyHtmlDocument(file_get_contents($file));

        $newFile = TEMP_INSTALL."/test_scope_file.min.html";

        $this->assertTrue(file_put_contents($newFile,$output) !== false);

        $newSize = filesize($newFile);

        $this->assertLessThan($originalSize, $newSize);

        echo "Asserting ".$originalSize." bytes is greater than ".$newSize." bytes\n";
    }

    public function generate_standard_test_css_file(): string
    {
        $file = TEMP_INSTALL."/test_standard_css_file.css";

        $content = <<<CSS
        body{  
            font-family: Georgia, Arial, Helvetica, Sans-serif,serif;
            font-size: 15px;
            color: #000;
            background: #fff;
        }
         
        h1 {
            font-size: 50px;
        }
         
        h3 {
            font-size: 22px;
        }
         
        #content-wrapper {
            width :960px;
            text-align: left;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
             
        }
         
        #header{
            width: 960px;
            height: 120px;
        }
         
        #post-content {
            float: left;
            width: 600px;
            padding: 0 15px 15px 15px;
        }
         
        .sidebar{
            float: left;
            width: 300px;
            margin: 0 0 0 15px;
            font-size: 15px;
            list-style: none;
        }
         
        #footer{
            height: 60px;
            background: #fff;
            padding: 15px;
            clear:both;
        }
        CSS;

        if(file_put_contents($file,$content) === false) {
            $this->assertFalse("Failed to write file");
        }

        return $file;
    }

    public function generate_once_css_test_html_file(): string
    {
        $file = TEMP_INSTALL."/test_once_css_file.html";

        $content = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>HTML 5 Boilerplate</title>
          </head>
          <body>
            <style data-minify-once="true">
            
            .form-input
            {
                background-color: red;
                color: white;
            }
            
            .form-input:hover
            {
                background-color: white;
                color: red;
                cursor:pointer;
            }
            </style>
            
            <style data-minify-once="true">
            
            .form-input
            {
                background-color: red;
                color: white;
            }
            
            .form-input:hover
            {
                background-color: white;
                color: red;
                cursor:pointer;
            }
            </style>
            
            <style data-minify-once="true">
            
            .form-input
            {
                background-color: red;
                color: white;
            }
            
            .form-input:hover
            {
                background-color: white;
                color: red;
                cursor:pointer;
            }
            </style>
          </body>
        </html>
        HTML;

        if(file_put_contents($file,$content) === false) {
            $this->assertFalse("Failed to write file");
        }

        return $file;
    }
    public function generate_scope_css_test_html_file(): string
    {
        $file = TEMP_INSTALL."/test_scope_css_file.html";

        $content = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>HTML 5 Boilerplate</title>
          </head>
          <body>
            <style data-minify-scope="form-components">
            
            .form-input
            {
                background-color: red;
                color: white;
            }
            
            .form-input:hover
            {
                background-color: white;
                color: red;
                cursor:pointer;
            }
            </style>
            
            <style data-minify-scope="form-components">
            
            .form-dropdown
            {
                border: red;
                color: gray;
            }
            
            .form-dropdown:hover
            {
                background-color: white;
                color: red;
                cursor:pointer;
            }
            </style>
          </body>
        </html>
        HTML;

        if(file_put_contents($file,$content) === false) {
            $this->assertFalse("Failed to write file");
        }

        return $file;
    }


}