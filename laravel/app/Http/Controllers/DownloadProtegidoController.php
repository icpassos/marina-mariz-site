<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Download de material gratuito e e-book gratuito (docs 01, 04 e 08).
 *
 * O link e assinado pelo Laravel e vale exatamente 3 dias. Isso reduz
 * descoberta casual; nao impede repasse do link dentro da validade nem
 * copia do arquivo depois de baixado.
 */
class DownloadProtegidoController extends Controller
{
    public const VALIDADE_EM_DIAS = 3;

    public static function linkPara(Media $media): string
    {
        return URL::temporarySignedRoute(
            'download.protegido',
            now()->addDays(self::VALIDADE_EM_DIAS),
            ['media' => $media->getKey()],
        );
    }

    public function __invoke(Request $request, Media $media): StreamedResponse
    {
        $disco = Storage::disk('local');

        abort_unless($disco->exists($media->path), 404);

        return $disco->download($media->path, $media->original_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
