<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
    public function isAdmin()
    {
        return $this->is_admin;
    }

    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    public static function createUser($data)
    {
        DB::transaction(function () use($data) {        //hace que todo se haga o nada para que no se ejecutara el perfil
            $user = User::create([
                'name' => $data['name'],        //para utilizar los datos que estan validados
                'email' => $data['email'],          //salta error porque no estan en las validadciones
                'password' => bcrypt($data['password']),

            ]);

            $user->profile()->create([
                'bio' => $data['bio'],
                'twitter' => $data['twitter'],
                'profession_id' => $data['profession_id'],
                //es lo que habia devuelve si hay cosas sino null es una abrevacion del operador ternario la abrevacion seria ?? o :?
                //'twitter' => array_get($data, 'twitter')   otra forma de hacerlo de arriba ya que devuelve null si no existe
            ]);
        });
    }
}
