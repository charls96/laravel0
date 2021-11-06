<?php

namespace App\Http\Controllers;
use App\Http\Requests\CreateUserRequest;
use App\Profession;
use App\User;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\UserProfile;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        $title = 'Usuarios';

        return view('users.index')->with(compact('users', 'title'));

        //Alternativa
        /* return view('users.index')
            ->with('users', User::all())
            ->with('title', 'Listado de Usuarios'); */
    }
    public function create()
    {
        $professions = Profession::orderBy('title', 'ASC')->get();
        return view('users.create', compact('professions'));
    }

    public function store(CreateUserRequest $request)         /*Request guarda en $request todo el contenido del formulario*/
    {

       $request->createUser();          //alternativa sin necesidad de crear un método en la siguiente página

       //User::createUser($request->validated());   asi no que hay que definir el método de CreateUSerRequest createUser


        return redirect()->route('users.index');
    }

    public function show(User $user)
    {

        if ($user == null){
            return response()->view('errors.404', [], 404);
        }

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(User $user)
    {
        $data = request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ''
        ]);


        if($data['password'] != null){
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }
}
