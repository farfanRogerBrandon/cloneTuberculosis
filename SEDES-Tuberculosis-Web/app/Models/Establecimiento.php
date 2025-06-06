<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Establecimiento
 * 
 * @property int $id
 * @property string $Abreviacion
 * @property string $nombre
 * @property string|null $telefono
 * @property int $idProvincia
 * @property string $estado
 * @property Carbon $fechaRegistro
 * @property Carbon|null $fechaActualizacion
 * @property int $registradoPor
 * 
 * @property Provincium $provincium
 * @property Collection|Empleado[] $empleados
 * @property Collection|Historialmedico[] $historialmedicos
 * @property Collection|Paciente[] $pacientes
 *
 * @package App\Models
 */
class Establecimiento extends Model
{
	protected $table = 'establecimiento';
	public $timestamps = false;

	protected $casts = [
		'idProvincia' => 'int',
		'fechaRegistro' => 'datetime',
		'fechaActualizacion' => 'datetime',
		'registradoPor' => 'int'
	];

	protected $fillable = [
		'Abreviacion',
		'nombre',
		'telefono',
		'idProvincia',
		'estado',
		'fechaRegistro',
		'fechaActualizacion',
		'registradoPor'
	];

	public function provincium()
	{
		return $this->belongsTo(Provincium::class, 'idProvincia');
	}

	public function empleados()
	{
		return $this->hasMany(Empleado::class, 'idEstablecimiento');
	}

	public function historialmedicos()
	{
		return $this->hasMany(Historialmedico::class, 'idEstablecimiento');
	}

	public function pacientes()
	{
		return $this->hasMany(Paciente::class, 'idEstablecimiento');
	}
}
