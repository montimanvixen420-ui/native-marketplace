<!-- Dark mode toggle — floating, appears on every page -->
    <button
      id="themeToggle"
      type="button"
      aria-label="Toggle dark mode"
      class="fixed bottom-5 right-5 z-50 w-11 h-11 rounded-full bg-white dark:bg-ink-2 border border-gray-200 dark:border-white/10 shadow-lg flex items-center justify-center text-ink dark:text-white hover:scale-105 active:scale-95 transition-all"
    >
      <!-- Moon icon: shown in light mode (click to go dark) -->
      <svg id="themeIconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 block dark:hidden">
        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
      </svg>
      <!-- Sun icon: shown in dark mode (click to go light) -->
      <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden dark:block">
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
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table[data-datatable]').forEach(function (table) {
      if (DataTable.isDataTable(table)) return;
      new DataTable(table, {
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        order: [],
        columnDefs: [{ orderable: false, targets: -1 }],
        // Keep the table controls minimal and consistent across every role.
        // Search is deliberately removed; page-size control stays on the right.
        layout: { topStart: null, topEnd: 'pageLength', bottomStart: 'info', bottomEnd: 'paging' },
        drawCallback: function () { addButtonIcons(); lucide.createIcons(); }
      });
    });

    document.addEventListener('submit', function (event) {
      var form = event.target;
      var action = form.getAttribute('action') || '';
      var needsConfirmation = form.classList.contains('js-confirm-form') || /\/(delete|reject|cancel|suspend|ban|remove)(?:\/|$)/i.test(action);
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
      Swal.fire({ toast: true, position: 'top-end', icon: params.has('success') ? 'success' : 'error', title: message, showConfirmButton: false, timer: 3500, timerProgressBar: true });
    }

    addButtonIcons();
    addExportMenus();
    lucide.createIcons();
  });

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
      var match = icons.find(function (entry) { return entry[0].test(label); });
      if (!match) return;
      element.insertAdjacentHTML('afterbegin', '<i data-lucide="' + match[1] + '" class="mr-1.5 inline-block h-4 w-4 align-[-2px]"></i>');
    });
  }

  function addExportMenus() {
    document.querySelectorAll('table[id$="Table"]').forEach(function (table) {
      var container = table.closest('.dt-container');
      if (!container || container.querySelector('.js-export-menu')) return;

      var topRow = container.querySelector('.dt-layout-row');
      if (!topRow) return;

      var menu = document.createElement('details');
      menu.className = 'js-export-menu relative mr-3';
      menu.innerHTML = '<summary class="cursor-pointer list-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-ink-2 dark:text-gray-200 dark:hover:bg-white/5"><i data-lucide="download" class="mr-1 inline-block h-4 w-4 align-[-2px]"></i>Export</summary><div class="absolute left-0 z-30 mt-2 w-36 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-white/10 dark:bg-ink-2"><button type="button" data-export="copy" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Copy</button><button type="button" data-export="csv" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">CSV</button><button type="button" data-export="print" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Print</button></div>';
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

  function tableToCsv(table) {
    return Array.from(table.querySelectorAll('tr')).map(function (row) {
      return Array.from(row.querySelectorAll('th, td')).map(function (cell) {
        return '"' + cell.innerText.trim().replaceAll('"', '""').replaceAll('\n', ' ') + '"';
      }).join(',');
    }).join('\n');
  }
</script>
<style>
  /* DataTables 2 (new pages) */
  .dt-search { display: none !important; }
  .dt-layout-row:first-child { justify-content: flex-end !important; }
  .dt-layout-row:first-child .dt-layout-end { margin-left: auto !important; }

  /* Light mode: keep length + paging on the white table card. */
  html:not(.dark) .dt-container .dt-length select,
  html:not(.dark) .dt-paging a {
    background-color: #fff !important;
    color: #1f2937 !important;
    border-color: #e5e7eb !important;
  }
  html:not(.dark) .dt-info { color: #6b7280; }

  /* Dark mode: table chrome matches the dark card (not gray-800 chips on white). */
  html.dark .dt-container { color: #e5e7eb; }
  html.dark .dt-info,
  html.dark .dataTables_info { color: #9ca3af !important; }
  html.dark .dt-length,
  html.dark .dataTables_length { color: #9ca3af; }
  html.dark .dt-container .dt-length select,
  html.dark .dataTables_wrapper .dataTables_length select {
    background-color: #111827 !important;
    color: #f9fafb !important;
    border-color: rgba(255,255,255,.15) !important;
  }
  html.dark .dt-paging a,
  html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
    background-color: #111827 !important;
    color: #e5e7eb !important;
    border-color: rgba(255,255,255,.12) !important;
  }
  html.dark .dt-paging a.font-semibold,
  html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #2563eb !important;
    color: #fff !important;
    border-color: #2563eb !important;
  }
  html.dark table.dataTable,
  html.dark table.dataTable tbody td,
  html.dark table.dataTable tbody tr { background-color: transparent !important; color: #e5e7eb !important; }
  html.dark table.dataTable tbody tr.even,
  html.dark table.dataTable.stripe tbody tr.even,
  html.dark table.dataTable.display tbody tr.even { background-color: rgba(255,255,255,.04) !important; }
  html.dark .dataTables_wrapper { color: #e5e7eb; }
  html.dark .dataTables_wrapper table.dataTable { background: transparent !important; }

  /* Legacy jQuery DataTables pages: remove Search and align page-size right. */
  .dataTables_wrapper .dataTables_filter { display: none !important; }
  .dataTables_wrapper .dataTables_length { float: right !important; text-align: right !important; }
  .dataTables_wrapper .dataTables_length label { float: none !important; }
  .dataTables_wrapper::after { content: ''; display: block; clear: both; }
</style>
</body>
</html>
