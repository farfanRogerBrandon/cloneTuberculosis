<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * Class Empleado
 * 
 * @property int $id
 * @property string $codigoEmpleado
 * @property string $nombreUsuario
 * @property string $password
 * @property int $idEstablecimiento
 * @property string $rol
 * @property Carbon $fechaRegistro
 * @property Carbon|null $fechaActualizacion
 * @property int $registradoPor
 * 
 * @property Establecimiento $establecimiento
 * @property Collection|Dosi[] $dosis
 *
 * @package App\Models
 */
class Empleado extends Model implements AuthenticatableContract
{
	use Authenticatable;
	use Notifiable;
	protected $table = 'empleado';
	public $timestamps = false;

	protected $casts = [
		'idEstablecimiento' => 'int',
		'fechaRegistro' => 'datetime',
		'fechaActualizacion' => 'datetime',
		'registradoPor' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'codigoEmpleado',
		'nombreUsuario',
		'password',
		'idEstablecimiento',
		'rol',
		'fechaRegistro',
		'fechaActualizacion',
		'registradoPor'
	];

	public function establecimiento()
	{
		return $this->belongsTo
		(Establecimiento::class, 
		'idEstablecimiento');
	}

	public function dosis()
	{
		return $this->hasMany
		(Dosi::class, 'registradoPor');
	}
}
