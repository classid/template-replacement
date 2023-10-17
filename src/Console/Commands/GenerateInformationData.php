<?php

namespace Classid\TemplateReplacement\Console\Commands;

use Classid\TemplateReplacement\TemplateReplacement;
use Illuminate\Console\Command;

class GenerateInformationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'information:show-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all data from template replacement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dd(TemplateReplacement::getAllData());
    }
}
