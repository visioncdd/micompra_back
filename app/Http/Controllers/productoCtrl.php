<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use JWTAuth;
use Validator;
use App\Foto;
use App\Producto;
use App\Categoria;
use App\Subcategoria;
use App\Estado;
use App\Http\Middleware\AddJsonAcceptHeader;

class productoCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function subir_foto(Request $request)
    {
        $datos = $request->all();
        // return ["r" => false, "error" => ['hola']];

        $file = $request->file('file');

        $n = Foto::where('producto_id',$datos["id"])->count();

        $tipo = explode(".",$file->getClientOriginalName());
        $tipo = $tipo[count($tipo)-1];

        $nombre = $datos["id"]."_".$n.'.'.$tipo;

        \Storage::disk('local')->put($nombre,  \File::get($file));

        $guardar = Foto::create(['producto_id' => $datos["id"], 'foto' => $nombre]);

       return ['r' => true];

        // return $nombre;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric'
        ];

        $validacion = Validator::make($input,$reglas,['token.required' => 'No autorizado...']);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        if(!$user = JWTAuth::toUser($input['token']))
            return ['r' => false, 'error' => ['No autorizado...']];

        if($user["role"]=="admin"){
            Producto::destroy($input["id"]);
            return ["r" => true];
        }

        $d = $user->productos()->find($input["id"])->delete();

        if($d)
            return ['r' => true, 'msj' => 'Eliminado con exito...'];
    }

    public function aprobar(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'token' => 'required',
            'id' => 'required|numeric'
        ];

        $validacion = Validator::make($input,$reglas,['token.required' => 'No autorizado...']);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        if(!$user = JWTAuth::toUser($input['token']))
            return ['r' => false, 'error' => ['No autorizado...']];

        if($user["role"]=="admin"){
            $p = Producto::find($input["id"]);
            $p->aprobado =  1;
            $p->save();
            return ["r" => true];
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        $producto = Producto::with(["fotos","categoria","subcategoria","usuario","estado","comentarios.usuario" => function($query){
            $query->orderBy('created_at', 'desc');
        }])->where('url',$name)->first();
        return $producto;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'aprobado' => 'in:0,1',
            'take' => 'numeric',
            'offset' => 'numeric',
            'order' => 'required_with:orderBy',
            'orderBy' => 'required_with:order'
        ];
        // return $input;

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $productos = Producto::with(['fotos','categoria','subcategoria','estado']);

        if(array_key_exists("aprobado", $input))
            $productos->aprobado($input["aprobado"]);
        if(array_key_exists("take", $input))
            $productos->take($input["take"]);
        if(array_key_exists("offset", $input))
            $productos->offset($input["take"]);
        if(array_key_exists("order", $input))
            $productos->orderBy($input["orderBy"],$input["order"]);

        // $productos = $productos->get()->map(function($p){
        //     $p->fotos = Foto::where("producto_id",$p->id)->get();
        //     return $p;
        // });

        return ["r" => true, "c" => ["productos" => $productos->get(), "total" => Producto::aprobado($input["aprobado"])->count()]];
    }

    public function filtro(Request $request)
    {
        $input = $request->all();

        $reglas = [
            'aprobado' => 'in:0,1',
            'take' => 'numeric',
            'offset' => 'numeric',
            'order' => 'required_with:orderBy',
            'orderBy' => 'required_with:order',
            'cat_name' => 'string',
            'subcat_name' => 'string',
            'filtro' => 'in:Nuevo,Usado',
            'estado_id' => 'numeric'
        ];
        // return $input;

        $validacion = Validator::make($input,$reglas);

        if($validacion->fails())
            return ['r' => false, 'error' => $validacion->errors()->all()];

        $productos = Producto::with(['fotos','estado']);
        $nuevo = Producto::with(['fotos','estado']);
        $usado = Producto::with(['fotos','estado']);

        if(array_key_exists("cat_name", $input) && !array_key_exists("subcat_name", $input)){
            $productos->where('categoria_id',Categoria::where('name',$input["cat_name"])->first()->id);
            $nuevo->where('categoria_id',Categoria::where('name',$input["cat_name"])->first()->id);
            $usado->where('categoria_id',Categoria::where('name',$input["cat_name"])->first()->id);
        }
        if(array_key_exists("subcat_name", $input)){
            $productos->where('subcategoria_id',Subcategoria::where('name',$input["subcat_name"])->first()->id);
            $nuevo->where('subcategoria_id',Subcategoria::where('name',$input["subcat_name"])->first()->id);
            $usado->where('subcategoria_id',Subcategoria::where('name',$input["subcat_name"])->first()->id);
        }
        if(array_key_exists("estado_id", $input)){
            $productos->where('estado_id',$input["estado_id"]);
            $nuevo->where('estado_id',$input["estado_id"]);
            $usado->where('estado_id',$input["estado_id"]);
        }
        if(array_key_exists("aprobado", $input)){
            $productos->aprobado($input["aprobado"]);
            $nuevo->aprobado($input["aprobado"]);
            $usado->aprobado($input["aprobado"]);
        }

        $total = $productos->count();
        $nuevo = $nuevo->where("tipo","Nuevo")->count();
        $usado = $usado->where("tipo","Usado")->count();

        if(array_key_exists("filtro", $input))
            $productos->where("tipo",$input["filtro"]);
        if(array_key_exists("take", $input))
            $productos->take($input["take"]);
        if(array_key_exists("offset", $input))
            $productos->offset($input["take"]);
        if(array_key_exists("order", $input))
            $productos->orderBy($input["orderBy"],$input["order"]);

        return [
            "r" => true,
            "c" => [
                "productos" => $productos->get(),
                "total" => $total,
                "nuevo" => $nuevo,
                "usado" => $usado,
                "estados" => Estado::with(["productosCount" => function($q) use ($input){
                    if(array_key_exists("filtro", $input))
                        $q->where("tipo",$input["filtro"]);
                    if(array_key_exists("estado_id", $input))
                        $q->where("estado_id",$input["estado_id"]);
                    if(array_key_exists("aprobado", $input))
                        $q->aprobado($input["aprobado"]);
                }])->get(),
                "categorias" => Categoria::with(["productosCount" => function($q) use ($input){
                    if(array_key_exists("filtro", $input))
                        $q->where("tipo",$input["filtro"]);
                    if(array_key_exists("estado_id", $input))
                        $q->where("estado_id",$input["estado_id"]);
                    if(array_key_exists("aprobado", $input))
                        $q->aprobado($input["aprobado"]);
                },"sub.productosCount" => function($q) use ($input){
                    if(array_key_exists("filtro", $input))
                        $q->where("tipo",$input["filtro"]);
                    if(array_key_exists("estado_id", $input))
                        $q->where("estado_id",$input["estado_id"]);
                    if(array_key_exists("aprobado", $input))
                        $q->aprobado($input["aprobado"]);
                }])->get(),
                "categoria" => array_key_exists("cat_name", $input) ? Categoria::where('name',$input["cat_name"])->with(["productosCount","sub.productosCount"])->first() : null,
                "subcategoria" => array_key_exists("subcat_name", $input) ? Subcategoria::where('name',$input["subcat_name"])->with(["productosCount"])->first() : null
            ]
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
