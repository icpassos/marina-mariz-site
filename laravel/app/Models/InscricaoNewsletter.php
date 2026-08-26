<?php

namespace App\Models;

use App\Enums\StatusInscricao;
use App\Filament\Resources\Newsletter\InscricaoNewsletterResource;
use App\Support\VaiParaOutbox;
use Database\Factories\InscricaoNewsletterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * Lista de inscritos da newsletter (doc 04). Captura de e-mail e mais nada:
 * o disparo em si fica fora do painel.
 */
class InscricaoNewsletter extends Model implements VaiParaOutbox
{
    /** @use HasFactory<InscricaoNewsletterFactory> */
    use HasFactory;

    protected $table = 'inscricoes_newsletter';

    protected $fillable = [
        'email',
        'nome',
        'origem',
        'status',
        'submission_id',
        'descadastro_token',
        'descadastrado_em',
        'politica_versao',
        'ip_hash',
    ];

    protected $hidden = [
        'descadastro_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusInscricao::class,
            'descadastrado_em' => 'datetime',
        ];
    }

    public function consentimentos(): MorphMany
    {
        return $this->morphMany(Consentimento::class, 'consentivel');
    }

    public static function novoToken(): string
    {
        return Str::random(64);
    }

    public function linkDeDescadastro(): ?string
    {
        return $this->descadastro_token
            ? route('newsletter.descadastrar', ['token' => $this->descadastro_token])
            : null;
    }

    /**
     * Descadastro de um clique (doc 04). O token e de uso unico: some do
     * banco assim que serve, e o registro guarda quando foi.
     */
    public function descadastrar(): void
    {
        $this->forceFill([
            'status' => StatusInscricao::Descadastrado,
            'descadastro_token' => null,
            'descadastrado_em' => now(),
        ])->save();

        $this->consentimentos()
            ->where('finalidade', Consentimento::NEWSLETTER)
            ->whereNull('revogado_em')
            ->update(['revogado_em' => now()]);
    }

    public function assuntoDoEmail(): string
    {
        return '[Site] Nova inscrição na newsletter';
    }

    public function responderPara(): string
    {
        return $this->email;
    }

    public function linhasDoEmail(): array
    {
        return [
            'Nome' => (string) $this->nome,
            'E-mail' => $this->email,
            'Origem' => $this->origem,
        ];
    }

    public function linkNoPainel(): string
    {
        return InscricaoNewsletterResource::getUrl('view', ['record' => $this]);
    }
}
