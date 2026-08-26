<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Os 6 tipos do catalogo (doc 01). Um unico conteudo com um campo TIPO:
 * nao existem seis tabelas nem seis CRUDs.
 */
enum EducationType: string implements HasLabel
{
    case Livro = 'livro';
    case Ebook = 'ebook';
    case Curso = 'curso';
    case Evento = 'evento';
    case Material = 'material';
    case Formacao = 'formacao';

    public function getLabel(): string
    {
        return match ($this) {
            self::Livro => 'Livro',
            self::Ebook => 'E-book',
            self::Curso => 'Curso',
            self::Evento => 'Evento',
            self::Material => 'Material gratuito',
            self::Formacao => 'Formação profissional',
        };
    }

    /** Como o tipo aparece no menu e no titulo da lista. */
    public function plural(): string
    {
        return match ($this) {
            self::Livro => 'Livros',
            self::Ebook => 'E-books',
            self::Curso => 'Cursos',
            self::Evento => 'Eventos',
            self::Material => 'Materiais gratuitos',
            self::Formacao => 'Formação profissional',
        };
    }

    /**
     * O que a entrada e, de onde vem e para onde vai (doc 01). Aparece no
     * topo da lista: a Marina precisa saber, olhando a tela, em que pagina
     * do site o item vai parar.
     */
    public function explicacao(): string
    {
        return match ($this) {
            self::Livro => 'Livros à venda, com link para a loja. Publicado, o item aparece em /educacao/livros e não tem página própria no site. Quem clica em "quero ser avisada" nessa página entra em Newsletter.',
            self::Ebook => 'E-books pagos ou gratuitos, listados em /educacao/ebooks. Marcado como gratuito, o mesmo item aparece também em /educacao/materiais-gratuitos, sem recadastro, e o download vira lead.',
            self::Curso => 'Cursos com inscrição fora do site: aparecem em /educacao/cursos e o botão leva ao link externo, em nova aba. Não gera lead.',
            self::Evento => 'Agenda de /educacao/eventos. O site move o evento para "Já aconteceram" sozinho quando a data de fim passa; quem pede aviso nessa página entra em Newsletter.',
            self::Material => 'Arquivos para baixar em /educacao/materiais-gratuitos. Com "exige e-mail" ligado, o download pede nome e e-mail e vira lead; o link vale três dias.',
            self::Formacao => 'Mentoria, supervisão, in company e palestra, em /educacao/formacao-profissional. O formulário dessa página cai em Mensagens, com profissão, registro e instituição.',
        };
    }

    public function icone(): Icone
    {
        return match ($this) {
            self::Livro => Icone::BookOpen,
            self::Ebook => Icone::FileText,
            self::Curso => Icone::GraduationCap,
            self::Evento => Icone::CalendarBlank,
            self::Material => Icone::DownloadSimple,
            self::Formacao => Icone::Medal,
        };
    }
}
