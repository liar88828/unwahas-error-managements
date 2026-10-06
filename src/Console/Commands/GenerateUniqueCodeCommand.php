<?php

namespace Unwahas\ErrorRedirect\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUniqueCodeCommand extends Command
{
    protected $signature = 'error-redirect:generate-code {--length=64 : Length of the generated code (16-128)}';

    protected $description = 'Generate a secure unique code for Error Redirect';

    public function handle(): int
    {
        $length = filter_var($this->option('length'), FILTER_VALIDATE_INT);

        if ($length === false || $length < 16 || $length > 128) {
            $this->components->error('The code length must be an integer between 16 and 128.');

            return self::FAILURE;
        }

        $this->components->info('Generated code:');
        $this->line(Str::random($length));

        return self::SUCCESS;
    }
}