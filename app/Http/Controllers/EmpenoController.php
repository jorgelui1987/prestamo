<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empeno;
use Illuminate\Http\Request;

class EmpenoController extends Controller
{
    public const ESTADOS = [
        'vigente' => 'Vigente',
        'recuperado' => 'Recuperado',
        'vencido' => 'Vencido',
        'rematado' => 'Rematado',
    ];

    public function index(Request $request)
    {
        Empeno::actualizarVencidos();

        $estado = $request->query('estado');
        $buscar = $request->query('q');

        $empenos = Empeno::query()
            ->with('cliente')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('codigo', 'like', "%{$buscar}%")
                        ->orWhere('articulo', 'like', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($c) use ($buscar) {
                            $c->where('nombres', 'like', "%{$buscar}%")
                                ->orWhere('apellidos', 'like', "%{$buscar}%")
                                ->orWhere('documento', 'like', "%{$buscar}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $resumen = [
            'total' => Empeno::count(),
            'vigentes' => Empeno::where('estado', 'vigente')->count(),
            'vencidos' => Empeno::where('estado', 'vencido')->count(),
            'capital' => (float) Empeno::whereIn('estado', ['vigente', 'vencido'])->sum('monto_prestado'),
        ];

        return view('empenos.index', compact('empenos', 'estado', 'buscar', 'resumen'));
    }

    public function create()
    {
        return view('empenos.form', [
            'empeno' => new Empeno([
                'tasa_interes' => 15,
                'fecha_inicio' => now()->toDateString(),
                'fecha_vencimiento' => now()->addDays(30)->toDateString(),
                'estado' => 'vigente',
            ]),
            'clientes' => Cliente::orderBy('nombres')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['codigo'] = $this->generarCodigo();
        $data['estado'] = 'vigente';
        Empeno::create($data);

        return redirect()->route('empenos.index')->with('ok', 'Empeno registrado correctamente.');
    }

    public function show(Empeno $empeno)
    {
        $empeno->load('cliente');

        return view('empenos.show', compact('empeno'));
    }

    public function edit(Empeno $empeno)
    {
        return view('empenos.form', [
            'empeno' => $empeno,
            'clientes' => Cliente::orderBy('nombres')->get(),
        ]);
    }

    public function update(Request $request, Empeno $empeno)
    {
        $empeno->update($this->validar($request));

        return redirect()->route('empenos.show', $empeno)->with('ok', 'Empeno actualizado.');
    }

    public function destroy(Empeno $empeno)
    {
        $empeno->delete();

        return redirect()->route('empenos.index')->with('ok', 'Empeno eliminado.');
    }

    public function cambiarEstado(Request $request, Empeno $empeno)
    {
        $data = $request->validate([
            'estado' => ['required', 'in:'.implode(',', array_keys(self::ESTADOS))],
        ]);

        $empeno->update(['estado' => $data['estado']]);

        return back()->with('ok', 'Estado del empeno actualizado a "'.self::ESTADOS[$data['estado']].'".');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'articulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'valor_tasacion' => ['required', 'numeric', 'min:0'],
            'monto_prestado' => ['required', 'numeric', 'min:1'],
            'tasa_interes' => ['required', 'numeric', 'min:0', 'max:100'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ], [], [
            'cliente_id' => 'cliente',
            'valor_tasacion' => 'valor de tasacion',
            'monto_prestado' => 'monto prestado',
            'tasa_interes' => 'tasa de interes',
            'fecha_vencimiento' => 'fecha de vencimiento',
        ]);
    }

    private function generarCodigo(): string
    {
        $next = (int) Empeno::withoutGlobalScopes()->max('id') + 1;

        do {
            $codigo = 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Empeno::withoutGlobalScopes()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
