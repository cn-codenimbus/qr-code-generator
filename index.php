<?php
// index.php — Minimaler QR-Code-Generator (SVG oder EPS, Vektor)
// Setup: 1) composer require endroid/qr-code:^4.8  2) php -S localhost:8000

require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\EpsWriter;

function ecFromString(string $s) {
    return match (strtoupper($s)) {
        'L' => new ErrorCorrectionLevelLow(),
        'M' => new ErrorCorrectionLevelMedium(),
        'Q' => new ErrorCorrectionLevelQuartile(),
        'H' => new ErrorCorrectionLevelHigh(),
        default => new ErrorCorrectionLevelMedium(),
    };
}

function hexToRgb(string $hex): array {
    $hex = ltrim(trim($hex), '#');
    if (strlen($hex) === 3) {
        $r = hexdec(str_repeat($hex[0], 2));
        $g = hexdec(str_repeat($hex[1], 2));
        $b = hexdec(str_repeat($hex[2], 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return [$r, $g, $b];
}

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$errors = [];

if ($isPost) {
    $data          = trim($_POST['data'] ?? '');
    $format        = strtolower($_POST['format'] ?? 'svg');
    $ec            = ecFromString($_POST['ec'] ?? 'M');
    $margin        = max(0, (int)($_POST['margin'] ?? 2));
    $size          = max(128, min(4096, (int)($_POST['size'] ?? 512))); 
    $fg            = $_POST['fg'] ?? '#000000';
    $bg            = $_POST['bg'] ?? '#ffffff';
    $logoMaxWidth  = max(0, min(2048, (int)($_POST['logo_width'] ?? 0))); 

    if ($data === '') {
        $errors[] = 'Bitte Daten/Text/URL eingeben.';
    }

    if (!$errors) {
        [$fr, $fg_, $fb] = hexToRgb($fg);
        [$br, $bg_, $bb] = hexToRgb($bg);

        $qr = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel($ec)
            ->setSize($size)
            ->setMargin($margin)
            ->setForegroundColor(new Color($fr, $fg_, $fb))
            ->setBackgroundColor(new Color($br, $bg_, $bb));

        $logo = null;
        if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
            $mime = mime_content_type($_FILES['logo']['tmp_name']);
            $allowed = ['image/png','image/jpeg','image/webp','image/gif'];
            if (in_array($mime, $allowed, true)) {
                $tmpPath = sys_get_temp_dir() . '/' . uniqid('logo_', true);
                move_uploaded_file($_FILES['logo']['tmp_name'], $tmpPath);
                $logo = Logo::create($tmpPath);
                if ($logoMaxWidth > 0) {
                    $logo = $logo->setResizeToWidth($logoMaxWidth);
                }
            } else {
                $errors[] = 'Logo nur als PNG/JPG/WEBP/GIF erlaubt.';
            }
        }

        if (!$errors) {
            $filenameBase = 'qr_' . preg_replace('/[^a-z0-9_-]+/i', '-', substr($data, 0, 32)) . '_' . date('Ymd_His');
            if ($format === 'eps') {
                $writer = new EpsWriter();
                $result = $writer->write($qr, $logo, null);
                $contentType = 'application/postscript';
                $ext = 'eps';
            } else {
                $writer = new SvgWriter();
                $result = $writer->write($qr, $logo, null);
                $contentType = 'image/svg+xml';
                $ext = 'svg';
            }

            header('Content-Type: ' . $contentType);
            header('X-Content-Type-Options: nosniff');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.' . $ext . '"');
            echo $result->getString();
            if ($logo && method_exists($logo, 'getPath')) {
                @unlink($logo->getPath());
            }
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QR Vektor-Generator (PHP)</title>
  <style>
    :root { --gap: 14px; }
    body { font-family: system-ui, Roboto, Arial, sans-serif; margin: 0; padding: 2rem; background: #0b0d10; color: #eef2f8; }
    .wrap { max-width: 980px; margin: 0 auto; }
    h1 { margin: 0 0 1rem; font-size: 1.6rem; }
    p.muted { color: #9fb3c8; }
    form { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); background: #11161c; padding: 1rem; border-radius: 16px; box-shadow: 0 6px 24px rgba(0,0,0,.35); box-sizing: border-box; }
    .full { grid-column: 1 / -1; }
    label { display: grid; gap: 6px; font-size: .9rem; color: #b9c6d8; }
    input[type="text"], input[type="number"], select, input[type="url"], input[type="file"] {
      width: 100%; max-width: 100%; box-sizing: border-box; padding: .66rem .75rem; border-radius: 10px; border: 1px solid #2a394a; background: #0f141a; color: #e8f0fb; outline: none;
    }
    input[type="color"] { width: 100%; height: 40px; padding: 0; border: none; background: transparent; box-sizing: border-box; }
    .row { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--gap); }
    .actions { display: flex; gap: var(--gap); align-items: center; flex-wrap: wrap; }
    button { background: #5b8cff; color: white; border: none; padding: .8rem 1rem; border-radius: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 6px 16px rgba(91,140,255,.35); }
    button:hover { filter: brightness(1.05); }
    .errors { background: #2a1320; border: 1px solid #7f1d3a; padding: .75rem 1rem; border-radius: 12px; }
    .errors ul { margin: 0; padding-left: 1.1rem; }
    .hint { font-size: .85rem; color: #9fb3c8; }
    footer { margin-top: 1rem; color: #8aa2b6; font-size: .85rem; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>QR Vektor-Generator (SVG / EPS)</h1>
    <p class="muted">Erzeugt echte Vektoren via <code>endroid/qr-code</code>. Logo-Overlay & Fehlertoleranz einstellbar.</p>

    <?php if ($errors): ?>
      <div class="errors full">
        <strong>Bitte prüfen:</strong>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <label class="full">
        Inhalt (Text / URL)
        <input type="text" name="data" placeholder="https://beispiel.tld" value="<?= isset($_POST['data']) ? htmlspecialchars($_POST['data'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>" required />
      </label>

      <label>
        Format
        <select name="format">
          <option value="svg" <?= (($_POST['format'] ?? '') === 'svg') ? 'selected' : '' ?>>SVG (empfohlen)</option>
          <option value="eps" <?= (($_POST['format'] ?? '') === 'eps') ? 'selected' : '' ?>>EPS (Druck)</option>
        </select>
      </label>

      <label>
        Fehlertoleranz
        <select name="ec">
          <?php $ecSel = strtoupper($_POST['ec'] ?? 'M'); ?>
          <option value="L" <?= $ecSel==='L'?'selected':'' ?>>L (niedrig)</option>
          <option value="M" <?= $ecSel==='M'?'selected':'' ?>>M</option>
          <option value="Q" <?= $ecSel==='Q'?'selected':'' ?>>Q</option>
          <option value="H" <?= $ecSel==='H'?'selected':'' ?>>H (hoch, gut für Logos)</option>
        </select>
      </label>

      <label>
        Quiet Zone (Rand)
        <input type="number" name="margin" min="0" max="50" value="<?= (int)($_POST['margin'] ?? 2) ?>" />
      </label>

      <label>
        Größe (px)
        <input type="number" name="size" min="128" max="4096" value="<?= (int)($_POST['size'] ?? 512) ?>" />
      </label>

      <label>
        Vordergrund
        <input type="text" name="fg" value="<?= htmlspecialchars($_POST['fg'] ?? '#000000', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="#000000" />
        <div class="hint">Hex z.&nbsp;B. #000000</div>
      </label>

      <label>
        Hintergrund
        <input type="text" name="bg" value="<?= htmlspecialchars($_POST['bg'] ?? '#ffffff', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="#ffffff" />
        <div class="hint">Für transparentes SVG: #ffffff im Nachgang in Editor entfernen.</div>
      </label>

      <label class="full">
        Logo (optional)
        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif" />
      </label>

      <label>
        Logo-Breite (px, optional)
        <input type="number" name="logo_width" min="0" max="2048" value="<?= (int)($_POST['logo_width'] ?? 0) ?>" />
        <div class="hint">0 = Originalbreite</div>
      </label>

      <div class="actions full">
        <button type="submit">Generieren &amp; Download</button>
        <span class="hint">Tipp: Für Logos lieber EC=<strong>H</strong> & ausreichend Quiet Zone.</span>
      </div>
    </form>

    <footer>
      Hinweis: Für echtes PDF-Vektor-Output nutze ggf. einen SVG→PDF-Export in Inkscape/Illustrator (bleibt Vektor). EPS ist bereits für Druck geeignet.
    </footer>
  </div>
</body>
</html>
