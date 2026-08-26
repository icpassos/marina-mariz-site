<?php

namespace App\Enums;

/**
 * Os icones do painel sao os mesmos do site: Phosphor regular, a familia do
 * DS (DS_Marina.html). O site carrega a fonte por CDN; aqui os SVG moram em
 * `resources/svg/phosphor` e entram em linha, sem CDN nenhuma.
 *
 * O nome do case e o nome do icone no Phosphor, nao o lugar onde ele aparece:
 * o mesmo desenho serve a mais de uma tela e um `enum` nao repete valor.
 */
enum Icone: string
{
    case Archive = 'ph-archive';
    case ArrowDown = 'ph-arrow-down';
    case ArrowUUpLeft = 'ph-arrow-u-up-left';
    case ArrowUp = 'ph-arrow-up';
    case ArrowUpRight = 'ph-arrow-up-right';
    case ArrowsClockwise = 'ph-arrows-clockwise';
    case ArrowsDownUp = 'ph-arrows-down-up';
    case Bell = 'ph-bell';
    case BookOpen = 'ph-book-open';
    case CalendarBlank = 'ph-calendar-blank';
    case CalendarCheck = 'ph-calendar-check';
    case CaretDoubleLeft = 'ph-caret-double-left';
    case CaretDoubleRight = 'ph-caret-double-right';
    case CaretDown = 'ph-caret-down';
    case CaretLeft = 'ph-caret-left';
    case CaretRight = 'ph-caret-right';
    case CaretUp = 'ph-caret-up';
    case ChatsCircle = 'ph-chats-circle';
    case Check = 'ph-check';
    case CheckCircle = 'ph-check-circle';
    case Columns = 'ph-columns';
    case Copy = 'ph-copy';
    case Database = 'ph-database';
    case Desktop = 'ph-desktop';
    case DotsSixVertical = 'ph-dots-six-vertical';
    case DotsThree = 'ph-dots-three';
    case DotsThreeVertical = 'ph-dots-three-vertical';
    case DownloadSimple = 'ph-download-simple';
    case EnvelopeOpen = 'ph-envelope-open';
    case EnvelopeSimple = 'ph-envelope-simple';
    case Eye = 'ph-eye';
    case EyeSlash = 'ph-eye-slash';
    case FileDashed = 'ph-file-dashed';
    case FileText = 'ph-file-text';
    case Funnel = 'ph-funnel';
    case Gear = 'ph-gear';
    case GraduationCap = 'ph-graduation-cap';
    case House = 'ph-house';
    case IdentificationBadge = 'ph-identification-badge';
    case Image = 'ph-image';
    case Info = 'ph-info';
    case LinkSimple = 'ph-link-simple';
    case List = 'ph-list';
    case ListChecks = 'ph-list-checks';
    case MagnifyingGlass = 'ph-magnifying-glass';
    case Medal = 'ph-medal';
    case MoonStars = 'ph-moon-stars';
    case NotePencil = 'ph-note-pencil';
    case PencilSimple = 'ph-pencil-simple';
    case Phone = 'ph-phone';
    case Plus = 'ph-plus';
    case Question = 'ph-question';
    case SealCheck = 'ph-seal-check';
    case SignOut = 'ph-sign-out';
    case SpinnerGap = 'ph-spinner-gap';
    case SquaresFour = 'ph-squares-four';
    case Sun = 'ph-sun';
    case Tag = 'ph-tag';
    case Trash = 'ph-trash';
    case UploadSimple = 'ph-upload-simple';
    case UserCircle = 'ph-user-circle';
    case UserPlus = 'ph-user-plus';
    case UsersThree = 'ph-users-three';
    case Warning = 'ph-warning';
    case WarningCircle = 'ph-warning-circle';
    case X = 'ph-x';
    case XCircle = 'ph-x-circle';
}
