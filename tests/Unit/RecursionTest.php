<?php

namespace Unit;

use FlexBlade\Blade;
use FlexBlade\Blade\BladeComponents;
use FlexBlade\Exceptions\CircularReferenceException;
use FlexBlade\Exceptions\MaxDepthExceededException;
use PHPUnit\Framework\TestCase;

class RecursionTest extends TestCase
{

    public function setUp(): void
    {
        if(!is_dir(TEMP_INSTALL."/views")){
            mkdir(TEMP_INSTALL."/views");
        }

        if(!is_dir(TEMP_INSTALL."/views/components")){
            mkdir(TEMP_INSTALL."/views/components");
        }

        define('VIEWS',TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR);

        // Reset max depth to default for each test
        BladeComponents::setMaxDepth(10);
    }

    public function test_circular_reference_detection(): void
    {
        // Create components that reference each other
        $this->generate_circular_component_a();
        $this->generate_circular_component_b();
        $this->generate_circular_test_page();

        $this->expectException(CircularReferenceException::class);
        $this->expectExceptionMessage('Circular component reference detected: components.circular-a -> components.circular-b -> components.circular-a');

        try {
            Blade::render("circular_test_page", []);
        } catch (CircularReferenceException $e) {
            // Verify the component stack is correct
            $expectedStack = ['components.circular-a', 'components.circular-b', 'components.circular-a'];
            $this->assertEquals($expectedStack, $e->componentStack);
            throw $e; // Re-throw for the expectException
        }
    }

    public function test_max_depth_exceeded(): void
    {
        // Set a low max depth for testing
        BladeComponents::setMaxDepth(3);

        // Create deeply nested components
        $this->generate_deep_nesting_components();
        $this->generate_deep_nesting_test_page();

        $this->expectException(MaxDepthExceededException::class);
        $this->expectExceptionMessage('Component nesting depth (4) exceeds maximum (3)');

        try {
            Blade::render("deep_nesting_test_page", []);
        } catch (MaxDepthExceededException $e) {
            // Verify exception details
            $this->assertEquals(4, $e->currentDepth);
            $this->assertEquals(3, $e->getMaxDepth());

            $expectedStack = ['components.level-1', 'components.level-2', 'components.level-3', 'components.level-4'];
            $this->assertEquals($expectedStack, $e->getComponentStack());

            throw $e; // Re-throw for the expectException
        }
    }

    public function test_successful_nested_components(): void
    {
        // Create a reasonable nesting scenario that should work
        $this->generate_successful_nested_components();
        $this->generate_successful_nested_test_page();
        $layout = VariableProcessingTest::generate_basic_layout_file();
        $this->assertTrue(file_exists($layout));

        try {
            $output = Blade::render("successful_nested_test_page", []);
        } catch (\Exception $e) {
            $this->fail("Successful nesting should not throw exception: " . $e->getMessage());
        }

        $this->assertNotNull($output);
        $this->assertStringContainsString('<div class="card">', $output);
        $this->assertStringContainsString('<h2>Test Title</h2>', $output);
        $this->assertStringContainsString('<button class="btn btn-primary">Click Me</button>', $output);

        // Verify nested structure
        $this->assertStringContainsString('<div class="card"><h2>Test Title</h2><div class="content"><button class="btn btn-primary">Click Me</button></div></div>', $output);
    }

    public function test_processing_stack_cleanup_on_error(): void
    {
        // Verify that processing stack is cleaned up properly when an error occurs
        $this->generate_error_component();
        $this->generate_error_test_page();

        // Stack should be empty initially
        $this->assertEmpty(BladeComponents::getProcessingStack());

        try {
            Blade::render("error_test_page", []);
            $this->fail("Expected an exception to be thrown");
        } catch (\Exception $e) {
            // After error, stack should be cleaned up
            $this->assertEmpty(BladeComponents::getProcessingStack(), "Processing stack should be empty after error");
        }
    }

