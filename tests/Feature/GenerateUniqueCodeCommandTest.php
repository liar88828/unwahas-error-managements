<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GenerateUniqueCodeCommandTest extends TestCase
{
    public function test_command_generates_a_64_character_code_by_default(): void
    {
        $this->assertSame(0, Artisan::call('error-redirect:generate-code'));

        $output = Artisan::output();
        preg_match('/Generated code:\s*(\S+)/', $output, $matches);

        $this->assertSame(64, strlen($matches[1] ?? ''));
    }

    public function test_command_accepts_a_custom_code_length(): void
    {
        $this->assertSame(0, Artisan::call('error-redirect:generate-code', ['--length' => 32]));

        preg_match('/Generated code:\s*(\S+)/', Artisan::output(), $matches);

        $this->assertSame(32, strlen($matches[1] ?? ''));
    }

    public function test_command_rejects_code_lengths_outside_the_supported_range(): void
    {
        $this->assertSame(1, Artisan::call('error-redirect:generate-code', ['--length' => 8]));
        $this->assertStringContainsString(
            'The code length must be an integer between 16 and 128.',
            Artisan::output(),
        );
    }
}