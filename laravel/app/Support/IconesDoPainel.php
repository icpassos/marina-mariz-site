<?php

namespace App\Support;

use App\Enums\Icone;
use Filament\Support\Facades\FilamentIcon;

/**
 * Troca os icones que o Filament traz de fabrica (Heroicon) pelos do site
 * (Phosphor, DS_Marina.html). Sem isto o painel misturava duas familias na
 * mesma tela: o icone do menu era do DS e o da lixeira, do Filament.
 *
 * Cobre o que aparece o tempo todo — acoes, paginacao, busca, avisos e os
 * botoes do topo. O que nao esta aqui e tela que a equipe nao usa (query
 * builder, multi-tenant, editor de imagem).
 */
class IconesDoPainel
{
    public static function registrar(): void
    {
        FilamentIcon::register([
            // Acoes de linha e de formulario.
            'actions::create-action.grouped' => Icone::Plus,
            'actions::edit-action' => Icone::PencilSimple,
            'actions::edit-action.grouped' => Icone::PencilSimple,
            'actions::view-action' => Icone::Eye,
            'actions::view-action.grouped' => Icone::Eye,
            'actions::delete-action' => Icone::Trash,
            'actions::delete-action.grouped' => Icone::Trash,
            'actions::delete-action.modal' => Icone::Trash,
            'actions::force-delete-action' => Icone::Trash,
            'actions::force-delete-action.grouped' => Icone::Trash,
            'actions::force-delete-action.modal' => Icone::Trash,
            'actions::restore-action' => Icone::ArrowUUpLeft,
            'actions::restore-action.grouped' => Icone::ArrowUUpLeft,
            'actions::restore-action.modal' => Icone::ArrowUUpLeft,
            'actions::action-group' => Icone::DotsThreeVertical,
            'actions::modal.confirmation' => Icone::Question,
            'actions::export-action.grouped' => Icone::DownloadSimple,
            'actions::import-action.grouped' => Icone::UploadSimple,

            // Fechar: o mesmo X do site.
            'modal.close-button' => Icone::X,
            'notifications::notification.close-button' => Icone::X,

            // Avisos, com as cores que o DS ja usa nesses estados.
            'notifications::notification.success' => Icone::CheckCircle,
            'notifications::notification.danger' => Icone::WarningCircle,
            'notifications::notification.warning' => Icone::Warning,
            'notifications::notification.info' => Icone::Info,
            'schema::components.callout.success' => Icone::CheckCircle,
            'schema::components.callout.danger' => Icone::WarningCircle,
            'schema::components.callout.warning' => Icone::Warning,
            'schema::components.callout.info' => Icone::Info,

            // Paginacao e trilha: os mesmos carets do menu do site.
            'pagination.previous-button' => Icone::CaretLeft,
            'pagination.next-button' => Icone::CaretRight,
            'pagination.first-button' => Icone::CaretDoubleLeft,
            'pagination.last-button' => Icone::CaretDoubleRight,
            'pagination.previous-button.rtl' => Icone::CaretRight,
            'pagination.next-button.rtl' => Icone::CaretLeft,
            'pagination.first-button.rtl' => Icone::CaretDoubleRight,
            'pagination.last-button.rtl' => Icone::CaretDoubleLeft,
            'breadcrumbs.separator' => Icone::CaretRight,
            'breadcrumbs.separator.rtl' => Icone::CaretLeft,

            // Busca, filtro e listas vazias.
            'panels::global-search.field' => Icone::MagnifyingGlass,
            'tables::search-field' => Icone::MagnifyingGlass,
            'forms::components.checkbox-list.search-field' => Icone::MagnifyingGlass,
            'tables::actions.filter' => Icone::Funnel,
            'tables::filters.remove-all-button' => Icone::X,
            'tables::empty-state' => Icone::FileDashed,
            'widgets::chart-widget.empty-state' => Icone::FileDashed,
            'notifications::database.modal.empty-state' => Icone::FileDashed,

            // Ordenacao e recolher.
            'tables::header-cell.sort-button' => Icone::ArrowsDownUp,
            'tables::header-cell.sort-asc-button' => Icone::ArrowUp,
            'tables::header-cell.sort-desc-button' => Icone::ArrowDown,
            'section.collapse-button' => Icone::CaretUp,
            'tables::columns.collapse-button' => Icone::CaretUp,
            'tables::grouping.collapse-button' => Icone::CaretUp,

            // Certo e errado nas tabelas e fichas.
            'tables::columns.icon-column.true' => Icone::CheckCircle,
            'tables::columns.icon-column.false' => Icone::XCircle,
            'infolists::components.icon-entry.true' => Icone::CheckCircle,
            'infolists::components.icon-entry.false' => Icone::XCircle,
            'forms::components.toggle-buttons.boolean.true' => Icone::CheckCircle,
            'forms::components.toggle-buttons.boolean.false' => Icone::XCircle,

            // Campos.
            'forms::components.text-input.actions.show-password' => Icone::Eye,
            'forms::components.text-input.actions.hide-password' => Icone::EyeSlash,
            'forms::components.text-input.actions.copy' => Icone::Copy,
            'forms::components.select.actions.create-option' => Icone::Plus,
            'forms::components.select.actions.edit-option' => Icone::PencilSimple,

            // Topo do painel.
            'panels::topbar.group.toggle-button' => Icone::CaretDown,
            'panels::topbar.open-sidebar-button' => Icone::List,
            'panels::topbar.close-sidebar-button' => Icone::X,
            'panels::topbar.open-database-notifications-button' => Icone::Bell,
            'panels::sidebar.open-database-notifications-button' => Icone::Bell,
            'panels::sidebar.collapse-button' => Icone::CaretLeft,
            'panels::sidebar.expand-button' => Icone::CaretRight,
            'panels::sidebar.group.collapse-button' => Icone::CaretUp,
            'panels::user-menu.toggle-button' => Icone::UserCircle,
            'panels::user-menu.profile-item' => Icone::UserCircle,
            'panels::user-menu.logout-button' => Icone::SignOut,
            'panels::theme-switcher.light-button' => Icone::Sun,
            'panels::theme-switcher.dark-button' => Icone::MoonStars,
            'panels::theme-switcher.system-button' => Icone::Desktop,
            'panels::sub-navigation.mobile-menu.button' => Icone::List,
            'panels::widgets.account.logout-button' => Icone::SignOut,

            // Navegacao interna do painel.
            'panels::pages.dashboard.navigation-item' => Icone::House,
            'panels::pages.dashboard.actions.filter' => Icone::Funnel,
            'panels::resources.pages.edit-record.navigation-item' => Icone::PencilSimple,
            'panels::resources.pages.view-record.navigation-item' => Icone::Eye,
            'panels::resources.pages.manage-related-records.navigation-item' => Icone::List,

            // Barra de ferramentas da tabela.
            'tables::actions.open-bulk-actions' => Icone::ListChecks,
            'tables::actions.group' => Icone::SquaresFour,
            'tables::actions.column-manager' => Icone::Columns,
            'tables::actions.enable-reordering' => Icone::ArrowsDownUp,
            'tables::actions.disable-reordering' => Icone::Check,
            'tables::reorder.handle' => Icone::DotsSixVertical,
            'badge.delete-button' => Icone::X,

            // Abas, assistente e campos que se repetem.
            'schema::components.tabs.dropdown-trigger-button' => Icone::CaretDown,
            'schema::components.tabs.more-tabs-button' => Icone::DotsThree,
            'schema::components.wizard.completed-step' => Icone::Check,
            'forms::components.repeater.actions.clone' => Icone::Copy,
            'forms::components.repeater.actions.collapse' => Icone::CaretUp,
            'forms::components.repeater.actions.expand' => Icone::CaretDown,
            'forms::components.repeater.actions.delete' => Icone::Trash,
            'forms::components.repeater.actions.move-up' => Icone::ArrowUp,
            'forms::components.repeater.actions.move-down' => Icone::ArrowDown,
            'forms::components.repeater.actions.reorder' => Icone::DotsSixVertical,
            'forms::components.builder.actions.clone' => Icone::Copy,
            'forms::components.builder.actions.collapse' => Icone::CaretUp,
            'forms::components.builder.actions.expand' => Icone::CaretDown,
            'forms::components.builder.actions.delete' => Icone::Trash,
            'forms::components.builder.actions.move-up' => Icone::ArrowUp,
            'forms::components.builder.actions.move-down' => Icone::ArrowDown,
            'forms::components.builder.actions.reorder' => Icone::DotsSixVertical,
            'forms::components.key-value.actions.delete' => Icone::Trash,
            'forms::components.key-value.actions.reorder' => Icone::DotsSixVertical,
            'forms::components.rich-editor.panels.custom-block.delete-button' => Icone::Trash,
            'forms::components.rich-editor.panels.custom-block.edit-button' => Icone::PencilSimple,
            'forms::components.rich-editor.panels.custom-blocks.close-button' => Icone::X,
            'forms::components.rich-editor.panels.merge-tags.close-button' => Icone::X,
        ]);
    }
}
