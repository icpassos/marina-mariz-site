<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use App\Rules\ArquivoDeMidia;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Arquivo')
                    ->disk('local')
                    ->directory('midia')
                    ->visibility('private')
                    ->storeFileNamesIn('original_name')
                    ->maxSize(50 * 1024)
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
                    ->required()
                    ->helperText('Imagem sai convertida em WebP, no máximo 1920px de largura. Documento, vídeo e áudio até 50MB.')
                    // O arquivo e a identidade do registro: trocar depois
                    // deixaria os metadados apontando para outro conteudo.
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
            ]);
    }
}
