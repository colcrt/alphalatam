<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use App\Core\Auth\Auth;
use App\Domain\Contrato\DTO\ContratoData;
use App\Models\Contrato;

class ContratoService
{
    private $repositorio;

    public function __construct($repositorio = null)
    {
        $this->repositorio = $repositorio ?? App::make('App\\Repositories\\Contracts\\ContratoRepositoryInterface');
    }

    public function paraAdmin(array $filtros = [])
    {
        return $this->repositorio->paginarParaAdmin($filtros);
    }

    public function crear(ContratoData $datos): Contrato
    {
        $atributos = $datos->toModelArray();
        $atributos['created_by'] = Auth::id();
        $atributos['updated_by'] = Auth::id();

        return $this->repositorio->crear($atributos);
    }

    public function actualizar(Contrato $contrato, ContratoData $datos): Contrato
    {
        $atributos = $datos->toModelArray();
        $atributos['updated_by'] = Auth::id();

        return $this->repositorio->actualizar($contrato, $atributos);
    }

    public function eliminar(Contrato $contrato): bool
    {
        return $this->repositorio->eliminar($contrato);
    }
}
