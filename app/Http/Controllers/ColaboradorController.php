<?php

namespace App\Http\Controllers;

use App\Models\Colaborador;
use App\Models\Sede;
use App\Models\Sede_Dependencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ColaboradorController extends Controller
{
    public function index()
    {
        $colaboradores = Colaborador::with('sedeDependencia.dependencia')->get();
        return view('content.colaboradores.index', compact('colaboradores'));
    }

    public function create()
    {
        $sedes            = Sede::where('estado', 'ACTIVO')->orderBy('nombre_sede')->get();
        $sedeDependencias = Sede_Dependencia::with(['sede', 'dependencia'])
            ->where('estado', 'ACTIVO')
            ->get();

        return view('content.colaboradores.create', compact('sedes', 'sedeDependencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nro_documento'      => 'required|string|max:20|unique:colaboradores,nro_documento',
            'per_nombre'         => 'required|string|max:100',
            'per_apepat'         => 'required|string|max:100',
            'per_apemat'         => 'nullable|string|max:100',
            'fecha_nacimiento'   => 'nullable|date|before:today',
            'nro_movil'          => 'nullable|string|max:20',
            'per_email'          => 'nullable|email|max:150',
            'per_direccion'      => 'nullable|string|max:255',
            'per_foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_sede_dependencia' => 'nullable|exists:sede_dependencia,id_sede_dependencia',
            'cargo' => [
                'required',
                'string',
                'max:100',
                Rule::in([
                    'JEFE',
                    'ESPECIALISTA',
                    'TECNICO',
                    'ASISTENTE',
                    'OTRO',
                ]),
            ],
            'tipo_colaborador'   => 'required|in:ADMINISTRATIVO,DOCENTE,TECNICO,PRACTICANTE,EXTERNO',
            'fecha_ingreso'      => 'nullable|date',
            'fecha_salida'       => 'nullable|date|after_or_equal:fecha_ingreso',
        ], [
            'nro_documento.required' => 'El número de documento es obligatorio.',
            'nro_documento.unique'   => 'Este número de documento ya está registrado.',
            'per_nombre.required'    => 'El nombre es obligatorio.',
            'per_apepat.required'    => 'El apellido paterno es obligatorio.',
            'per_email.email'        => 'El correo electrónico no es válido.',
            'per_foto.image'         => 'El archivo debe ser una imagen.',
            'per_foto.max'           => 'La imagen no puede superar los 2 MB.',
            'tipo_colaborador.required' => 'Debe seleccionar el tipo de colaborador.',
            'fecha_salida.after_or_equal' => 'La fecha de salida debe ser posterior a la de ingreso.',
        ]);

        $data = $request->except('per_foto');
        $data['per_nombre'] = strtoupper($data['per_nombre']);
        $data['per_apepat'] = strtoupper($data['per_apepat']);
        $data['per_apemat'] = $data['per_apemat'] ? strtoupper($data['per_apemat']) : null;

        if ($request->hasFile('per_foto')) {
            $data['per_foto'] = $request->file('per_foto')->store('colaboradores', 'public');
        }

        Colaborador::create($data);

        return redirect()->route('colaboradores.index')
            ->with('success', 'Colaborador registrado correctamente.');
    }

    public function edit(int $id)
    {
        $colaborador     = Colaborador::findOrFail($id);
        $sedes           = Sede::where('estado', 'ACTIVO')->orderBy('nombre_sede')->get();
        $sedeDependencias = Sede_Dependencia::with(['sede', 'dependencia'])
            ->where('estado', 'ACTIVO')
            ->get();

        return view('content.colaboradores.edit', compact('colaborador', 'sedes', 'sedeDependencias'));
    }

    public function update(Request $request, int $id)
    {
        $colaborador = Colaborador::findOrFail($id);

        $request->validate([
            'nro_documento'      => "required|string|max:20|unique:colaboradores,nro_documento,{$id},id_colaborador",
            'per_nombre'         => 'required|string|max:100',
            'per_apepat'         => 'required|string|max:100',
            'per_apemat'         => 'nullable|string|max:100',
            'fecha_nacimiento'   => 'nullable|date|before:today',
            'nro_movil'          => 'nullable|string|max:20',
            'per_email'          => 'nullable|email|max:150',
            'per_direccion'      => 'nullable|string|max:255',
            'per_foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'id_sede_dependencia' => 'nullable|exists:sede_dependencia,id_sede_dependencia',
            'cargo'              => 'nullable|string|max:100',
            'tipo_colaborador'   => 'required|in:ADMINISTRATIVO,DOCENTE,TECNICO,PRACTICANTE,EXTERNO',
            'fecha_ingreso'      => 'nullable|date',
            'fecha_salida'       => 'nullable|date|after_or_equal:fecha_ingreso',
        ], [
            'nro_documento.unique' => 'Este número de documento ya está registrado.',
            'per_nombre.required'  => 'El nombre es obligatorio.',
            'per_apepat.required'  => 'El apellido paterno es obligatorio.',
            'per_email.email'      => 'El correo electrónico no es válido.',
            'per_foto.image'       => 'El archivo debe ser una imagen.',
            'per_foto.max'         => 'La imagen no puede superar los 2 MB.',
        ]);

        $data = $request->except('per_foto');
        $data['per_nombre'] = strtoupper($data['per_nombre']);
        $data['per_apepat'] = strtoupper($data['per_apepat']);
        $data['per_apemat'] = $data['per_apemat'] ? strtoupper($data['per_apemat']) : null;

        if ($request->hasFile('per_foto')) {
            if ($colaborador->per_foto) {
                Storage::disk('public')->delete($colaborador->per_foto);
            }
            $data['per_foto'] = $request->file('per_foto')->store('colaboradores', 'public');
        }

        $colaborador->update($data);

        return redirect()->route('colaboradores.index')
            ->with('success', 'Colaborador actualizado correctamente.');
    }

    public function toggleEstado(int $id)
    {
        $colaborador = Colaborador::findOrFail($id);
        $colaborador->estado = $colaborador->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $colaborador->save();

        return response()->json([
            'success'      => true,
            'message'      => 'Estado actualizado correctamente.',
            'nuevo_estado' => $colaborador->estado,
        ]);
    }
}
