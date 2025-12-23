<?php
// projects/index.php  (served from /var/www/html)
//
// Local-dev dashboard. Do not deploy to production as-is.

declare(strict_types=1);

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

function detectProject(array $p): array {
    $path = $p['path'];

    $hasArtisan      = is_file($path . '/artisan');
    $hasComposerJson = is_file($path . '/composer.json');
    $hasPackageJson  = is_file($path . '/package.json');
    $hasPublic       = is_dir($path . '/public');
    $hasReadme       = is_file($path . '/README.md') || is_file($path . '/readme.md');
    $hasEnv          = is_file($path . '/.env');

    $type = 'Static';
    if ($hasArtisan) $type = 'Laravel';
    elseif ($hasComposerJson && $hasPackageJson) $type = 'PHP + Node';
    elseif ($hasComposerJson) $type = 'PHP';
    elseif ($hasPackageJson) $type = 'Node';

    // "Modified" is best-effort: folder mtime (fast) rather than deep scan (expensive).
    $modifiedTs = @filemtime($path) ?: time();

    // Quick “contents count” (top-level only, fast)
    $count = 0;
    $list = @scandir($path);
    if (is_array($list)) {
        foreach ($list as $x) {
            if ($x === '.' || $x === '..') continue;
            $count++;
        }
    }

    return [
        'type' => $type,
        'hasPublic' => $hasPublic,
        'hasReadme' => $hasReadme,
        'hasEnv' => $hasEnv,
        'modifiedTs' => $modifiedTs,
        'itemsCount' => $count,
    ];
}

$root = __DIR__;
$entries = array_filter(glob($root . '/*', GLOB_ONLYDIR) ?: [], 'is_dir');

$projects = [];
foreach ($entries as $dirPath) {
    $name = basename($dirPath);

    // Skip common noise folders if you want (optional)
    if (in_array($name, ['.git', 'node_modules', 'vendor'], true)) continue;

    $meta = detectProject(['path' => $dirPath]);

    $projects[] = [
        'name' => $name,
        'path' => $dirPath,
        'meta' => $meta,
    ];
}

// Optional server info for header
$phpVersion = PHP_VERSION;
$server = $_SERVER['SERVER_SOFTWARE'] ?? 'Web Server';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Docker Projects Dashboard</title>
  <style>
    :root{
      --bg: #0b1220;
      --card:#111b2e;
      --muted:#9fb3c8;
      --text:#e8f0fb;
      --border: rgba(255,255,255,.08);
      --accent:#5aa7ff;
      --good:#34d399;
      --warn:#fbbf24;
      --bad:#fb7185;
      --chip:#0d2a4f;
      --shadow: 0 10px 25px rgba(0,0,0,.25);
      --radius: 16px;
    }
    @media (prefers-color-scheme: light){
      :root{
        --bg:#f6f8fb; --card:#ffffff; --text:#0f172a; --muted:#475569;
        --border: rgba(2,6,23,.08); --chip:#eef2ff; --shadow: 0 10px 25px rgba(2,6,23,.08);
      }
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background: var(--bg); color: var(--text);
    }
    .wrap{max-width:1200px; margin:0 auto; padding:28px 18px 50px}
    .top{
      display:flex; gap:14px; align-items:flex-start; justify-content:space-between;
      flex-wrap:wrap; margin-bottom:18px;
    }
    .title h1{margin:0 0 6px; font-size:22px; letter-spacing:.2px}
    .title p{margin:0; color:var(--muted); font-size:13px}
    .toolbar{
      display:flex; gap:10px; flex-wrap:wrap; align-items:center;
      background: rgba(255,255,255,.02); border:1px solid var(--border);
      padding:10px; border-radius: var(--radius);
    }
    input, select, button{
      font: inherit; color: inherit;
      background: rgba(255,255,255,.03);
      border:1px solid var(--border);
      border-radius: 12px;
      padding:10px 12px;
      outline:none;
    }
    input{min-width: 260px}
    select{min-width: 170px}
    button{cursor:pointer}
    button:hover{border-color: rgba(90,167,255,.6)}
    .grid{
      display:grid;
      grid-template-columns: repeat(12, 1fr);
      gap:14px;
      margin-top:14px;
    }
    .card{
      grid-column: span 6;
      background: var(--card);
      border:1px solid var(--border);
      border-radius: var(--radius);
      padding:16px;
      box-shadow: var(--shadow);
      display:flex; flex-direction:column; gap:12px;
      position:relative;
    }
    @media (max-width: 900px){ .card{grid-column: span 12;} input{min-width: 220px} }
    .card-head{
      display:flex; justify-content:space-between; align-items:flex-start; gap:10px;
    }
    .name{font-size:16px; font-weight:700; margin:0}
    .meta{color:var(--muted); font-size:12px; margin-top:4px}
    .chips{display:flex; gap:6px; flex-wrap:wrap; align-items:center}
    .chip{
      font-size:11px; padding:6px 8px; border-radius: 999px;
      background: var(--chip); border:1px solid var(--border);
      color: var(--muted);
    }
    .chip.type{color: var(--text); border-color: rgba(90,167,255,.35)}
    .chip.good{color: var(--good)}
    .chip.warn{color: var(--warn)}
    .actions{display:flex; gap:8px; flex-wrap:wrap}
    .link{
      text-decoration:none; display:inline-flex; align-items:center; gap:8px;
      padding:9px 11px; border-radius: 12px; border:1px solid var(--border);
      background: rgba(255,255,255,.02); color: var(--text);
    }
    .link:hover{border-color: rgba(90,167,255,.6)}
    .small{font-size:12px; color: var(--muted)}
    .cmd{
      display:flex; gap:8px; align-items:center; flex-wrap:wrap;
      padding:10px; border-radius: 14px; border:1px dashed var(--border);
      background: rgba(255,255,255,.02);
    }
    code{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
      font-size: 12px;
      color: var(--text);
      word-break: break-all;
    }
    .copy{padding:9px 11px}
    .empty{
      padding:18px; border:1px solid var(--border); border-radius: var(--radius);
      background: rgba(255,255,255,.02); color: var(--muted);
    }
    .footer{
      margin-top:22px; color:var(--muted); font-size:12px;
      display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;
    }
    .badge{
      position:absolute; right:14px; top:14px;
      font-size:11px; padding:6px 10px; border-radius:999px;
      border:1px solid var(--border);
      background: rgba(90,167,255,.08);
      color: var(--accent);
    }
  </style>
