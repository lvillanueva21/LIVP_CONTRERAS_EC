(function (window, $) {
    'use strict';

    var AppTablas = {
        defaults: {
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            latestFirst: true
        },

        init: function () {
            this.prepareTables();
        },

        refresh: function () {
            $('[data-app-table="true"]').each(function () {
                var table = $(this);

                if (table.data('app-table-ready')) {
                    var wrapper = table.closest('.app-table-wrapper');
                    var originalParent = wrapper.parent();

                    table.insertBefore(wrapper);
                    wrapper.remove();
                    originalParent.find('[data-app-table="true"]').removeData('app-table-ready');
                }
            });

            this.prepareTables();
        },

        prepareTables: function () {
            $('[data-app-table="true"]').each(function () {
                AppTablas.build($(this));
            });
        },

        build: function (table) {
            if (table.data('app-table-ready')) {
                return;
            }

            var tbody = table.find('tbody').first();

            if (tbody.length === 0) {
                return;
            }

            table.data('app-table-ready', true);
            table.addClass('app-table table table-bordered table-hover');

            var rows = tbody.find('tr').toArray();
            var state = {
                table: table,
                tbody: tbody,
                rows: rows,
                filteredRows: rows.slice(0),
                page: 1,
                pageLength: this.getPageLength(table),
                search: '',
                emptyText: table.attr('data-empty-text') || 'No hay registros para mostrar.',
                emptyIcon: table.attr('data-empty-icon') || 'fas fa-inbox'
            };

            this.applyInitialOrder(state);
            this.wrapTable(state);
            this.render(state);
        },

        getPageLength: function (table) {
            var value = parseInt(table.attr('data-page-length'), 10);

            if (value === 20 || value === 50 || value === 100) {
                return value;
            }

            return 10;
        },

        applyInitialOrder: function (state) {
            var latestFirstAttr = state.table.attr('data-latest-first');

            if (latestFirstAttr === 'false' || latestFirstAttr === '0') {
                return;
            }

            var withSortableValue = false;

            $.each(state.rows, function (index, row) {
                var value = AppTablas.getRowSortValue($(row));

                if (value !== null) {
                    withSortableValue = true;
                }

                $(row).attr('data-original-index', index);
            });

            if (!withSortableValue) {
                return;
            }

            state.rows.sort(function (a, b) {
                var rowA = $(a);
                var rowB = $(b);
                var valueA = AppTablas.getRowSortValue(rowA);
                var valueB = AppTablas.getRowSortValue(rowB);

                if (valueA === valueB) {
                    return parseInt(rowB.attr('data-original-index'), 10) - parseInt(rowA.attr('data-original-index'), 10);
                }

                return valueB > valueA ? 1 : -1;
            });

            state.filteredRows = state.rows.slice(0);

            $.each(state.rows, function (index, row) {
                state.tbody.append(row);
            });
        },

        getRowSortValue: function (row) {
            var raw = row.attr('data-order');

            if (raw === undefined) {
                raw = row.attr('data-id');
            }

            if (raw === undefined) {
                raw = row.attr('data-created-at');
            }

            if (raw === undefined || raw === '') {
                return null;
            }

            if ($.isNumeric(raw)) {
                return parseFloat(raw);
            }

            var timestamp = Date.parse(raw);

            if (!isNaN(timestamp)) {
                return timestamp;
            }

            return String(raw).toLowerCase();
        },

        wrapTable: function (state) {
            var table = state.table;
            var wrapper = $('<div class="app-table-wrapper"></div>');
            var top = $('<div class="app-table-top"></div>');
            var bottom = $('<div class="app-table-bottom"></div>');
            var lengthBox = $('<div class="app-table-length"></div>');
            var searchBox = $('<div class="app-table-search"></div>');
            var infoBox = $('<div class="app-table-info"></div>');
            var paginationBox = $('<div class="app-table-pagination"></div>');
            var select = $('<select class="custom-select custom-select-sm app-table-page-length"></select>');
            var input = $('<input type="search" class="form-control form-control-sm app-table-search-input" placeholder="Buscar">');

            $.each(this.defaults.lengthMenu, function (index, value) {
                select.append('<option value="' + value + '">' + value + '</option>');
            });

            select.val(String(state.pageLength));

            lengthBox.append('<label class="mb-0 mr-2">Mostrar</label>');
            lengthBox.append(select);
            lengthBox.append('<span class="ml-2">registros</span>');

            searchBox.append('<label class="mb-0 mr-2">Buscar</label>');
            searchBox.append(input);

            top.append(lengthBox);
            top.append(searchBox);
            bottom.append(infoBox);
            bottom.append(paginationBox);

            table.before(wrapper);
            wrapper.append(top);
            wrapper.append('<div class="table-responsive"></div>');
            wrapper.find('.table-responsive').append(table);
            wrapper.append(bottom);

            state.wrapper = wrapper;
            state.infoBox = infoBox;
            state.paginationBox = paginationBox;
            state.searchInput = input;
            state.lengthSelect = select;

            select.on('change', function () {
                state.pageLength = parseInt($(this).val(), 10);
                state.page = 1;
                AppTablas.render(state);
            });

            input.on('input keyup change', function () {
                state.search = $(this).val();
                state.page = 1;
                AppTablas.render(state);
            });

            table.data('app-table-state', state);
        },

        normalizeText: function (value) {
            return String(value || '')
                .toLowerCase()
                .replace(/[áàäâ]/g, 'a')
                .replace(/[éèëê]/g, 'e')
                .replace(/[íìïî]/g, 'i')
                .replace(/[óòöô]/g, 'o')
                .replace(/[úùüû]/g, 'u')
                .replace(/ñ/g, 'n');
        },

        filterRows: function (state) {
            var search = this.normalizeText(state.search);

            if (search === '') {
                state.filteredRows = state.rows.slice(0);
                return;
            }

            state.filteredRows = $.grep(state.rows, function (row) {
                var text = AppTablas.normalizeText($(row).text());
                return text.indexOf(search) !== -1;
            });
        },

        render: function (state) {
            this.filterRows(state);

            var total = state.filteredRows.length;
            var pageLength = state.pageLength;
            var totalPages = Math.max(1, Math.ceil(total / pageLength));

            if (state.page > totalPages) {
                state.page = totalPages;
            }

            var start = (state.page - 1) * pageLength;
            var end = Math.min(start + pageLength, total);

            $(state.rows).hide();

            if (total > 0) {
                for (var i = start; i < end; i++) {
                    $(state.filteredRows[i]).show();
                }
            }

            this.renderInfo(state, total, start, end);
            this.renderPagination(state, totalPages);
            this.renderEmptyState(state, total);
        },

        renderInfo: function (state, total, start, end) {
            if (total === 0) {
                state.infoBox.text('Mostrando 0 registros');
                return;
            }

            state.infoBox.text('Mostrando ' + (start + 1) + ' a ' + end + ' de ' + total + ' registros');
        },

        renderPagination: function (state, totalPages) {
            var pagination = $('<ul class="pagination pagination-sm mb-0"></ul>');
            var pages = this.getPaginationPages(state.page, totalPages);

            pagination.append(this.pageItem('Anterior', state.page - 1, state.page === 1));

            $.each(pages, function (index, page) {
                if (page === 'gap') {
                    pagination.append('<li class="page-item disabled"><span class="page-link">&hellip;</span></li>');
                    return;
                }

                pagination.append(AppTablas.pageItem(page, page, false, page === state.page));
            });

            pagination.append(this.pageItem('Siguiente', state.page + 1, state.page === totalPages));

            state.paginationBox.empty().append(pagination);

            state.paginationBox.find('[data-page]').on('click', function (event) {
                event.preventDefault();

                var page = parseInt($(this).attr('data-page'), 10);

                if (!page || page < 1 || page > totalPages || page === state.page) {
                    return;
                }

                state.page = page;
                AppTablas.render(state);
            });
        },

        getPaginationPages: function (current, total) {
            var pages = [];
            var candidates = [1, current - 1, current, current + 1, total];

            $.each(candidates, function (index, value) {
                if (value >= 1 && value <= total && $.inArray(value, pages) === -1) {
                    pages.push(value);
                }
            });

            pages.sort(function (a, b) {
                return a - b;
            });

            var result = [];

            $.each(pages, function (index, value) {
                if (index > 0 && value - pages[index - 1] > 1) {
                    result.push('gap');
                }

                result.push(value);
            });

            return result;
        },

        pageItem: function (label, page, disabled, active) {
            var disabledClass = disabled ? ' disabled' : '';
            var activeClass = active ? ' active' : '';
            var dataPage = disabled ? '' : ' data-page="' + page + '"';

            return '' +
                '<li class="page-item' + disabledClass + activeClass + '">' +
                '    <a class="page-link" href="#"' + dataPage + '>' + label + '</a>' +
                '</li>';
        },

        renderEmptyState: function (state, total) {
            var colCount = state.table.find('thead th').length;

            if (colCount <= 0) {
                colCount = 1;
            }

            state.tbody.find('tr.app-table-empty-row').remove();

            if (total > 0) {
                return;
            }

            var emptyHtml;

            if (window.AppUI && typeof window.AppUI.emptyState === 'function') {
                emptyHtml = window.AppUI.emptyState('Sin registros', state.emptyText, state.emptyIcon);
            } else {
                emptyHtml = '<div class="app-empty-state"><h5>Sin registros</h5><p>' + state.emptyText + '</p></div>';
            }

            state.tbody.append(
                '<tr class="app-table-empty-row">' +
                '    <td colspan="' + colCount + '">' + emptyHtml + '</td>' +
                '</tr>'
            );
        }
    };

    window.AppTablas = AppTablas;

    $(function () {
        AppTablas.init();
    });
})(window, window.jQuery);