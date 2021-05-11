<?php
namespace App\Shell;

use Cake\Console\Shell;

class ChellShell extends Shell
{
    public $tasks = ['CleanTags', 'MakeSuperAdmin'];

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription('This shell contains various administrative tools for the management of Chell.')
            ->addSubcommand('cleanTags', [
                'help' => 'Cleans the database from currently unused tags',
                'parser' => $this->CleanTags->getOptionParser(),
            ])
            ->addSubcommand('makeSuperAdmin', [
                'help' => 'Promotes a user to super admin.',
                'parser' => $this->MakeSuperAdmin->getOptionParser(), //TODO: Username is required
            ]);

        return $parser;
    }
}