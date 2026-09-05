<!-- Dark mode toggle -->
<button id="themeToggle" type="button" aria-label="Toggle dark mode"
  class="fixed bottom-5 right-5 z-50 flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-ink shadow-lg transition-all hover:scale-105 active:scale-95 dark:border-white/10 dark:bg-ink-2 dark:text-white">
  <svg id="themeIconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="block h-5 w-5 dark:hidden">
    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
  </svg>
  <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5 dark:block">
    <circle cx="12" cy="12" r="4"/>
    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
  </svg>
</button>

<script>
document.getElementById('themeToggle').addEventListener('click', function () {
  var isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
function addButtonIcons() {
  var icons = [
    [/delete|remove|discard/i, 'trash-2'],
    [/edit|update|modify/i, 'pencil'],
    [/approve|accept|confirm/i, 'check'],
    [/reject|decline|cancel|close/i, 'x'],
    [/suspend|ban|block/i, 'ban'],
    [/reactivate|restore/i, 'rotate-ccw'],
    [/filter|search/i, 'search'],
    [/save/i, 'save'],
    [/add|new|create/i, 'plus'],
    [/submit|send/i, 'send'],
    [/view|details/i, 'eye'],
    [/download|export/i, 'download']
  ];

  document.querySelectorAll('button, a.inline-flex').forEach(function (element) {
    if (element.querySelector('svg, i[data-lucide]')) return;

    var label = element.textContent.trim();
    var match = icons.find(function (entry) {
      return entry[0].test(label);
    });

    if (!match) return;

    element.insertAdjacentHTML(
      'afterbegin',
      '<i data-lucide="' + match[1] + '" class="mr-1.5 inline-block h-4 w-4 align-[-2px]"></i>'
    );
  });
}

function standardizePagination() {
  document.querySelectorAll('.dataTables_wrapper .paginate_button').forEach(function (button) {
    var label = button.textContent.trim().toLowerCase();

    if (label === 'first') button.textContent = '«';
    if (label === 'previous') button.textContent = '‹';
    if (label === 'next') button.textContent = '›';
    if (label === 'last') button.textContent = '»';
  });
}
// Renders the same pill-style pagination used on the Orders page,
// but driven by a DataTables instance's API instead of manual arrays.
function renderDtPillPagination(dtApi, paginationId, infoId) {
  var pageInfo = dtApi.page.info();
  var currentPage = pageInfo.page + 1;
  var totalPages = pageInfo.pages || 1;

  var infoEl = document.getElementById(infoId);
  if (infoEl) {
    infoEl.textContent = pageInfo.recordsDisplay === 0
      ? 'Showing 0 to 0 of 0 entries'
      : 'Showing ' + (pageInfo.start + 1) + ' to ' + pageInfo.end + ' of ' + pageInfo.recordsDisplay + ' entries';
  }

     var pagEl = document.getElementById(paginationId);
  if (!pagEl) return;
  pagEl.innerHTML = '';
  if (totalPages < 1) return; // walang records talaga (0 entries) — wala munang ipapakita

  function makeBtn(label, targetPage, disabled, active) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = label;
    btn.disabled = disabled;
    btn.className = active
      ? 'px-3 py-1 text-xs font-bold rounded-lg transition-colors bg-indigo-600 text-white dark:bg-indigo-500'
      : 'px-3 py-1 text-xs font-bold rounded-lg transition-colors border border-slate-200 dark:border-slate-700 ' +
        (disabled ? 'opacity-40 cursor-not-allowed text-slate-400' : 'hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200');
    if (!disabled && !active) btn.onclick = function () { dtApi.page(targetPage).draw('page'); };
    return btn;
  }

  pagEl.appendChild(makeBtn('«', 0, currentPage === 1));
  pagEl.appendChild(makeBtn('‹', currentPage - 2, currentPage === 1));

  var start = Math.max(1, currentPage - 2);
  var end = Math.min(totalPages, start + 4);
  start = Math.max(1, end - 4);

  for (var p = start; p <= end; p++) {
    pagEl.appendChild(makeBtn(String(p), p - 1, false, p === currentPage));
  }

  pagEl.appendChild(makeBtn('›', currentPage, currentPage === totalPages));
  pagEl.appendChild(makeBtn('»', totalPages - 1, currentPage === totalPages));
}

function tableToCsv(table) {
  return Array.from(table.querySelectorAll('tr')).map(function (row) {
    return Array.from(row.querySelectorAll('th, td')).map(function (cell) {
      return '"' + cell.innerText.trim().replaceAll('"', '""').replaceAll('\n', ' ') + '"';
    }).join(',');
  }).join('\n');
}

function addExportMenus() {
  document.querySelectorAll('table[id$="Table"]').forEach(function (table) {
    var container = table.closest('.dt-container');

    if (!container || container.querySelector('.js-export-menu')) return;

    var topRow = container.querySelector('.dt-layout-row');
    if (!topRow) return;

    var menu = document.createElement('details');
    menu.className = 'js-export-menu relative mr-3';

    menu.innerHTML = `
      <summary class="cursor-pointer list-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-ink-2 dark:text-gray-200 dark:hover:bg-white/5">
        <i data-lucide="download" class="mr-1 inline-block h-4 w-4 align-[-2px]"></i>Export
      </summary>
      <div class="absolute left-0 z-30 mt-2 w-36 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-white/10 dark:bg-ink-2">
        <button type="button" data-export="copy" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Copy</button>
        <button type="button" data-export="csv" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">CSV</button>
        <button type="button" data-export="print" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Print</button>
      </div>
    `;

    topRow.prepend(menu);

    menu.addEventListener('click', function (event) {
      var action = event.target.dataset.export;
      if (!action) return;

      event.preventDefault();
      menu.open = false;

      var csv = tableToCsv(table);

      if (action === 'copy') {
        navigator.clipboard.writeText(csv);
      } else if (action === 'csv') {
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');

        link.href = URL.createObjectURL(blob);
        link.download = (table.id || 'table') + '.csv';
        link.click();

        URL.revokeObjectURL(link.href);
      } else {
        window.print();
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('table[data-datatable]').forEach(function (table) {
    if (DataTable.isDataTable(table)) return;

    var uid = table.id || 'dt-' + Math.random().toString(36).slice(2);

    // Custom toolbar (search + entries) — kaparehong design ng ibang pages,
    // dinisenyo dito sa JS dahil generic/reusable ito para sa lahat ng
    // table[data-datatable] kahit anong page pa ito.
    var toolbar = document.createElement('div');
    toolbar.className = 'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4';
    toolbar.innerHTML =
      '<div class="relative w-full sm:w-72">' +
        '<i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>' +
        '<input type="text" id="' + uid + '-search" placeholder="Search product or variant..." class="w-full pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-900 dark:text-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">' +
      '</div>' +
      '<div class="flex items-center gap-2 shrink-0">' +
        '<select id="' + uid + '-length" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-sm rounded-lg px-2 py-1.5 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">' +
          '<option value="5">5</option>' +
          '<option value="10">10</option>' +
          '<option value="15">15</option>' +
          '<option value="20">20</option>' +
        '</select>' +
        '<span class="text-sm text-gray-500 dark:text-gray-400">entries per page</span>' +
      '</div>';
    table.insertAdjacentElement('beforebegin', toolbar);

    // Footer row (info text + pill pagination) DIRETSO pagkatapos ng table.
    var footer = document.createElement('div');
    footer.className = 'flex flex-col sm:flex-row items-center justify-between gap-4 pt-5 mt-2 border-t border-gray-100 dark:border-slate-700/60';
    footer.innerHTML =
      '<div id="' + uid + '-info" class="text-xs font-medium text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</div>' +
      '<div id="' + uid + '-pagination" class="flex items-center gap-1"></div>';
    table.insertAdjacentElement('afterend', footer);

    var dt = new DataTable(table, {
      pageLength: 5,
      lengthMenu: [5, 10, 15, 20],
      order: [],
      columnDefs: [{ orderable: false, targets: -1 }],
      searching: true,
      layout: {
        topStart: null,
        topEnd: null,
        bottomStart: null,
        bottomEnd: null
      },
      drawCallback: function () {
        renderDtPillPagination(this.api(), uid + '-pagination', uid + '-info');
        addButtonIcons();
        lucide.createIcons();
      }
    });

    document.getElementById(uid + '-search').addEventListener('keyup', function () {
      dt.search(this.value).draw();
    });
    document.getElementById(uid + '-length').addEventListener('change', function () {
      dt.page.len(this.value).draw();
    });
  });

  document.addEventListener('submit', function (event) {
    var form = event.target;
    var action = form.getAttribute('action') || '';
    var needsConfirmation = form.classList.contains('js-confirm-form') ||
      /\/(delete|reject|cancel|suspend|ban|remove)(?:\/|$)/i.test(action);

    if (!needsConfirmation || form.dataset.swalConfirmed === 'true') return;

    event.preventDefault();

    Swal.fire({
      title: form.dataset.title || 'Are you sure?',
      text: form.dataset.text || 'This action may not be reversible.',
      icon: form.dataset.icon || 'warning',
      showCancelButton: true,
      confirmButtonText: form.dataset.confirmText || 'Yes, continue',
      cancelButtonText: 'Cancel',
      confirmButtonColor: form.dataset.confirmColor || '#dc2626',
      cancelButtonColor: '#6b7280',
      reverseButtons: true
    }).then(function (result) {
      if (result.isConfirmed) {
        form.dataset.swalConfirmed = 'true';
        form.requestSubmit();
      }
    });
  });

  var params = new URLSearchParams(window.location.search);
  var message = params.get('success') || params.get('error');

  if (message) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: params.has('success') ? 'success' : 'error',
      title: message,
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true
    });
  }

  standardizePagination();
  addButtonIcons();
  addExportMenus();
  lucide.createIcons();

  var observer = new MutationObserver(function () {
    standardizePagination();
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
});
</script>

<style>
/* ── TINDA standard DataTable workspace ── */
.dt-container {
  color: #334155;
  font-size: .8125rem;
}

.dt-container .dt-layout-row:first-child {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  margin: 0 0 1rem;
}

.dt-container .dt-layout-row:first-child .dt-layout-start {
  flex: 1;
}

.dt-container .dt-layout-row:first-child .dt-layout-end {
  margin-left: auto;
}

.dt-container .dt-search label {
  display: block;
  position: relative;
  max-width: 14rem;
}

.dt-container .dt-search label::before {
  content: '⌕';
  position: absolute;
  z-index: 1;
  left: .72rem;
  top: 50%;
  transform: translateY(-52%);
  color: #94a3b8;
  font-size: 1rem;
  line-height: 1;
}

.dt-container .dt-search input {
  width: 100%;
  border: 1px solid #dbe3ef;
  border-radius: .8rem;
  background: #fff;
  padding: .5rem .75rem .5rem 2rem;
  color: #334155;
  font-size: .75rem;
  outline: 0;
}

.dt-container .dt-search input:focus {
  border-color: #818cf8;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}

.dt-container .dt-length label {
  display: flex;
  align-items: center;
  gap: .45rem;
  color: #64748b;
  font-size: .75rem;
  white-space: nowrap;
}

.dt-container .dt-length select {
  min-width: 3.6rem;
  border: 1px solid #dbe3ef;
  border-radius: .75rem;
  background: #fff;
  padding: .48rem 1.7rem .48rem .65rem;
  color: #334155;
  font-size: .75rem;
  font-weight: 600;
}

.dt-container table.dataTable {
  margin: 0 !important;
  border-collapse: separate;
  border-spacing: 0;
}

.dt-container table.dataTable thead th {
  border-top: 1px solid #e6edf5 !important;
  border-bottom: 1px solid #dfe7f0 !important;
  background: #fff !important;
  padding: .9rem 1rem !important;
  color: #94a3b8 !important;
  font-size: .65rem !important;
  font-weight: 800 !important;
  letter-spacing: .06em;
  text-transform: uppercase;
}

.dt-container table.dataTable tbody td {
  border-bottom: 1px solid #edf2f7 !important;
  padding: .95rem 1rem !important;
  vertical-align: middle;
}

.dt-container table.dataTable tbody tr:hover {
  background: #f8fafc !important;
}

.dt-container .dt-layout-row:last-child {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .75rem;
  margin-top: 1rem;
}

.dt-container .dt-info {
  color: #64748b;
  font-size: .72rem;
  font-weight: 600;
}

/* Global + legacy pagination style */
.dt-container .dt-paging,
.dataTables_wrapper .dataTables_paginate {
  display: flex !important;
  align-items: center;
  justify-content: flex-end;
  gap: .28rem;
}

/* Exact pagination style: « ‹ 1 2 3 4 5 › » */
.dt-container .dt-paging,
.dataTables_wrapper .dataTables_paginate {
  display: flex !important;
  align-items: center !important;
  justify-content: flex-end !important;
  gap: .35rem !important;
}

.dt-container .dt-paging .dt-paging-button,
.dt-container .dt-paging button,
.dataTables_wrapper .dataTables_paginate .paginate_button {
  display: inline-flex !important;
  min-width: 1.9rem !important;
  height: 1.9rem !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0 .45rem !important;
  border: 1px solid #dbe3ef !important;
  border-radius: .55rem !important;
  background: #ffffff !important;
  background-image: none !important;
  box-shadow: none !important;
  color: #475569 !important;
  font-size: .72rem !important;
  font-weight: 700 !important;
  line-height: 1 !important;
  text-decoration: none !important;
}

.dt-container .dt-paging .dt-paging-button.current,
.dt-container .dt-paging button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  border-color: #4f46e5 !important;
  background: #4f46e5 !important;
  background-image: none !important;
  color: #ffffff !important;
}

.dt-container .dt-paging .dt-paging-button:hover:not(.disabled):not(.current),
.dt-container .dt-paging button:hover:not(.disabled):not(.current),
.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
  border-color: #a5b4fc !important;
  background: #eef2ff !important;
  background-image: none !important;
  color: #4338ca !important;
}

.dt-container .dt-paging .dt-paging-button.disabled,
.dt-container .dt-paging button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  opacity: .45 !important;
  cursor: not-allowed !important;
}

.dt-container .dt-paging button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  border-color: #4f46e5 !important;
  background: #4f46e5 !important;
  color: #fff !important;
}

.dt-container .dt-paging button:hover:not(.disabled):not(.current),
.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
  border-color: #a5b4fc !important;
  background: #eef2ff !important;
  color: #4338ca !important;
}

