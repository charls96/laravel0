<?php

use App\{Profession, User};
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        /* ->select('id')        //se hace porque asi consume menos recursos solo trae el recurso id sino traeria todos los datos
        ->first(); */

        //dd($profession);        //ver el contenido de una variable una vez ejecutado

       /*  DB::table('users')->insert([
            'name' => 'Pepe Perez',
            'email' => 'pepe@gmail.com',
            'password' => bcrypt('123456'),
            'profession_id' => DB::table('professions')->whereTitle('Desarrollador Back-End')->value('id')
        ]); */

        $professionId = Profession::whereTitle('Desarrollador Back-End')->value('id');

        $user = User::create([
            'name' => 'Pepe Perez',
            'email' => 'pepe@gmail.com',
            'password' => bcrypt('123456'),
            'is_admin' => true
        ]);

        $user->profile()->create([
            'bio' => 'Programador',
            'profession_id' => $professionId
        ]);


        factory(User::class, 49)->create()->each(function ($user) {
            $user->profile()->create(
                factory(App\UserProfile::class)->raw()
            );
        });


        //Eloquent para sustituir mysql

        //podria habeerse hecho (SOLO SE UTILIZA COMO ULTIMO RECURSO YA QUE ESTA LIGADO A USAR MYSQL SI O SI y no se puede comprobar lo que viene del formualrio)
        /* DB::insert('INSERT INTO users VALUES ()'); */
    }
}
