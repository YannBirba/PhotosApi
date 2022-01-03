<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SpecialMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smgt
    {--d=false : If true, will dump all database, false by default.}
    {--f=true : If true, will drop all tables and re-migrate, true by default.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the migrations in the order specified in the file app/Console/Comands/SpecialMigrate.php. You can specify if you want to not drop all the table in db before execute the command. with the option --f=false';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $basePath = 'database/migrations/'; 
        $isDump = $this->option('d');
        if ($isDump == 'true') {
            Artisan::call('schema:dump');
        }
        $isFresh = $this->option('f');
        if ($isFresh == 'true') {
            Artisan::call('db:wipe');
        }

       /** Specify the names of the migrations files in the order you want to 
        * loaded
        * $migrations =[ 
        *    'xxxx_xx_xx_000000_create_nameTable_table.php',
        *];
        */
        $to_migrate_before = [ 
            '2021_12_11_180332_create_groups_table',
            '2021_12_11_201430_create_events_table',
            '2021_12_19_204544_create_images_table',
            '2021_12_19_211846_add_image_column_in_event_table',
        ];
        if ($to_migrate_before && count($to_migrate_before) > 0 && $to_migrate_before != null) {
            foreach($to_migrate_before as $migration)
            {         
               $migrationName = trim($migration);
               $path = $basePath.$migrationName.'.php';
               $this->call('migrate', [
                '--path' => $path ,            
               ]);
            }
        }

        $migrations = [ 
            '2014_10_12_000000_create_users_table',
            '2014_10_12_100000_create_password_resets_table',
            '2019_12_14_000001_create_personal_access_tokens_table',
            '2019_08_19_000000_create_failed_jobs_table',
            '2021_12_11_203522_create_pivot_table_group_event',
        ];
        if ($migrations && count($migrations) > 0 && $migrations != null) {
            foreach($migrations as $migration)
            {
                $migrationName = trim($migration);
                $path = $basePath.$migrationName.'.php';
                $this->call('migrate', [
                '--path' => $path ,            
                ]);
            }
        }
    }
} 