.dt-container .dt-paging button.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
  opacity: .45 !important;
  cursor: not-allowed !important;
}

/* Legacy jQuery DataTables search and entries */
.dataTables_wrapper .dataTables_filter {
  float: left !important;
  text-align: left !important;
}

.dataTables_wrapper .dataTables_filter label {
  display: flex;
  align-items: center;
  gap: .45rem;
  font-size: 0;
}

.dataTables_wrapper .dataTables_filter input {
  width: 14rem;
  margin-left: 0 !important;
  border: 1px solid #dbe3ef;
  border-radius: .8rem;
  padding: .5rem .75rem;
  color: #334155;
  font-size: .75rem;
}

.dataTables_wrapper .dataTables_length {
  float: right !important;
  text-align: right !important;
}

.dataTables_wrapper .dataTables_length label {
  float: none !important;
  color: #64748b;
  font-size: .75rem;
}

.dataTables_wrapper::after {
  content: '';
  display: block;
  clear: both;
}

/* Dark mode */
html.dark .dt-container {
  color: #e5e7eb;
}

html.dark .dt-container .dt-search input,
html.dark .dt-container .dt-length select,
html.dark .dt-container .dt-paging button,
html.dark .dataTables_wrapper .dataTables_filter input,
html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
  border-color: rgba(255,255,255,.15) !important;
  background: #111827 !important;
  color: #e5e7eb !important;
}

