<?php

namespace App\Filament\Resources\Mensagens\Schemas;

use App\Enums\OrigemMensagem;
use App\Filament\Schemas\SecaoOutbox;
use App\Models\Mensagem;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MensagemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Mensagem')
                ->schema([
                    TextEntry::make('nome')->label('Nome'),
                    TextEntry::make('email')->label('E-mail')->copyable(),
                    TextEntry::make('whatsapp')->label('WhatsApp')->placeholder('—'),
                    TextEntry::make('origem')->label('Origem')->badge(),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('created_at')->label('Recebida em')->dateTime('d/m/Y H:i'),
                    TextEntry::make('texto')->label('Texto')->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Formação profissional')
                ->visible(fn (Mensagem $record): bool => $record->origem === OrigemMensagem::FormacaoProfissional)
                ->schema([
                    TextEntry::make('profissao')->label('Profissão')->placeholder('—'),
                    TextEntry::make('registro_profissional')->label('CRM/COREN')->placeholder('—'),
                    TextEntry::make('instituicao')->label('Instituição')->placeholder('—'),
                    TextEntry::make('modalidade')->label('Modalidade de interesse')->placeholder('—'),
                ])
                ->columns(2),

            Section::make('Anotação interna')
                ->collapsed()
                ->schema([
                    TextEntry::make('anotacao')->hiddenLabel()->placeholder('Sem anotação.'),
                ]),

            Section::make('Privacidade')
                ->collapsed()
                ->schema([
                    TextEntry::make('politica_versao')->label('Versão da Política aceita'),
                    TextEntry::make('ip_hash')->label('IP pseudonimizado')->placeholder('—')
                        ->helperText('HMAC do IP. O IP em claro não é guardado.'),
                    TextEntry::make('submission_id')->label('Envio'),
                ])
                ->columns(3),

            SecaoOutbox::secao(),
        ]);
    }
}
