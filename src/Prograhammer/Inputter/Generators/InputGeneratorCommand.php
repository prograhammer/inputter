<?php namespace Prograhammer\EasyInput;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GridGeneratorCommand extends Command{
	
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'EasyInput:generate';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate a new EasyInput datagrid PHP class";
 
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->line('Welcome to the EasyInput generator.');
		
        // Get the name arguments and the type option from the input instance.
        $name = $this->argument('name');
 
        $age = $this->option('type');	
		
			
    }
	
   /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Name of the class to be created'),
        );
    }
 
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('type', null, InputOption::VALUE_REQUIRED, 'jqgrid or htmlgrid')
        );
    }	
 
}	