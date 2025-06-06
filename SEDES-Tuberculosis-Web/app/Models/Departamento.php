<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Departamento
 * 
 * @property int $id
 * @property string $nombre
 * 
 * @property Collection|Provincium[] $provincia
 *
 * @package App\Models
 */
class Departamento extends Model
{
	protected $table = 'departamento';
	public $timestamps = false;

	protected $fillable = [
		'nombre'
	];

	public function provincia()
	{
		return $this->hasMany(Provincium::class, 'idDepartamento');
	}
}
