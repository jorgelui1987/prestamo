<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ActualizarMoraDiaria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuotas:actualizar-mora';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza las cuotas vencidas y calcula la mora diaria de forma automatica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de mora diaria...');
        \App\Models\Cuota::actualizarVencidas();
        $this->info('¡Mora diaria actualizada con éxito!');
    }
}
