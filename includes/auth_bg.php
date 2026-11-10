<?php
// Animated background for the login / sign-up pages.
//  - If assets/img/bg.mp4 (or bg.webm) exists  -> plays as a looping, muted background video.
//  - Otherwise every picture named bg*.jpg / .png / .webp in assets/img becomes a slide
//    (bg.jpg, bg1.jpg, bg2.jpg ...) that fades and slowly zooms.
//  - If nothing exists, the dark blue/orange gradient from the CSS is shown.
$imgDir = dirname(__DIR__) . '/assets/img/';

$slides = [];
foreach (scandir($imgDir) ?: [] as $file) {            // case-insensitive: Bg1.PNG, bg2.jpg ...
    if (preg_match('/^bg.*\.(jpe?g|png|webp)$/i', $file)) { $slides[] = $file; }
}
sort($slides, SORT_NATURAL | SORT_FLAG_CASE);

$video = null;
foreach (['bg.mp4', 'bg.webm'] as $candidate) {
    if (is_file($imgDir . $candidate)) { $video = $candidate; break; }
}
?>
<div class="bg-stage" aria-hidden="true">
<?php if ($video): ?>
  <video class="bg-video" autoplay muted loop playsinline<?= $slides ? ' poster="assets/img/' . e($slides[0]) . '"' : '' ?>>
    <source src="assets/img/<?= e($video) ?>">
  </video>
<?php else: foreach ($slides as $i => $slide): ?>
  <div class="bg-slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('assets/img/<?= e($slide) ?>')"></div>
<?php endforeach; endif; ?>
  <div class="bg-overlay"></div>
</div>
<?php if (!$video && count($slides) > 1): ?>
<script src="assets/js/slideshow.js"></script>
<?php endif; ?>