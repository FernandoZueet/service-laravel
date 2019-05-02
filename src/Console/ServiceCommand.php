<?php

/**
 * This file is part of the FzService package
 *
 * @link http://github.com/fernandozueet/service-laravel
 * @copyright 2019
 * @license MIT License
 * @author Fernando Zueet <fernandozueet@hotmail.com>
 */

namespace FzService\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use FzService\Traits\CommandTrait;

class ServiceCommand extends Command
{
	use CommandTrait;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fzservice:make:service {service : Service name - Ex: User}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a class of service';

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new command instance.
	 *
	 * @param Filesystem $files
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();

		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		//Get inputs
		$name = $this->argument('service');

		//Paths
		$path = app_path('Services/') . $name . 'Service.php';
		if (!$this->files->isDirectory(dirname($path))) {
			$this->files->makeDirectory(dirname($path), 0755, true);
		}

		if ($this->confirm('Want to create a complete service?', true)) {

			$pathModel = $this->ask('Model directory', 'App\\Models');

			//mount template service
			$template = $this->replaceTemplate([
				['field' => 'name', 'value' => $name],
				['field' => 'model', 'value' => $pathModel],
			], file_get_contents(__DIR__ . '/stubs/service/service.stub'));
		}else{

			//mount template service
			$template = $this->replaceTemplate([
				['field' => 'name', 'value' => $name],
				['field' => 'model', 'value' => $pathModel],
			], file_get_contents(__DIR__ . '/stubs/service/service-clean.stub'));
		}

		//create
		if (!$this->files->exists($path)) {
			$this->files->put($path, $template);
			$this->info("{$name}Service successfully generated");
		} else {
			$this->info("{$name}Service already exists");
		}

		//create resource
		if ($this->confirm('Want to create a resource class?', true)) {
			$this->call('fzservice:make:resource', [ 'resource' => $name ]);
		}
	}

}
