<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->query('q');
        $cobradorId = $request->query('cobrador_id');

        $clientes = Cliente::query()
            ->with('prestamos')
            ->when($cobradorId, function ($query) use ($cobradorId) {
                $query->where(function ($q) use ($cobradorId) {
                    $q->where('created_by', $cobradorId)
                      ->orWhereExists(function ($sub) use ($cobradorId) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('prestamos')
                              ->whereColumn('prestamos.cliente_id', 'clientes.id')
                              ->where('prestamos.cobrador_id', $cobradorId);
                      });
                });
            })
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', "%{$buscar}%")
                      ->orWhere('apellidos', 'like', "%{$buscar}%")
                      ->orWhere('documento', 'like', "%{$buscar}%")
                      ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $cobradores = \App\Models\User::where('rol', 'cobrador')->orderBy('name')->get();

        return view('clientes.index', compact('clientes', 'buscar', 'cobradores', 'cobradorId'));
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        
        // Validar que no exista un cliente con el mismo documento (evitar duplicados)
        if (!empty($data['documento'])) {
            $existe = Cliente::where('documento', $data['documento'])
                ->when($request->route('cliente'), fn($q, $id) => $q->where('id', '!=', $id))
                ->exists();
            if ($existe) {
                return back()->withInput()->withErrors([
                    'documento' => 'Ya existe un cliente registrado con el documento ' . $data['documento'] . '. 
                    Usa el buscador en el formulario de préstamo para seleccionarlo.'
                ]);
            }
        }
        
        $data['codigo'] = $this->generarCodigo();
        $data['ingreso_mensual'] = $data['ingreso_mensual'] ?? 0;
        $data['created_by'] = auth()->id();
        Cliente::create($data);

        // Si el usuario es un cobrador o viene de la app móvil, redirigir de vuelta a la app móvil
        if (auth()->user()->rol === 'cobrador' || $request->query('origen') === 'movil') {
            return redirect()->route('movil.index')
                ->with('ok', 'Cliente registrado correctamente.');
        }

        return redirect()->route('clientes.index')
            ->with('ok', 'Cliente registrado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $this->validar($request, $cliente->id);
        $data['ingreso_mensual'] = $data['ingreso_mensual'] ?? 0;
        $cliente->update($data);

        return redirect()->route('clientes.index')
            ->with('ok', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        abort_if(auth()->user()->rol === 'cobrador', 403, 'No tienes permisos para eliminar clientes.');

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('ok', 'Cliente eliminado.');
    }

    private function validar(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'tipo_documento' => ['required', 'string', 'max:20'],
            'documento' => ['nullable', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'ocupacion' => ['nullable', 'string', 'max:120'],
            'ingreso_mensual' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'in:activo,inactivo,moroso'],
            'observaciones' => ['nullable', 'string'],
        ]);
    }

    /** Buscador AJAX de clientes (para el formulario de préstamo) */
    public function buscarJson(Request $request)
    {
        $q = $request->query('q');
        
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::where(function ($query) use ($q) {
                $query->where('nombres', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('documento', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            })
            ->orderBy('nombres')
            ->limit(15)
            ->get(['id', 'codigo', 'nombres', 'apellidos', 'documento', 'telefono', 'direccion']);

        return response()->json($clientes->map(fn($c) => [
            'id' => $c->id,
            'label' => "{$c->codigo} · {$c->nombre_completo}",
            'subtext' => $c->documento ? "DNI: {$c->documento}" : '',
            'telefono' => $c->telefono,
            'direccion' => $c->direccion,
        ]));
    }

    private function generarCodigo(): string
    {
        $next = (int) Cliente::withoutGlobalScopes()->max('id') + 1;

        do {
            $codigo = 'CLI-'.str_pad($next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Cliente::withoutGlobalScopes()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
