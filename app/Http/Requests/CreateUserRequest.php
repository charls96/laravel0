<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;        //cunando no se utilizan roles de usuario no se pone false
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',        //otra opcion de poner varias reglas ['required', 'email'], en unque hay que indicar en que tabla quiere que se compruebe y la columna
            'password' => 'required',            //una vez puesto en validacion sin ninguna validacion pasa las pruebas
            'bio' => 'required',                //hacer test de validaciones para ver que es requerido
            'twitter' => 'nullable|present|url',           //hacer un test para ver si es una url y que puede ser nulo
            'profession_id' => [
                'nullable', 'present',
                Rule::exists('professions', 'id')->whereNull('deleted_at')
                ],
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'El campo nombre es obligatorio',    //podemos decirle a validate los errores que queremos que salgan
            'email.required' => 'El campo email es obligatorio',
            'password.required' => 'El campo contraseña es obligatorio',            //ejercicio poner mas reglas de validacion como string a nombre o minimo de valores...
            'email.unique' => 'Ese email ya existe en la BD'
        ];
    }

    public function createUser(){
        User::createUser($this->validated());
    }
}
