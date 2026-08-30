<?php
  $__topbarName = $name ?? ($_SESSION['user_name'] ?? 'User');
  $__topbarInitials = '';
  foreach (explode(' ', trim($__topbarName)) as $__topbarWord) { if ($__topbarWord !== '') $__topbarInitials .= strtoupper($__topbarWord[0]); if (strlen($__topbarInitials) >= 2) break; }
?>
<div class="dashboard-topbar">
  <div class="hidden items-center gap-3 sm:flex">
    <span class="inline-flex h-2 w-2 rounded-full bg-brand"></span>
    <span class="text-xs font-semibold text-gray-500 dark:text-white/50"><?= date('D, M j, Y') ?></span>
    <span class="rounded-full bg-brand-light px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-brand">Workspace</span>
  </div>
  <div class="dashboard-search hidden md:flex">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
      <circle cx="11" cy="11" r="7"/>
      <path d="m20 20-4-4"/>
    </svg>
    <span>Search workspace</span>
    <kbd>⌘ K
    </kbd>
  </div>
  <div class="ml-auto flex items-center gap-3">
    <button type="button" aria-label="Notifications" class="relative rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm dark:border-white/10 dark:bg-ink-2 dark:text-white/60">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
        <path d="M10 21h4"/>
      </svg>
      <span class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full bg-brand">
      </span>
    </button>
    <div class="hidden text-right sm:block">
      <p class="text-xs font-bold text-ink dark:text-white"><?= htmlspecialchars($__topbarName) ?>
    </p><p class="text-[10px] text-gray-400">Signed in</p>
  </div>
  <div class="topbar-avatar"><?= htmlspecialchars($__topbarInitials ?: '?') ?></div>
</div>
</div>
