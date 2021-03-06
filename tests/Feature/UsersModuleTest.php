<?php

namespace Tests\Feature;

use App\Profession;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersModuleTest extends TestCase
{
    use RefreshDatabase;

    private $profession;

    /** @test */
    public function test_it_shows_the_users_list()
    {
        factory(User::class)->create([
            'name' => 'Joel',
        ]);
        factory(User::class)->create([
            'name' => 'Ellie',
        ]);

        $this->get('usuarios')
            ->assertStatus(200)
            ->assertSee('Usuarios')
            ->assertSee('Joel')
            ->assertSee('Ellie');
    }

    /** @test */
    public function test_it_shows_a_default_message_if_the_users_list_is_empty()
    {
        $this->get('usuarios')
            ->assertStatus(200)
            ->assertSee('Usuarios')
            ->assertSee('No hay usuarios');
    }

    /** @test */
    public function it_displays_the_users_details()
    {
        $user = factory(User::class)->create([
            'name' => 'José Martínez',
        ]);

        $this->get('usuarios/' . $user->id)
            ->assertStatus(200)
            ->assertSee($user->name);
    }

    /** @test */
    public function it_loads_the_news_users_page()
    {
        $profession = factory(Profession::class)->create();
        $skillA = factory(Skill::class)->create();
        $skillB = factory(Skill::class)->create();

        $this->get('usuarios/crear')
            ->assertStatus(200)
            ->assertSee('Crear nuevo usuario')
            ->assertViewHas('professions', function ($professions) use($profession) {
                return $professions->contains($profession);
            })->assertViewHas('skills', function ($skills) use($skillA, $skillB){
                return $skills->contains($skillA) && $skills->contains($skillB);
            });
    }

    /** @test */
    public function it_displays_a_404_error_if_the_user_is_not_found()
    {
        $this->get('usuarios/999')
            ->assertStatus(404)
            ->assertSee('Página no encontrada');
    }

    /** @test */
    public function it_creates_a_new_user()
    {
        $this->post('usuarios', $this->getValidDAta())
            ->assertRedirect('usuarios');

        $this->assertCredentials([
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => '123456',
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'bio' => 'Programador de Laravel y Vue.js',
            'twitter' => 'https://twitter.com/pepe',
            'user_id' => User::findByEmail('pepe@mail.es')->id,
            'profession_id' => $this->profession->id,
        ]);
    }

    /** @test */
    public function the_twitter_field_is_optional()
    {

        $this->post('usuarios', $this->getValidData([
            'twitter' => null
        ]))->assertRedirect('usuarios');

        $this->assertCredentials([
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => '123456'
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'bio' => 'Programador de Laravel y Vue.js',
            'twitter' => null,
            'user_id' => User::findByEmail('pepe@mail.es')->id,
        ]);
    }

    /** @test */
    public function the_name_is_required()
    {
        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
            ->post('usuarios', $this->getValidData([
                'name' => ''
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors(['name' => 'El campo nombre es obligatorio']);

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function the_email_is_required()
    {
        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
        ->post('usuarios', $this->getValidData([
            'email' => ''
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors(['email' => 'El campo email es obligatorio']);

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function the_email_must_be_valid()
    {
        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
        ->post('usuarios', [
            'name' => 'Pepe',
            'email' => 'correo-no-valido',
            'password' => '123456'
        ])->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors('email');

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function the_email_must_be_unique()      //intenta insertar el usaurio creado en pruebas en la base de datos y dar un error dado que ya hay un usuario con ese correo
    {

        factory(User::class)->create([
            'email' => 'pepe@mail.es'
        ]);
        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
        ->post('usuarios', $this->getValidData([
            'email' => 'pepe@mail.es',
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors('email');

        $this->assertEquals(1, User::count());      //dado que buscamos que hay uno en la base de datos el usuario con el mismo correo

    }

    /** @test */
    public function the_password_is_required()
    {
        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
        ->post('usuarios', $this->getValidData([
            'password' => ''
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors(['password' => 'El campo contraseña es obligatorio']);

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function the_profession_id_field_is_optional()
    {
        $this->post('usuarios', $this->getValidData([
            'profession_id' => null
        ]))->assertRedirect('usuarios');

        $this->assertCredentials([
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => '123456',

        ]);

        $this->assertDatabaseHas('user_profiles', [
            'bio' => 'Programador de Laravel y Vue.js',
            'user_id' => User::findByEmail('pepe@mail.es')->id,
            'profession_id' => null,
        ]);
    }

    /** @test */
    public function the_profession_must_be_valid()
    {

        $this->from('usuarios/crear')       //hay que decirle desde donde lo estamos enviando al método validate
        ->post('usuarios', $this->getValidData([
            'profession_id' => '999'
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors(['profession_id']);

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function only_not_deleted_professions_can_be_selected()
    {
        $deletedProfession = factory(Profession::class)->create([
            'deleted_at' => now()->format('Y-m-d'),
        ]);

        $this->from('usuarios/crear')
        ->post('usuarios', $this->getValidData([
            'profession_id' => $deletedProfession->id
        ]))->assertRedirect('usuarios/crear')
            ->assertSessionHasErrors(['profession_id']);

        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function it_loads_the_edit_user_page()
    {
        $user = factory(User::class)->create();         //crea un usuario con datos aleatorios

        $this->get('usuarios/' . $user->id . '/editar')
        ->assertStatus(200)
        ->assertViewIs('users.edit')
        ->assertSee('Editar usuario')
        ->assertViewHas('user', function ($viewUser) use ($user){       //creamos una funcion anonima en la que le pasamos a view el user creado en factory
            return $viewUser -> id === $user->id;
        });
    }

    /** @test */
    public function it_updates_a_user()
    {
        $user = factory(User::class)->create();

        $this->put('usuarios/' . $user->id, $this->getValidData())
            ->assertRedirect('usuarios/' . $user->id);

        $this->assertCredentials([
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => '123456'
        ]);
    }

    /** @test */
    public function the_name_is_required_when_updating_a_user()
    {
        $user = factory(User::class)->create();

        $this->from('usuarios/' . $user->id . '/editar')
        ->put('usuarios/' . $user-> id, $this->getValidData([
            'name' => '',
        ]))->assertRedirect('usuarios/' . $user->id . '/editar')
        ->assertSessionHasErrors(['name']);

        $this->assertDatabaseMissing('users', ['email' => 'pepe@mail.es']);
    }

    /** @test */
    public function the_email_is_required_when_updating_a_user()
    {
        $user = factory(User::class)->create();

        $this->from('usuarios/' . $user->id . '/editar')       //hay que decirle desde donde lo estamos enviando al método validate
        ->put('usuarios/' . $user-> id, $this->getValidData([
            'email' => '',
        ]))->assertRedirect('usuarios/' . $user->id . '/editar');

        $this->assertDatabaseMissing('users', ['name' => 'Pepe']);
    }

    /** @test */
    public function the_email_must_be_valid_when_updating_a_user()
    {
        $user = factory(User::class)->create();

        $this->from('usuarios/' . $user->id . '/editar')       //hay que decirle desde donde lo estamos enviando al método validate
        ->put('usuarios/' . $user-> id, $this->getValidData([
            'email' => 'correo-no-valido',
        ]))->assertRedirect('usuarios/' . $user->id . '/editar')
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['name' => 'Pepe']);
    }

    /** @test */
    public function the_email_must_be_unique_when_updating_a_user()      //intenta insertar el usaurio creado en pruebas en la base de datos y dar un error dado que ya hay un usuario con ese correo
    {
        factory(User::Class)->create([
           'email' => 'existing_email@mail.es'
        ]);

        $user = factory(User::class)->create([
            'email' => 'pepe@mail.es'
        ]);

        $this->from('usuarios/' . $user->id . '/editar')       //hay que decirle desde donde lo estamos enviando al método validate
        ->put('usuarios/' . $user-> id, $this->getValidData([
            'email' => 'existing_email@mail.es',
        ]))->assertRedirect('usuarios/' . $user->id . '/editar')
            ->assertSessionHasErrors('email');

    }

    /** @test */
    public function the_password_is_optional_when_updating_a_user()
    {

        $oldPassword = 'CLAVE ANTERIOR';

        $user = factory(User::Class)->create([
            'password' => bcrypt($oldPassword)
        ]);

        $this->from('usuarios/' . $user->id . '/editar')       //hay que decirle desde donde lo estamos enviando al método validate
         ->put('usuarios/' . $user-> id, $this->getValidData([
             'password' => ''
         ]))->assertRedirect('usuarios/' . $user->id);


        $this->assertCredentials([
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => $oldPassword
        ]);
    }

    /** @test */
    public function the_user_email_can_stay_the_same_when_updating_a_user()
    {

        $user = factory(User::Class)->create([
            'email' => 'pepe@mail.es'
        ]);

        $this->from('usuarios/' . $user->id . '/editar')       //hay que decirle desde donde lo estamos enviando al método validate
        ->put('usuarios/' . $user-> id, $this->getValidData([
            'email' => 'pepe@mail.es',
        ]))->assertRedirect('usuarios/' . $user->id);

        $this->assertDatabaseHas('users', [
            'name' => 'Pepe',
            'email' => 'pepe@mail.es'
        ]);
    }

    /** @test */
    public function it_deletes_a_user()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::Class)->create();

        $this->delete('usuarios/' . $user->id)
            ->assertRedirect('usuarios');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        //$this->assertSame(0, User::count());      //otra forma de ver si hay un usuario igual cuenta si lo encuentra
    }


    public function getValidData(array $custom = [])
    {
        $this->profession = factory(Profession::class)->create();
        return array_merge([               //elimina valores null y suporpone los valores de $custom
            'name' => 'Pepe',
            'email' => 'pepe@mail.es',
            'password' => '123456',
            'profession_id' => $this->profession->id,
            'bio' => 'Programador de Laravel y Vue.js',
            'twitter' => 'https://twitter.com/pepe'
        ], $custom);
    }

    /** @test */
    /*  public function test_it_loads_details_users_page()
    {
        $this->get('usuarios/5')
            ->assertStatus(200)
            ->assertSee('detalles del usuario: 5');
    } */
}
