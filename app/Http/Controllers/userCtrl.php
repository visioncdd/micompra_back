<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Utilidades;
use App\Producto;
use App\Favorito;
use App\Comentario;
use Hash;
use JWTAuth;
use Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

// use JWTAuth;
// use Validator;
use App\Http\Middleware\AddJsonAcceptHeader;
use Config;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Mail\Message;

class userCtrl extends Controller
{

    public function register(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'email' => 'email|max:255|required',
            'password' => 'required_if:facebook,false|confirmed',
            'name' => 'required|string',
            'tel' => 'required_if:facebook,false|numeric',
            'estado_id' => 'required_if:facebook,false|numeric'
        ];

        $validacion = Validator::make($input,$reglas,[
            'name.string' => 'El campo nombre solo puede contener letras.',
        ]);

        $input['password'] = Hash::make(array_key_exists('password', $input) ? $input['password'] : 'facebook');

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        try{

            $usuario = User::create($input);

        }
        catch (\Illuminate\Database\QueryException $e) {
            return ['r' => false, 'error' => ['El usuario ya existe...']];
        }

        $data = $usuario;
        $user = User::with(['favoritos','estado','productos'])->find($data["id"]);
        $user["token"] = JWTAuth::fromUser($usuario);

        return response()->json(['r' => true, 'c' => $user, 'msj' => 'Usuario creado con exito.' ]);
    }

    public function login(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'email' => 'email|max:255|required',
            'password' => 'required_if:facebook,false'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        if(array_key_exists("facebook", $input) && $input["facebook"]==1)
            $input = ['email' => $input['email'], 'password' => 'facebook'];

        if (!$token = JWTAuth::attempt($input))
            return response()->json(['r' => false, 'error' => ['Email o clave incorrectos.']]);

        $data = JWTAuth::toUser($token);
        $user = User::with(['favoritos','estado','productos.fotos'])->find($data["id"]);
        $user["token"] = $token;

        return response()->json(['r' => true, 'c' => $user, 'msj' => 'Login exitoso...']);
    }

    public function get_user_details(Request $request)
    {
        $input = $request->all();

        $user = JWTAuth::toUser($input['token']);

        if($user){
            $user = User::with(['favoritos','estado','productos.fotos'])->find($user["id"]);
            $data = $user;
            $data["token"] = JWTAuth::refresh($input['token']);
        }

        return response()->json(['r' => $user ? true : false, 'c' => $user ? $data : false]);

        // return $input;
    }

    public function edit(Request $request)
    {
        $input = $request->all();

         $reglas = [
            'name' => 'required|string',
            'tel' => 'numeric',
            'estado_id' => 'numeric'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ["r" => false, "error" => $validacion->errors()->all()];

        // $user = JWTAuth::toUser($input['token']);

        // if($user){
            $user = User::find($input["id"]);

            $user->name = $input["name"];
            if(array_key_exists("tel", $input))
                $user->tel = $input["tel"];
            if(array_key_exists("estado_id", $input))
                $user->estado_id = $input["estado_id"];
            $user->save();
        // }

        return response()->json(['r' => true]);

        // return $input;
    }

    public function favorito(Request $request)
    {
        $input = $request->only(['token','id','type']);

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric',
            'type' => 'required|in:1,0'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input['token']);

        $favorito = $input["type"]==1 ? $user->favoritos()->create(['producto_id'=>$input["id"]]) : Favorito::where('producto_id',$input["id"])->where('user_id',$user["id"])->delete();

        return response()->json(['r' => $favorito ? true : false, 'c' => $favorito]);
    }

    public function comentar(Request $request)
    {
        $input = $request->only(['token','id','pregunta']);

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric',
            'pregunta' => 'required'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input['token']);

        $comentario = Comentario::create(['pregunta' => $input["pregunta"], 'producto_id' => $input["id"], 'user_id' => $user["id"]]);

        $comentario["usuario"] = User::find($comentario["user_id"]);

        return response()->json(['r' => $comentario ? true : false, 'c' => $comentario]);
    }

    public function comentario_d(Request $request)
    {
        $input = $request->only(['token','id']);

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric',
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input['token']);

        $comentario = Comentario::find($input["id"]);

        $producto = $comentario->producto;

        if($comentario["user_id"]==$user["id"] || $producto["user_id"]==$user["id"])
            $delete = $comentario->delete();

        return response()->json(['r' => isset($delete) ? true : false, 'c' => isset($delete) ? true : 'El comentario no existe']);
    }

    public function comentario_r(Request $request)
    {
        $input = $request->only(['token','id','respuesta']);

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric',
            'respuesta' => 'required'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input['token']);

        $comentario = Comentario::find($input["id"]);

        $producto = $comentario->producto;

        if($producto["user_id"]==$user["id"]){
            $comentario->respuesta = $input["respuesta"];
            $r = $comentario->save();
        }

        return response()->json(['r' => isset($r) ? true : false, 'c' => isset($r) ? $comentario : 'El comentario no existe']);
    }

    public function comentario_rd(Request $request)
    {
        $input = $request->only(['token','id']);

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric',
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input['token']);

        $comentario = Comentario::find($input["id"]);

        $producto = $comentario->producto;

        if($producto["user_id"]==$user["id"]){
            $comentario->respuesta = "";
            $r = $comentario->save();
        }

        return response()->json(['r' => isset($r) ? true : false, 'c' => isset($r) ? $comentario : 'El comentario no existe']);
    }

    public function publicar(Request $request)
    {

        function urls_amigables($url){
            $url = strtolower($url);

            $find = array('á','é','í','ó','ú','ñ');
            $repl = array('a','e','i','o','u','n');

            $url = str_replace($find, $repl, $url);

            $find = array(' ','&','\r\n','\n','+');
            $url = str_replace($find, '-', $url);

            $find = array('/[^a-z0-9\-<>]/','/[\-]+/','/<[^>]*>/');

            $repl = array('','-','');

            $url = preg_replace($find, $repl, $url);

            return $url;
        }

        $input = $request->all();

        $user = JWTAuth::toUser($input['token']);

        if(!$user)
            return ['r' => false, 'error' => ['Usuario no existe...']];

        $reglas = [
            'nombre' => 'required|string',
            'precio' => 'numeric',
            'descripcion' => 'required|string',
            'tipo' => 'required|in:Nuevo,Usado',
            'categoria_id' => 'required|numeric',
            'subcategoria_id' => 'required|numeric',
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        try{

            $input["url"] = urls_amigables($input['nombre']);

            $c = Producto::where('url','like','%'.$input["url"].'%')->count();

            $input["url"] .= $c>0 ? Producto::where('url','like','%'.$input["url"].'%')->orderBy('id','desc')->first()->id : "";

            $input["estado_id"] = $user["estado_id"];

            $publicacion = $user->productos()->create($input);

        }
        catch (\Illuminate\Database\QueryException $e) {
            return ['r' => false, 'error' => ['No se pudo realizar la publicacion...']];
        }

        return ['r' => true, 'c' => $publicacion];

    }

    public function admin(Request $request)
    {
        $input = $request->only(["token"]);

        try{
            $user = JWTAuth::toUser($input['token']);
        }catch(TokenInvalidException $e){
            return ['r' => false];
        }catch(\Exception $e){
            return ['r' => false];
        }

        return response()->json(['r' => $user && $user["role"]=="admin" ? true : false, 'c' => $user]);

        // return $input;
    }

}