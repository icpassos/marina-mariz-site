<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use App\Rules\ArquivoDeMidia;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        // Duas colunas, como a tela de anexo do WordPress: o arquivo e o que
        // se escreve sobre ele a esquerda, onde ele esta em uso a direita.
        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'fi-wp-edicao'])
            ->components([
                Group::make([
                    Section::make('Arquivo')->schema([
                        self::campoDeArquivo()
                            ->storeFileNamesIn('original_name')
                            ->required()
                            // Trocar o arquivo é a ação "Substituir arquivo": ela relê os
                            // metadados e apaga o antigo do disco (doc 05).
                            ->hiddenOn('edit'),

                        TextInput::make('name')
                            ->label('Nome')
                            ->maxLength(255)
                            ->helperText('Como este arquivo aparece na busca da biblioteca. Em branco, usa o nome do arquivo enviado.'),

                        // Alt so existe para imagem. Documento, video e audio
                        // nao tem texto alternativo.
                        Toggle::make('is_decorative')
                            ->label('Imagem decorativa')
                            ->helperText('Marque quando a imagem não acrescenta informação. O alt fica vazio de propósito e leitores de tela a ignoram.')
                            ->live()
                            ->visible(fn (?Media $record): bool => (bool) $record?->isImage()),

                        TextInput::make('alt')
                            ->label('Texto alternativo (alt)')
                            ->maxLength(255)
                            ->helperText('Descreva o que a imagem mostra, para quem não pode vê-la.')
                            ->required(fn (Get $get): bool => ! $get('is_decorative'))
                            ->disabled(fn (Get $get): bool => (bool) $get('is_decorative'))
                            ->dehydrateStateUsing(fn (?string $state, Get $get): ?string => $get('is_decorative') ? null : $state)
                            ->visible(fn (?Media $record): bool => (bool) $record?->isImage()),

                    ]),
                ]),

                Group::make([
                    Section::make('Uso')->schema([
                        Placeholder::make('usos')
                            ->label('Usado em')
                            ->content(fn (?Media $record): string => self::usos($record))
                            ->visibleOn('edit'),
                    ]),
                ])->visibleOn('edit'),
            ]);
    }

    /** O mesmo campo do envio pela biblioteca e da ação "Substituir arquivo". */
    public static function campoDeArquivo(): FileUpload
    {
        return FileUpload::make('path')
            ->label('Arquivo')
            ->disk('local')
            ->directory('midia')
            ->visibility('private')
            ->maxSize((int) (ArquivoDeMidia::TAMANHO_MAXIMO / 1024))
            // Origem aceita. Imagem entra JPEG/PNG/WebP e sai WebP,
            // convertida no navegador; o servidor confere depois.
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/pdf',
                'application/epub+zip',
                'text/csv',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'video/mp4',
                'video/webm',
                'audio/mpeg',
                'audio/mp4',
                'audio/wav',
            ])
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth((string) ArquivoDeMidia::LARGURA_MAXIMA)
            ->rules([new ArquivoDeMidia])
            ->helperText('Imagem sai convertida em WebP, no máximo 1920px de largura. Documento, vídeo e áudio até 50MB.');
    }

    private static function usos(?Media $record): string
    {
        $usos = $record?->usos() ?? [];

        return $usos === []
            ? 'Ainda não está sendo usado em lugar nenhum.'
            : implode(' · ', $usos);
    }
}