</head>
<body>
<div class="wrap">

  <div class="top">
    <div class="title">
      <h1>Docker Projects Dashboard</h1>
      <p>
        Host: <strong><?= h($host) ?></strong> · <?= h($server) ?> · PHP <?= h($phpVersion) ?>
        · Folder: <code><?= h($root) ?></code>
      </p>
    </div>

    <div class="toolbar">
      <input id="q" type="search" placeholder="Search project (e.g., saas, api, vue)..." autocomplete="off">
      <select id="type">
        <option value="">All types</option>
        <option>Laravel</option>
        <option>PHP</option>
        <option>Node</option>
        <option>PHP + Node</option>
        <option>Static</option>
      </select>
      <select id="sort">
        <option value="name-asc">Sort: Name (A→Z)</option>
        <option value="name-desc">Sort: Name (Z→A)</option>
        <option value="mod-desc">Sort: Recently modified</option>
        <option value="mod-asc">Sort: Oldest modified</option>
      </select>
      <a class="link" href="/phpmyadmin/" onclick="return false;" style="display:none"></a>
      <a class="link" href="http://localhost:8081" target="_blank" rel="noopener">Open phpMyAdmin</a>
    </div>
  </div>

  <?php if (count($projects) === 0): ?>
    <div class="empty">
      No project folders found. Create a folder inside <code>projects/</code> (host),
      then refresh this page.
      <div class="small" style="margin-top:10px">
        Example: <code>mkdir -p projects/myapp</code>
      </div>
    </div>
  <?php else: ?>
    <div class="grid" id="grid">
      <?php foreach ($projects as $p):
        $name = $p['name'];
        $m = $p['meta'];
        $type = $m['type'];

        $openRoot = "/" . rawurlencode($name) . "/";
        $openPublic = $m['hasPublic'] ? ("/" . rawurlencode($name) . "/public/") : null;

        $readmeUrl = null;
        if ($m['hasReadme']) {
          // Provide a plain download link; GitHub-style rendering is not built-in.
          $readmeUrl = $openRoot . (is_file($p['path'].'/README.md') ? 'README.md' : 'readme.md');
        }

        $modHuman = date('Y-m-d H:i', $m['modifiedTs']);
        $itemsCount = (int)$m['itemsCount'];

        // Suggested command
        $cd = "cd /var/www/html/" . $name;
        $cmd = $cd;
        if ($type === 'Laravel') $cmd .= " && composer install && php artisan -V";
        elseif ($type === 'PHP') $cmd .= " && composer install";
        elseif ($type === 'Node') $cmd .= " && npm i && npm run dev -- --host 0.0.0.0";
        elseif ($type === 'PHP + Node') $cmd .= " && composer install && npm i";
      ?>
      <div class="card"
           data-name="<?= h(strtolower($name)) ?>"
           data-type="<?= h($type) ?>"
           data-mod="<?= (int)$m['modifiedTs'] ?>">
        <div class="badge"><?= h($type) ?></div>

        <div class="card-head">
          <div>
            <p class="name"><?= h($name) ?></p>
            <div class="meta">
              Modified: <?= h($modHuman) ?> · Items: <?= h((string)$itemsCount) ?>
            </div>
          </div>
          <div class="chips">
            <span class="chip type"><?= h($type) ?></span>
            <?php if ($m['hasPublic']): ?><span class="chip good">public/</span><?php endif; ?>
            <?php if ($m['hasReadme']): ?><span class="chip good">README</span><?php endif; ?>
            <?php if ($m['hasEnv']): ?><span class="chip warn">.env</span><?php endif; ?>
          </div>
        </div>

        <div class="actions">
          <a class="link" href="<?= h($openRoot) ?>" target="_blank" rel="noopener">Open</a>

          <?php if ($openPublic): ?>
            <a class="link" href="<?= h($openPublic) ?>" target="_blank" rel="noopener">Open /public</a>
          <?php endif; ?>

          <?php if ($readmeUrl): ?>
            <a class="link" href="<?= h($readmeUrl) ?>" target="_blank" rel="noopener">README</a>
          <?php endif; ?>
        </div>

        <div class="cmd">
          <div style="flex:1; min-width:260px">
            <div class="small">Quick command (inside container):</div>
            <code><?= h($cmd) ?></code>
          </div>
          <button class="copy" type="button" data-copy="<?= h($cmd) ?>">Copy</button>
        </div>

        <div class="small">
          Tip: open a shell with <code>docker compose exec web bash</code> then run the command above.
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="footer">
    <div>
      If you don’t see your Laravel app at <code>/project/public</code>, create an Apache vhost pointing
      DocumentRoot to that <code>public/</code> folder.
    </div>
    <div>
      Local dev dashboard only. Don’t deploy to production.
    </div>
  </div>

