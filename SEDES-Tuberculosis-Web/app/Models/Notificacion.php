<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notificacion
 * 
 * @property int $id
 * @property int $id_usuario
 * @property string $tipo_usuario
 * @property string $titulo
 * @property string $mensaje
 * @property Carbon|null $leido_en
 * @property Carbon $creado_en
 * @property Carbon|null $actualizado_en
 * @property int|null $idDosis
 * 
 * @property Dosi|null $dosi
 *
 * @package App\Models
 */
class Notificacion extends Model
{
	protected $table = 'notificacion';
	public $timestamps = false;

	protected $casts = [
		'id_usuario' => 'int',
		'leido_en' => 'datetime',
		'creado_en' => 'datetime',
		'actualizado_en' => 'datetime',
		'idDosis' => 'int'
	];

	protected $fillable = [
		'id_usuario',
		'tipo_usuario',
		'titulo',
		'mensaje',
		'leido_en',
		'creado_en',
		'actualizado_en',
		'idDosis'
	];

	public function dosi()
	{
		return $this->belongsTo(Dosi::class, 'idDosis');
	}
}
