<?php

namespace App\Filament\Resources\Newsletter\Schemas;

use App\Filament\Schemas\SecaoOutbox;
use App\Models\InscricaoNewsletter;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InscricaoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inscrito')
                ->schema([
                    TextEntry::make('email')->label('E-mail')->copyable(),
                    TextEntry::make('nome')->label('Nome')->placeholder('—'),
                    TextEntry::make('origem')->label('Origem')->badge(),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('created_at')->label('Inscrição')->dateTime('d/m/Y H:i'),
                    TextEntry::make('descadastrado_em')->label('Descadastro')->dateTime('d/m/Y H:i')->placeholder('—'),
                ])
                ->columns(3),

            Section::make('Consentimento')
                ->schema([
                    TextEntry::make('consentimentosResumo')
                        ->hiddenLabel()
                        ->state(fn (InscricaoNewsletter $record): string => $record->consentimentos
                            ->map(fn ($c): string => sprintf(
                                '%s · aviso %s · %s%s',
                                $c->finalidade,
                                $c->versao_aviso,
                                $c->concedido_em->format('d/m/Y H:i'),
                                $c->revogado_em ? ' · revogado em '.$c->revogado_em->format('d/m/Y H:i') : '',
                            ))
                            ->implode("\n"))
                        ->placeholder('Nenhum consentimento registrado.'),
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
