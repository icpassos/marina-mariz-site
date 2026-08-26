<?php

namespace App\Models;

use App\Services\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Linha unica de SEO padrao e IDs de Analytics (doc 06). */
class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $guarded = ['id'];

    public static function instancia(): self
    {
        return static::firstOrCreate([]);
    }

    public function imagemDeCompartilhamento(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_image_id');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Site::limparCache());
    }
}
