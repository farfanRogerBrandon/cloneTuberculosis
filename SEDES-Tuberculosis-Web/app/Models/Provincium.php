<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Provincium
 * 
 * @property int $id
 * @property string $nombre
 * @property int $idDepartamento
 * 
 * @property Departamento $departamento
 * @property Collection|Establecimiento[] $establecimientos
 *
 * @package App\Models
 */
class Provincium extends Model
{
	protected $table = 'provincia';
	public $timestamps = false;

	protected $casts = [
		'idDepartamento' => 'int'
	];

	protected $fillable = [
		'nombre',
		'idDepartamento'
	];

	public function departamento()
	{
		return $this->belongsTo(Departamento::class, 'idDepartamento');
	}

	public function establecimientos()
	{
		return $this->hasMany(Establecimiento::class, 'idProvincia');
	}
}
