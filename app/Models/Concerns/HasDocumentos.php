<?php

namespace App\Models\Concerns;

use App\Models\Documento;
use App\Models\DocumentoVinculo;

trait HasDocumentos
{
    public function documentos()
    {
        return $this->morphToMany(Documento::class, 'vinculable', 'documento_vinculos')
            ->withPivot(['tipo_vinculo', 'created_by'])
            ->withTimestamps();
    }

    public function documentoVinculos()
    {
        return $this->morphMany(DocumentoVinculo::class, 'vinculable');
    }

    protected static function bootHasDocumentos()
    {
        static::deleting(function ($model) {
            $model->documentoVinculos()->delete();
        });
    }
}
