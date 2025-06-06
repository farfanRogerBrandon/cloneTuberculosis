<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Dosi
 * 
 * @property int $id
 * @property int $idPaciente
 * @property int $nroDosis
 * @property string|null $rutaVideo
 * @property Carbon|null $fechaGrabacion
 * @property string|null $descripcion
 * @property string $estado
 * @property int $registradoPor
 * @property Carbon $fechaRegistro
 * @property Carbon|null $fechaActualizacion
 * 
 * @property Paciente $paciente
 * @property Empleado $empleado
 * @property Collection|Notificacion[] $notificacions
 *
 * @package App\Models
 */
class Dosi extends Model
{
	protected $table = 'dosis';
	public $timestamps = false;

	protected $casts = [
		'idPaciente' => 'int',
		'nroDosis' => 'int',
		'fechaGrabacion' => 'datetime',
		'registradoPor' => 'int',
		'fechaRegistro' => 'datetime',
		'fechaActualizacion' => 'datetime'
	];

	protected $fillable = [
		'idPaciente',
		'nroDosis',
		'rutaVideo',
		'fechaGrabacion',
		'descripcion',
		'estado',
		'registradoPor',
		'fechaRegistro',
		'fechaActualizacion'
	];

	public function paciente()
	{
		return $this->belongsTo(Paciente::class, 'idPaciente');
	}

	public function empleado()
	{
		return $this->belongsTo(Empleado::class, 'registradoPor');
	}

	public function notificacions()
	{
		return $this->hasMany(Notificacion::class, 'idDosis');
	}
}
