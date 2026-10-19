<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Envios;
use App\Models\Encuesta;


class ResultadosController extends Controller
{
    public function index()
    {
        $query = Encuesta::query()->latest();
        if (!auth()->user()->isAdministrador()) {
            $query->where('id_usuario', auth()->id());
        }
        $encuestas = $query->get();
        $envios = Envios::whereIn('id_encuesta', $encuestas->pluck('id'))->get();

        return view('resultados.index', compact('envios', 'encuestas'));
    }

    public function show($id)
    {
        $encuesta = Encuesta::findOrFail($id);
        abort_unless(
            auth()->user()->isAdministrador()
                || (int) $encuesta->id_usuario === (int) auth()->id(),
            403,
            'No tienes permiso para consultar estos resultados.'
        );

        return redirect()->route('encuestas.resultados', $encuesta);
    }
}
