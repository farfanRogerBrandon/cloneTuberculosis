<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * Class Paciente
 * 
 * @property int $id
 * @property string $ci
 * @property string $nombres
 * @property string $primerApellido
 * @property string|null $segundoApellido
 * @property string $celular
 * @property string $sexo
 * @property Carbon $fechaNacimiento
 * @property string $nombreUsuario
 * @property int $idEstablecimiento
 * @property string $estado
 * @property Carbon $fechaRegistro
 * @property Carbon|null $fechaActualizacion
 * @property int $registradoPor
 * 
 * @property Establecimiento $establecimiento
 * @property Collection|Dosi[] $dosis
 * @property Collection|Historialmedico[] $historialmedicos
 *
 * @package App\Models
 */
class Paciente extends Authenticatable
{
	use HasApiTokens, Notifiable;
	protected $table = 'paciente';
	public $timestamps = false;

	protected $casts = [
		'fechaNacimiento' => 'datetime',
		'idEstablecimiento' => 'int',
		'fechaRegistro' => 'datetime',
		'fechaActualizacion' => 'datetime',
		'registradoPor' => 'int'
	];

	protected $fillable = [
		'ci',
		'nombres',
		'primerApellido',
		'segundoApellido',
		'celular',
		'sexo',
		'fechaNacimiento',
		'nombreUsuario',
		'idEstablecimiento',
		'estado',
		'fechaRegistro',
		'fechaActualizacion',
		'registradoPor'
	];

	public function establecimiento()
	{
		return $this->belongsTo(Establecimiento::class, 'idEstablecimiento');
	}

	public function dosis()
	{
		return $this->hasMany(Dosi::class, 'idPaciente');
	}

	public function historialmedicos()
	{
		return $this->hasMany(Historialmedico::class, 'idPaciente');
	}
}
