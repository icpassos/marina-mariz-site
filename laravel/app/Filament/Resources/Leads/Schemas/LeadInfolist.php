<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Filament\Schemas\SecaoOutbox;
use App\Models\Lead;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead')
                ->schema([
                    TextEntry::make('nome')->label('Nome'),
                    TextEntry::make('email')->label('E-mail')->copyable(),
                    TextEntry::make('telefone')->label('Telefone')->placeholder('—'),
                    TextEntry::make('created_at')->label('Primeiro contato')->dateTime('d/m/Y H:i'),
                ])
                ->columns(2),

            Section::make('Histórico de downloads')
                ->schema([
                    TextEntry::make('historico')
                        ->hiddenLabel()
                        ->state(fn (Lead $record): string => $record->downloads()
                            ->latest('id')
                            ->get()
                            ->map(fn ($d): string => $d->created_at->format('d/m/Y H:i').' — '.$d->origem)
                            ->implode("\n"))
                        ->placeholder('Nenhum download.'),
                ]),

            Section::make('Consentimentos')
                ->collapsed()
                ->schema([
                    TextEntry::make('consentimentosResumo')
                        ->hiddenLabel()
                        ->state(fn (Lead $record): string => $record->consentimentos
                            ->map(fn ($c): string => sprintf(
                                '%s · aviso %s · %s%s',
                                $c->finalidade,
                                $c->versao_aviso,
                                $c->concedido_em->format('d/m/Y H:i'),
                                $c->revogado_em ? ' · revogado em '.$c->revogado_em->format('d/m/Y H:i') : '',
                            ))
                            ->implode("\n"))
                        ->placeholder('Nenhum consentimento opcional.'),
                ]),

            Section::make('Privacidade')
                ->collapsed()
                ->schema([
                    TextEntry::make('politica_versao')->label('Versão da Política aceita'),
                    TextEntry::make('ip_hash')->label('IP pseudonimizado')->placeholder('—')
                        ->helperText('HMAC do IP. O IP em claro não é guardado.'),
                ])
                ->columns(2),

            SecaoOutbox::secao(),
        ]);
    }
}
