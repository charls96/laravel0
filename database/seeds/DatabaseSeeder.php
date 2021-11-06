<?php

use Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTables(['professions', 'users']);

        $this->call(ProfessionSeeder::class);
        $this->call(UserSeeder::class);
    }

    private function truncateTables(array $tables)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');    //desabilita el checkeo de claves foreigns

        foreach ($tables as $table){
            DB::table($table)->truncate();   //borra la tabla
        }


        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
