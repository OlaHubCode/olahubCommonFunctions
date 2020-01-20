<?php

namespace Laravel\Lumen\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class createModuleCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Serve the application on the PHP development server";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {

        //$newModuleName single from module name
        //$moduleName plural from the name
        //$mTableName new module table

        $newModuleName = $this->input->getArgument('moduleName');
        if (!$newModuleName) {
            return $this->error(sprintf("\n\nYou should write new module name\n"));
        }

        $defaultName = "DTemplate";
        $pluralDefaultName = $defaultName."s";
        $dTableName = 'dtemplate';
        $mode = 0775;
        $file = base_path('modules');
        if (!file_exists($file)) {
            $moduleFile = mkdir($file, $mode, TRUE);
        }
        $moduleName = \OlaHub\Libraries\InflectLibrary::pluralize($newModuleName);
        $mTableName = $moduleName;
        $newModule = base_path("modules/$moduleName");
        if (file_exists($newModule)) {
            return $this->error(sprintf("\n\nThe module ($moduleName) already exist\n"));
        }
        $old_umask = umask(0);
        mkdir($newModule, $mode);
        
        mkdir("$newModule/Controllers", $mode);
        $controllerName = ucfirst($moduleName);
        $singularControllerName = ucfirst($newModuleName);
        $file = fopen("$newModule/Controllers/$controllerName" . "Controller.php", "w+");
        $controllerContent = file_get_contents(__DIR__ . "/templatesData/Controllers/$pluralDefaultName" . "Controller.php");
        $controllerContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$controllerName,$singularControllerName,$mTableName], $controllerContent);
        fwrite($file, $controllerContent);
        fclose($file);
        
        $trashControllerName = ucfirst($moduleName);
        $singularTrashControllerName = ucfirst($newModuleName);
        $file = fopen("$newModule/Controllers/$controllerName" . "TrashController.php", "w+");
        $trashControllerContent = file_get_contents(__DIR__."/templatesData/Controllers/$pluralDefaultName" . "TrashController.php");
        $trashControllerContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$controllerName,$singularControllerName,$mTableName], $trashControllerContent);
        fwrite($file, $trashControllerContent);
        fclose($file);

        mkdir("$newModule/Models", $mode);
        $modelName = ucfirst($moduleName);
        $singularModelName = ucfirst($newModuleName);
        $file = fopen("$newModule/Models/$singularModelName" . ".php", "w+");
        $modelContent = file_get_contents(__DIR__."/templatesData/Models/$defaultName" . ".php");
        $modelContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$modelName,$singularModelName,$mTableName], $modelContent);
        fwrite($file, $modelContent);
        fclose($file);
        
        mkdir("$newModule/Repositories", $mode);
        $RepoName = ucfirst($moduleName);
        $singularRepoName = ucfirst($newModuleName);
        $file = fopen("$newModule/Repositories/$RepoName" . "Repository.php", "w+");
        $repoContent = file_get_contents(__DIR__."/templatesData/Repositories/$pluralDefaultName" . "Repository.php");
        $repoContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$RepoName,$singularRepoName,$mTableName], $repoContent);
        fwrite($file, $repoContent);
        fclose($file);
        
        mkdir("$newModule/ResponseHandler", $mode);
        $HandlerName = ucfirst($moduleName);
        $singularHandlerName = ucfirst($newModuleName);
        $file = fopen("$newModule/ResponseHandler/$HandlerName" . "ResponseHandler.php", "w+");
        $HandlerContent = file_get_contents(__DIR__."/templatesData/ResponseHandler/$pluralDefaultName" . "ResponseHandler.php");
        $HandlerContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$HandlerName,$singularHandlerName,$mTableName], $HandlerContent);
        fwrite($file, $HandlerContent);
        fclose($file);
        
        mkdir("$newModule/Routes", $mode);
        $file = fopen("$newModule/Routes/route.php", "w+");
        $RouteContent = file_get_contents(__DIR__."/templatesData/Routes/route.php");
        $RouteContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$controllerName,$singularControllerName,$mTableName], $RouteContent);
        fwrite($file, $RouteContent);
        fclose($file);
        
        mkdir("$newModule/Services", $mode);
        $ServiceName = ucfirst($moduleName);
        $singularServiceName = ucfirst($newModuleName);
        $file = fopen("$newModule/Services/$ServiceName" . "Services.php", "w+");
        $ServiceContent = file_get_contents(__DIR__."/templatesData/Services/$pluralDefaultName" . "Services.php");
        $ServiceContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$ServiceName,$singularServiceName,$mTableName], $ServiceContent);
        fwrite($file, $ServiceContent);
        fclose($file);
        
        mkdir("$newModule/Utilities", $mode);
        $helperName = ucfirst($moduleName);
        $singularHelperName = ucfirst($newModuleName);
        $file = fopen("$newModule/Utilities/$helperName" . "Helper.php", "w+");
        $helperContent = file_get_contents(__DIR__."/templatesData/Utilities/$pluralDefaultName" . "Helper.php");
        $helperContent = str_replace([$pluralDefaultName,$defaultName,$dTableName], [$helperName,$singularHelperName,$mTableName], $helperContent);
        fwrite($file, $helperContent);
        fclose($file);
        
        umask($old_umask);
        shell_exec('composer dump-autoload');
        $this->info("New module ($moduleName) has been created successfully");
    }

    /**
     * Get the console command Required.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
            array('moduleName', null, InputOption::VALUE_REQUIRED, false),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'),
            array('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8000),
        );
    }

}
