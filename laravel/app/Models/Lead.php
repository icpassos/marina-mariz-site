<?php

namespace App\Models;

use App\Filament\Resources\Leads\LeadResource;
use App\Support\VaiParaOutbox;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Quem baixou material gratuito ou e-book gratuito (doc 04).
 *
 * Mesmo e-mail = um lead so, com historico crescente em lead_downloads.
 * Nunca dois registros da mesma pessoa.
 */
class Lead extends Model implements VaiParaOutbox
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'nome',
        'telefone',
        'eh_cliente',
        'politica_versao',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'eh_cliente' => 'boolean',
        ];
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(LeadDownload::class);
    }

    public function consentimentos(): MorphMany
    {
        return $this->morphMany(Consentimento::class, 'consentivel');
    }

    /** O download que originou o envio em processamento na outbox. */
    public function downloadDe(string $submissionId): ?LeadDownload
    {
        return $this->downloads()->where('submission_id', $submissionId)->first();
    }

    public function assuntoDoEmail(): string
    {
        return '[Site] Novo lead: '.($this->downloads()->latest('id')->value('origem') ?? 'material');
    }

    public function responderPara(): string
    {
        return $this->email;
    }

    public function linhasDoEmail(): array
    {
        return [
            'Nome' => $this->nome,
            'E-mail' => $this->email,
            'Material' => (string) $this->downloads()->latest('id')->value('origem'),
        ];
    }

    public function linkNoPainel(): string
    {
        return LeadResource::getUrl('view', ['record' => $this]);
    }
}
