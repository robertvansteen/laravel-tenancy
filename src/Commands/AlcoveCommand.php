<?php

namespace Alcove\Alcove\Commands;

use Illuminate\Console\Command;

class AlcoveCommand extends Command
{
    public $signature = 'alcove';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