    public function test_max_depth_configuration(): void
    {
        // Test that max depth can be configured
        $originalMaxDepth = 10; // Default

        BladeComponents::setMaxDepth(5);
        $this->generate_configurable_depth_components(6); // One more than max
        $this->generate_configurable_depth_test_page();

        $this->expectException(MaxDepthExceededException::class);

        try {
            Blade::render("configurable_depth_test_page", []);
        } catch (MaxDepthExceededException $e) {
            $this->assertEquals(5, $e->getMaxDepth());
            throw $e;
        }
    }

    // Helper methods to generate test files

    private function generate_circular_component_a(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/circular-a.blade.php";
        $content = '<div class="component-a"><x-components.circular-b/></div>';
        file_put_contents($file, $content);
    }

    private function generate_circular_component_b(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/circular-b.blade.php";
        $content = '<div class="component-b"><x-components.circular-a/></div>';
        file_put_contents($file, $content);
    }

    private function generate_circular_test_page(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."circular_test_page.blade.php";
        $content = '<x-components.circular-a/>';
        file_put_contents($file, $content);
    }

    private function generate_deep_nesting_components(): void
    {
        // Level 1 -> Level 2 -> Level 3 -> Level 4 (exceeds max depth of 3)
        $components = [
            'level-1' => '<div class="level-1"><x-components.level-2/></div>',
            'level-2' => '<div class="level-2"><x-components.level-3/></div>',
            'level-3' => '<div class="level-3"><x-components.level-4/></div>',
            'level-4' => '<div class="level-4">Deep content</div>'
        ];

        foreach ($components as $name => $content) {
            $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/{$name}.blade.php";
            file_put_contents($file, $content);
        }
    }

    private function generate_deep_nesting_test_page(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."deep_nesting_test_page.blade.php";
        $content = '<x-components.level-1/>';
        file_put_contents($file, $content);
    }

    private function generate_successful_nested_components(): void
    {
        // Button component
        $buttonFile = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/button.blade.php";
        $buttonContent = '<button class="btn btn-{{$type ?? \'primary\'}}">{{$children ?? \'Button\'}}</button>';
        file_put_contents($buttonFile, $buttonContent);

        // Card component that uses button
        $cardFile = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/card.blade.php";
        $cardContent = '<div class="card"><h2>{{$title}}</h2><div class="content">{{$children}}</div></div>';
        file_put_contents($cardFile, $cardContent);
    }

    private function generate_successful_nested_test_page(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."successful_nested_test_page.blade.php";
        $content = <<<'CONTENT'
        @extends('layout')
        <x-components.card title="Test Title">
            <x-components.button type="primary">Click Me</x-components.button>
        </x-components.card>
        CONTENT;
        file_put_contents($file, $content);
    }

    private function generate_error_component(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/error-component.blade.php";
        // Component that references a non-existent variable to cause an error
        $content = '<div>{{$nonExistentVariable}}</div>';
        file_put_contents($file, $content);
    }

    private function generate_error_test_page(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."error_test_page.blade.php";
        $content = '<x-components.error-component/>';
        file_put_contents($file, $content);
    }

    private function generate_configurable_depth_components(int $depth): void
    {
        for ($i = 1; $i <= $depth; $i++) {
            $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."components/depth-{$i}.blade.php";

            if ($i === $depth) {
                $content = "<div class=\"depth-{$i}\">Final level</div>";
            } else {
                $nextLevel = $i + 1;
                $content = "<div class=\"depth-{$i}\"><x-components.depth-{$nextLevel}/></div>";
            }

            file_put_contents($file, $content);
        }
    }

    private function generate_configurable_depth_test_page(): void
    {
        $file = TEMP_INSTALL.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."configurable_depth_test_page.blade.php";
        $content = '<x-components.depth-1/>';
        file_put_contents($file, $content);
    }
}