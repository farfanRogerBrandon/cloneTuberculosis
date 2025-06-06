<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Historialmedico
 * 
 * @property int $id
 * @property string $descripcion
 * @property string $imagen
 * @property string $Origen
 * @property string $Destino
 * @property int $idPaciente
 * @property int $idEstablecimiento
 * @property string $estado
 * @property Carbon $fechaRegistro
 * @property Carbon|null $fechaActualizacion
 * @property int $registradoPor
 * 
 * @property Paciente $paciente
 * @property Establecimiento $establecimiento
 *
 * @package App\Models
 */
class Historialmedico extends Model
{
	protected $table = 'historialmedico';
	public $timestamps = false;

	protected $casts = [
		'idPaciente' => 'int',
		'idEstablecimiento' => 'int',
		'fechaRegistro' => 'datetime',
		'fechaActualizacion' => 'datetime',
		'registradoPor' => 'int'
	];

	protected $fillable = [
		'descripcion',
		'imagen',
		'Origen',
		'Destino',
		'idPaciente',
		'idEstablecimiento',
		'estado',
		'fechaRegistro',
		'fechaActualizacion',
		'registradoPor'
	];

	public function paciente()
	{
		return $this->belongsTo(Paciente::class, 'idPaciente');
	}

	public function establecimiento()
	{
		return $this->belongsTo(Establecimiento::class, 'idEstablecimiento');
	}
}
