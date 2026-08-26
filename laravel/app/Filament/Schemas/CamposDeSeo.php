<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * SEO por item (doc 00): mesmos tres campos em todo modulo, com o mesmo
 * comportamento — vazio significa usar o titulo e a descricao do proprio
 * item, nunca um texto inventado.
 */
class CamposDeSeo
{
    public static function secao(): Section
    {
        return Section::make('SEO')
            ->description('Em branco, o site usa o título e a descrição do próprio item.')
            ->collapsed()
            ->schema([
                TextInput::make('seo_title')
                    ->label('Título SEO')
                    ->maxLength(60)
                    ->helperText('Até 60 caracteres. Acima disso o Google corta.'),

                Textarea::make('seo_description')
                    ->label('Descrição SEO')
                    ->rows(2)
                    ->maxLength(160)
                    ->helperText('Até 160 caracteres.'),

                // Envio direto, como todo campo de arquivo do painel: a
                // biblioteca e o registro do que foi enviado, nao a porta
                // de entrada.
                EnvioDeMidia::imagem('seo_image_id', 'Imagem de compartilhamento')
                    ->helperText('Aparece quando o link é compartilhado no WhatsApp ou nas redes. Entra na Biblioteca de Mídia.'),
            ]);
    }
}
