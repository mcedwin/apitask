<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $account = DB::table('auth_accounts')
      ->where('provider', 'password')
      ->where('provider_id', $request->email)
      ->first();

    if (!$account || !Hash::check($request->password, $account->password_hash)) {
      return response()->json(['error' => 'Credenciales inválidas'], 401);
    }

    $user = User::find($account->user_id);
    $token = $user->createToken('mobile')->plainTextToken;

    return response()->json([
      'user' => $user,
      'token' => $token
    ]);
  }

  // public function google(Request $request)
  // {
  //   $account = DB::table('auth_accounts')
  //     ->where('provider', 'google')
  //     ->where('provider_id', $request->google_id)
  //     ->first();

  //   if (!$account) {
  //     $userId = DB::table('users')->insertGetId([
  //       'name' => $request->name,
  //       'email' => $request->email,
  //       'timezone' => 'America/Lima'
  //     ]);

  //     DB::table('auth_accounts')->insert([
  //       'user_id' => $userId,
  //       'provider' => 'google',
  //       'provider_id' => $request->google_id
  //     ]);
  //   } else {
  //     $userId = $account->user_id;
  //   }

  //   $user = User::find($userId);
  //   $token = $user->createToken('mobile')->plainTextToken;

  //   return response()->json(compact('user', 'token'));
  // }

  public function google(Request $request)
{
    $request->validate([
        'id_token' => 'required|string',
    ]);

    // 1️⃣ Validar token con Google
    $googleResponse = Http::get(
        'https://oauth2.googleapis.com/tokeninfo',
        ['id_token' => $request->id_token]
    );

    if (!$googleResponse->ok()) {
        return response()->json(['error' => 'Token inválido'], 401);
    }

    $googleUser = $googleResponse->json();

    DB::beginTransaction();

    try {
        // 2️⃣ Buscar auth account
        $account = DB::table('auth_accounts')
            ->where('provider', 'google')
            ->where('provider_id', $googleUser['sub'])
            ->first();

        if (!$account) {
            // 3️⃣ Buscar usuario por email (por seguridad)
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'] ?? 'Usuario',
                    'email' => $googleUser['email'],
                    'timezone' => 'America/Lima',
                    'password' => bcrypt(Str::random(32)),
                ]);
            }

            // 4️⃣ Crear vínculo Google
            DB::table('auth_accounts')->insert([
                'user_id' => $user->id,
                'provider' => 'google',
                'provider_id' => $googleUser['sub'],
                'created_at' => now(),
            ]);
        } else {
            $user = User::findOrFail($account->user_id);
        }

        // 5️⃣ Token propio
        $token = $user->createToken('mobile')->plainTextToken;

        DB::commit();

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return response()->json([
            'error' => 'Error al autenticar',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
}

  public function register(Request $request)
  {
    $request->validate([
      'name' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:6'
    ]);

    $userId = DB::table('users')->insertGetId([
      'name' => $request->name,
      'email' => $request->email,
      'timezone' => 'America/Lima'
    ]);

    DB::table('auth_accounts')->insert([
      'user_id' => $userId,
      'provider' => 'password',
      'provider_id' => $request->email,
      'password_hash' => Hash::make($request->password)
    ]);

    return response()->json(['success' => true]);
  }
}
