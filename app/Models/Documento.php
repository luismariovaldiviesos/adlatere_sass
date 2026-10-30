<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'juicio_id',
        'tipo_documento_id',
        'estado_procesal_id',
        'origen_tipo',
        'origen_id',
        'nombre',
        'descripcion',
        'fecha_documento',
        'ruta_archivo',
        'tipo_archivo',
        'hash',
        'created_by',
        'tamaño_archivo',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
        'tamaño_archivo' => 'float',
    ];

    public function juicio()
    {
        return $this->belongsTo(Juicio::class);
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function estadoProcesal()
    {
        return $this->belongsTo(EstadoProcesal::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vinculos()
    {
        return $this->hasMany(DocumentoVinculo::class);
    }

    public function actividades()
    {
        return $this->morphedByMany(Actividad::class, 'vinculable', 'documento_vinculos');
    }

    public function audiencias()
    {
        return $this->morphedByMany(Audiencia::class, 'vinculable', 'documento_vinculos');
    }

    public function tareas()
    {
        return $this->morphedByMany(Tarea::class, 'vinculable', 'documento_vinculos');
    }

    public function consultas()
    {
        return $this->morphedByMany(Consulta::class, 'vinculable', 'documento_vinculos');
    }

    public function vincularA(Model $modelo, string $tipoVinculo = 'principal')
    {
        return $this->vinculos()->updateOrCreate(
            [
                'vinculable_type' => $modelo->getMorphClass(),
                'vinculable_id' => $modelo->getKey(),
            ],
            [
                'tipo_vinculo' => $tipoVinculo,
                'created_by' => auth()->id(),
            ]
        );
    }

    public function desvincularDe(Model $modelo)
    {
        return $this->vinculos()
            ->where('vinculable_type', $modelo->getMorphClass())
            ->where('vinculable_id', $modelo->getKey())
            ->delete();
    }

    public function getPesoKbAttribute()
    {
        return (float) ($this->tamaño_archivo ?? 0);
    }

    public function getVinculadoAAttribute(): string
    {
        $etiquetas = $this->vinculos
            ->sortBy(fn ($vinculo) => $vinculo->tipo_vinculo === 'principal' ? 0 : 1)
            ->map->etiqueta
            ->filter()
            ->values();

        return $etiquetas->isEmpty() ? 'Expediente' : $etiquetas->implode(' · ');
    }

    protected static function booted()
    {
        static::saved(fn ($d) => $d->juicio_id ? Juicio::recalcularUltimaActividad($d->juicio_id) : null);
        static::deleted(fn ($d) => $d->juicio_id ? Juicio::recalcularUltimaActividad($d->juicio_id) : null);
    }
}
