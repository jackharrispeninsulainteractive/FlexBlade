<?php

namespace Unit;

use Exception;
use FlexBlade\Blade\BladeCompiler;
use PHPUnit\Framework\TestCase;

class VariableProcessingTest extends TestCase
{

    private BladeCompiler $blade;

    public function setUp(): void
    {
        $this->blade = new BladeCompiler();

        if(!is_dir(TEMP_INSTALL."/views")){
            mkdir(TEMP_INSTALL."/views");
        }

        define('VIEWS',TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR);
    }


    public function test_invalid_or_missing_view() : void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unable to load layout file from " . VIEWS . "MyView.blade.php");
        $this->blade->render("MyView");
    }
    public function test_render_with_missing_layout(): void
    {
        $layout = $this->generate_basic_layout_file();
        unlink($layout);
        $this->assertFileDoesNotExist($layout);

        $page = $this->generate_basic_page_file();
        $this->assertFileExists($page);

        try {
            $this->blade->render("home");
        }catch (Exception $e){
            $this->assertEquals("Unable to load layout file from ".$layout,$e->getMessage());
        }
    }


    public function test_render_with_valid_layout(): void
    {
        $layout = $this->generate_basic_layout_file();
        $this->assertFileExists($layout);

        $page = $this->generate_basic_page_file();
        $this->assertFileExists($page);

        // Provide data for the template variables
        $data = [
            'title' => 'Test Title',
            'user' => ['name' => 'John Doe']
        ];

        try {
            $output = $this->blade->render("home", $data);
        }catch (Exception $e){
            $this->fail($e->getMessage());
        }

        // Assert the output contains expected content
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Test Title', $output);
        $this->assertStringContainsString('Welcome John Doe', $output);
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
    }


    public function generate_basic_layout_file(): string
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."layout.blade.php";

        $content = <<<'CONTENT'
        <!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>{{$title}}</title>
          </head>
          <body>
            @yield('content')
          </body>
        </html>
        CONTENT;

        if(file_put_contents($file,$content) === false) {
            $this->assertFalse("Failed to write file");
        }

        return $file;
    }

    public function generate_basic_page_file(): string
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."home.blade.php";

        $content = <<<'CONTENT'
        @extends('layout')
        <h1>Welcome {{$user['name']}}</h1>
        CONTENT;

        if(file_put_contents($file,$content) === false) {
            $this->assertFalse("Failed to write file");
        }

        return $file;
    }



}