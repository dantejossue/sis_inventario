<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  // Procesar la validación
  public function authenticate(Request $request)
  {
    // 1. Validar que los campos no vengan vacios
    $credenciales = $request->validate(
      [
        'nombre_usuario' => ['required', 'string'],
        'contrasena' => ['required', 'string'],
      ],
      [
        // Mensajes de error personalizados en español
        'nombre_usuario.required' => 'El campo usuario es obligatorio.',
        'contrasena.required' => 'La contraseña es obligatoria.'
      ]
    );

    // 2. Intentar el login con la BD
    // PASO 1: Buscar si el usuario existe en la base de datos
    $usuario = User::where('nombre_usuario', $credenciales['nombre_usuario'])->first();

    if (!$usuario) {
      return back()->withErrors([
        'nombre_usuario' => 'Este usuario no existe en el sistema.',
      ])->onlyInput('nombre_usuario');
    }

    // PASO 2: Verificar si la contraseña coincide (comparamos texto plano vs hash de la BD)
    if (!Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
      // Retornamos error específico al campo contraseña
      return back()->withErrors([
        'contrasena' => 'La contraseña es incorrecta.',
      ])->onlyInput('contrasena');
    }

    // PASO 3: Verificar el estado del usuario
    if ($usuario->estado === 'INACTIVO') {
      // Retornamos error de cuenta deshabilitada
      return back()->withErrors([
        'nombre_usuario' => 'Su cuenta está INACTIVA. Comuníquese con la OTI.',
      ])->onlyInput('nombre_usuario');
    }

    Auth::login($usuario);

    $request->session()->regenerate();

    // Cargar relación rol para la redirección
    $usuario->load('rol');
    $nombreRol = $usuario->rol?->nombre ?? '';

    return match ($nombreRol) {
      'PROVEEDOR' => redirect()->route('proveedor.dashboard'),
      default     => redirect()->route('home'),
    };

    // Nota: Mapeamos 'usuario' de tu input a la columna de tu BD (asumiendo que se llama 'usuario')
    // if (Auth::attempt([
    //   'nombre_usuario' => $credenciales['nombre_usuario'],
    //   'password' => $credenciales['contrasena'],
    //   'estado' => 'ACTIVO',
    // ])) {
    //   // 3. Si es exitoso, regeneramos la sesion por seguridad (evita Session Fixation)
    //   $request->session()->regenerate();
    //   // 4. Redirigimos al sistema
    //   return redirect()->intended('home');
    // }
    // //5. Si falla, lo devolvemos al login con un error
    // return back()->withErrors([
    //   'nombre_usuario' => 'El usuario o la contraseña son incorrectos.',
    // ])->onlyInput('nombre_usuario'); // Retenemos el nombre del usuario para que no tenga que volver a escribirlo
  }

  public function logout(Request $request)
  {
    Auth::logout(); // Cierra la sesión del usuario

    $request->session()->invalidate(); // Destruye la sesión actual
    $request->session()->regenerateToken(); // Regenera el token CSRF por seguridad

    return redirect('/login'); // Lo mandamos de regreso al formulario
  }
}