html.dark .dt-container table.dataTable thead th {
  border-color: rgba(255,255,255,.08) !important;
  background: #111827 !important;
  color: #9ca3af !important;
}

html.dark .dt-container table.dataTable tbody td {
  border-color: rgba(255,255,255,.08) !important;
}

html.dark .dt-container table.dataTable tbody tr:hover {
  background: rgba(255,255,255,.05) !important;
}

html.dark .dt-container .dt-paging button.current,
html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
  border-color: #4f46e5 !important;
  background: #4f46e5 !important;
  color: #fff !important;
}

@media (max-width: 640px) {
  .dt-container .dt-layout-row:first-child,
  .dt-container .dt-layout-row:last-child {
    align-items: flex-start;
    flex-direction: column;
  }

  .dt-container .dt-layout-row:first-child .dt-layout-end {
    margin-left: 0;
  }

  .dt-container .dt-search label {
    max-width: none;
  }
}
/* Force separate pagination buttons — removes DataTables joined-box style */
.dt-container .dt-paging nav {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.dt-container .dt-paging nav .dt-paging-button,
.dt-container .dt-paging nav button {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-width: 30px !important;
  width: 30px !important;
  height: 30px !important;
  margin: 0 !important;
  padding: 0 !important;
  border: 1px solid #dbe3ef !important;
  border-radius: 8px !important;
  background: #ffffff !important;
  background-image: none !important;
  box-shadow: none !important;
  color: #475569 !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  line-height: 1 !important;
}

.dt-container .dt-paging nav .dt-paging-button:first-child,
.dt-container .dt-paging nav .dt-paging-button:last-child {
  border-radius: 8px !important;
}

.dt-container .dt-paging nav .dt-paging-button.current,
.dt-container .dt-paging nav button.current {
  border-color: #4f46e5 !important;
  background: #4f46e5 !important;
  color: #ffffff !important;
}

.dt-container .dt-paging nav .dt-paging-button:hover:not(.disabled):not(.current),
.dt-container .dt-paging nav button:hover:not(.disabled):not(.current) {
  border-color: #a5b4fc !important;
  background: #eef2ff !important;
  color: #4338ca !important;
}

.dt-container .dt-paging nav .dt-paging-button.disabled,
.dt-container .dt-paging nav button.disabled {
  opacity: .45 !important;
}

html.dark .dt-container .dt-paging nav .dt-paging-button,
html.dark .dt-container .dt-paging nav button {
  border-color: rgba(255,255,255,.14) !important;
  background: #111827 !important;
  color: #e5e7eb !important;
}

html.dark .dt-container .dt-paging nav .dt-paging-button.current,
html.dark .dt-container .dt-paging nav button.current {
  border-color: #4f46e5 !important;
  background: #4f46e5 !important;
  color: #ffffff !important;
}
</style>

</body>
</html>