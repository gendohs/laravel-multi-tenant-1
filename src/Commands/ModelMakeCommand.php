<?php

namespace Sellmate\Laravel\MultiTenant\Commands;

use Illuminate\Support\Str;
use Illuminate\Foundation\Console\ModelMakeCommand as BaseCommand;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends BaseCommand
{
    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $args = [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ];

        if ($this->input->getOption('tenant')) $args['--tenant'] = TRUE;
        elseif ($this->input->getOption('system')) $args['--system'] = TRUE;

        $this->call('make:migration', $args);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['system', 'S', InputOption::VALUE_NONE, "Create a new Eloquent model class for system database."],
            ['tenant', 'T', InputOption::VALUE_NONE, "Create a new Eloquent model class for tenant database."],
        ]);
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        if (!$this->input->getOption('tenant') && !$this->input->getOption('system')) return parent::buildClass($name);

        $stub = $this->files->get($this->getStub());
        
        $connection = '';
        if ($this->input->getOption('system')) $connection = Config::get('multitenancy.system-connection', 'system');
        elseif ($this->input->getOption('tenant')) $connection = Config::get('multitenancy.tenant-connection', 'tenant');

        return $this->replaceConnection($stub, $connection)->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function replaceConnection(&$stub, $connection)
    {
        $stub = str_replace(['DummyConnectionName', '{{ connection }}', '{{connection}}'], $connection, $stub);

        return $this;
    }
}
