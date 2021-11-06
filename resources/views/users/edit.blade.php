@extends('layout')

@section('title', 'Editar usuario')

@section('content')
    <h1>Editar usuario</h1>

    @if($errors->any())     <!--Si hay errores (los errores estan en $error que lo maneja laravel de sesion)-->
    <div class="alert alert-danger">
        <h6>Por favor, corrige los siguientes errores</h6>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
    </div>
    @endif
    <!-- Hay errores en los archivos edit-->
    <form action="{{ route('users.update', $user) }}" method="POST">

        {{ csrf_field() }}
        {{ method_field('PUT') }}

        <label for="name">Nombre:</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}">       <!--para que recuerde los datos puestos -->
        <br>
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">
        <br>
        <label for="password">Contraseña: </label>
        <input type="password" name="password">
        <br>
        <button type="submit">Crear usuario</button>
    </form>

    <p>
        <a href="{{ route('users.index') }}">Regresar al listado de usuarios</a>
    </p>

@endsection