</div>

<script>
(() => {
  const q = document.getElementById('q');
  const type = document.getElementById('type');
  const sort = document.getElementById('sort');
  const grid = document.getElementById('grid');

  function apply() {
    if (!grid) return;
    const query = (q.value || '').trim().toLowerCase();
    const t = type.value || '';

    const cards = Array.from(grid.querySelectorAll('.card'));

    // Filter
    for (const c of cards) {
      const name = c.dataset.name || '';
      const ct = c.dataset.type || '';
      const okName = !query || name.includes(query);
      const okType = !t || ct === t;
      c.style.display = (okName && okType) ? '' : 'none';
    }

    // Sort (only visible cards)
    const visible = cards.filter(c => c.style.display !== 'none');
    const mode = sort.value;

    visible.sort((a, b) => {
      const an = a.querySelector('.name').textContent.trim().toLowerCase();
      const bn = b.querySelector('.name').textContent.trim().toLowerCase();
      const am = parseInt(a.dataset.mod || '0', 10);
      const bm = parseInt(b.dataset.mod || '0', 10);

      if (mode === 'name-asc') return an.localeCompare(bn);
      if (mode === 'name-desc') return bn.localeCompare(an);
      if (mode === 'mod-asc') return am - bm;
      return bm - am; // mod-desc
    });

    // Re-append in sorted order (preserve hidden cards position doesn’t matter)
    for (const c of visible) grid.appendChild(c);
  }

  q?.addEventListener('input', apply);
  type?.addEventListener('change', apply);
  sort?.addEventListener('change', apply);

  // Copy buttons
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button.copy');
    if (!btn) return;
    const text = btn.getAttribute('data-copy') || '';
    try {
      await navigator.clipboard.writeText(text);
      const old = btn.textContent;
      btn.textContent = 'Copied';
      setTimeout(() => btn.textContent = old, 900);
    } catch {
      // Fallback
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
      const old = btn.textContent;
      btn.textContent = 'Copied';
      setTimeout(() => btn.textContent = old, 900);
    }
  });

  apply();
})();
</script>
</body>
</html>
