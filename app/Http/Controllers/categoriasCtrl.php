<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Categoria;
use App\Subcategoria;

use Validator;
use JWTAuth;

class categoriasCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
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

        $reglas = [
            'nombre' => 'required|string',
            'icono' => 'string',
            'categoria' => 'numeric',
            'token' => 'required'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input["token"]);

        if($user["role"]!="admin")
            return ['r' => false, 'error' => ['No tiene permisos suficientes.']];

        try{

            $name = urls_amigables($input['nombre']);

            $c = array_key_exists("categoria", $input) ? Subcategoria::where('name','like','%'.$name.'%')->count() : Categoria::where('name','like','%'.$name.'%')->count();

            $name .= $c>0 ? array_key_exists("categoria", $input) ? Subcategoria::where('name','like','%'.$name.'%')->orderBy('id','desc')->first()->id : Categoria::where('name','like','%'.$name.'%')->orderBy('id','desc')->first()->id : "";

            $categoria = array_key_exists("categoria", $input) ? Subcategoria::create(["titulo" => $input["nombre"], "name" => $name, "categoria_id" => $input["categoria"]]) : Categoria::create(["titulo" => $input["nombre"], "name" => $name, "icono" => $input["icono"]]);

            if(!array_key_exists("categoria", $input))
                $categoria["sub"] = [];

        }
        catch (\Illuminate\Database\QueryException $e) {
            return ['r' => false, 'error' => ['No se pudo crear la categoria...']];
        }

        return ['r' => true, 'c' => Categoria::with('sub')->orderBy('titulo','asc')->get()];

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        return Categoria::with('sub')->orderBy('titulo','asc')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {

        $input = $request->all();

        $reglas = [
            'id' => 'required|numeric',
            'token' => 'required',
            't' => 'in:categoria,subcategoria'
        ];

        // return $input;

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input["token"]);

        if($user["role"]!="admin")
            return ['r' => false, 'error' => ['No tiene permisos suficientes.']];

        $t = $input["t"];

        try{

            if($t=="categoria"){
                Categoria::find($input["id"])->sub()->delete();
                Categoria::destroy($input["id"]);
            }
            else
                Subcategoria::destroy($input["id"]);

        }
        catch (\Illuminate\Database\QueryException $e) {
            return ['r' => false, 'error' => ["No se pudo eliminar la $t..."]];
        }

        return ['r' => true];

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
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

        $reglas = [
            'nombre' => 'required|string',
            'icono' => 'string',
            'token' => 'required',
            'id' => 'required|numeric'
        ];

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $user = JWTAuth::toUser($input["token"]);

        if($user["role"]!="admin")
            return ['r' => false, 'error' => ['No tiene permisos suficientes.']];

        try{
            // return $input;
            $categoria = array_key_exists("sub", $input) ? Categoria::find($input["id"]) : Subcategoria::find($input["id"]);

            $name = $input["name"];

            if($categoria->titulo!=$input["nombre"]){

                $name = urls_amigables($input['nombre']);

                $co = array_key_exists("sub", $input) ? Categoria::where('name','like','%'.$name.'%') : Subcategoria::where('name','like','%'.$name.'%');

                $c = $co->count();

                $name .= $c>0 ? $co->orderBy('id','desc')->first()->id : "";



            }

           if(array_key_exists("sub", $input))
               $categoria->icono = $input["icono"];
           $categoria->name = $name;
           $categoria->titulo = $input["nombre"];
           $categoria->save();

        }
        catch (\Illuminate\Database\QueryException $e) {
            return ['r' => false, 'error' => ["No se pudo eliminar la $t..."]];
        }

        return ['r' => true, 'c' => $categoria];

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
