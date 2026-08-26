<?php

namespace App\Models;

use App\Enums\OrigemMensagem;
use App\Enums\StatusMensagem;
use App\Filament\Resources\Mensagens\MensagemResource;
use App\Support\VaiParaOutbox;
use Database\Factories\MensagemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Caixa de entrada dos formularios de Contato e Formacao Profissional
 * (doc 04). Registro nasce do site; o painel so le, anota e arquiva.
 */
class Mensagem extends Model implements VaiParaOutbox
{
    /** @use HasFactory<MensagemFactory> */
    use HasFactory;

    protected $table = 'mensagens';

    protected $fillable = [
        'submission_id',
        'origem',
        'nome',
        'email',
        'whatsapp',
        'texto',
        'profissao',
        'registro_profissional',
        'instituicao',
        'modalidade',
        'status',
        'anotacao',
        'politica_versao',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'origem' => OrigemMensagem::class,
            'status' => StatusMensagem::class,
        ];
    }

    public function assuntoDoEmail(): string
    {
        return "[Site] Nova mensagem de {$this->nome}";
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
            'Origem' => $this->origem->getLabel(),
        ];
    }

    public function linkNoPainel(): string
    {
        return MensagemResource::getUrl('view', ['record' => $this]);
    }
}
