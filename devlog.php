<?php
session_start();
$pageTitle = 'Devlog — Catnetic Storm';
$active = 'devlog';

require __DIR__ . '/includes/db.php';

// Zmienione zapytanie - dodano LEFT JOIN by pobrać autora i zliczanie komentarzy!
$stmt = $pdo->query("
    SELECT p.devlog_id, p.title, p.content, p.media_path, p.media_type, p.created_at, u.username,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.devlog_id) AS comments_count
    FROM devlog_posts p
    LEFT JOIN users u ON u.user_id = p.author_id
    ORDER BY p.created_at DESC, p.devlog_id DESC
");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section id="devlog-list" class="devlog">

        <?php if (!$posts): ?>
            <article class="post-card" tabindex="0">
                <div class="post-left">
                    <h2 class="post-title">BRAK POSTÓW</h2>
                    <p class="post-body">Dodaj pierwszy post do tabeli <strong>devlog_posts</strong>.</p>
                </div>
                <div class="post-right"></div>
            </article>
        <?php endif; ?>

        <?php foreach ($posts as $p): ?>
            <?php
            $id = (int)$p['devlog_id'];
            $title = (string)$p['title'];
            $body = (string)$p['content'];
            $media = (string)$p['media_path'];
            $type = (string)$p['media_type'];

            // Zabezpieczenie, jeśli post nie ma autora
            $author = $p['username'] ? (string)$p['username'] : 'Admin';
            $date = (string)$p['created_at'];
            $comCount = (int)$p['comments_count'];

            // Generowanie tekstu ze statystykami
            $metaText = "Napisał(a): <strong>" . htmlspecialchars($author) . "</strong> • " . htmlspecialchars($date) . " • Komentarze: <strong>" . $comCount . "</strong>";
            ?>
            <article class="post-card devlog-item" tabindex="0"
                     data-post-id="<?= $id ?>"
                     data-title="<?= htmlspecialchars($title) ?>"
                     data-body="<?= htmlspecialchars($body) ?>"
                     data-media="<?= htmlspecialchars($media) ?>"
                     data-type="<?= htmlspecialchars($type) ?>"
                     data-meta="<?= htmlspecialchars($metaText) ?>"
            >
                <div class="post-left">
                    <span class="forum-meta"><?= $metaText ?></span>
                    <h2 class="post-title"><?= htmlspecialchars($title) ?></h2>
                    <p class="post-body">
                        <?= nl2br(htmlspecialchars($body)) ?>
                    </p>
                </div>

                <div class="post-right">
                    <?php if ($type === 'image' && trim($media) !== ''): ?>
                        <img class="post-img media-trigger" data-type="image" data-src="<?= htmlspecialchars($media) ?>" src="<?= htmlspecialchars($media) ?>" alt="<?= htmlspecialchars('Grafika do wpisu: ' . $title) ?>" loading="lazy" style="cursor: zoom-in;" />
                    <?php elseif ($type === 'video' && trim($media) !== ''): ?>
                        <video class="forum-media-video media-trigger" data-type="video" data-src="<?= htmlspecialchars($media) ?>" loading="lazy" style="cursor: zoom-in;">
                            <source src="<?= htmlspecialchars($media) ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <section id="devlog-post" class="devlog is-hidden" aria-label="Szczegóły posta">
        <button class="back-btn" type="button" id="backToList"> ← WRÓĆ </button>

        <article class="post-card post-card--details">
            <div class="post-left">
                <span class="forum-meta" id="detailsMeta"></span>
                <h2 class="post-title" id="detailsTitle">NOWY POST</h2>
                <p class="post-body" id="detailsBody">...</p>
            </div>

            <div class="post-right">
                <img class="post-img media-trigger is-hidden" data-type="image" id="detailsImg" src="" alt="Grafika do wpisu devloga" style="cursor: zoom-in;" />
                <video class="forum-media-video media-trigger is-hidden" data-type="video" id="detailsVideo" controls style="cursor: zoom-in;">
                    <source src="" type="video/mp4">
                </video>
            </div>
        </article>

        <section class="comments">
            <div class="comments-head">KOMENTARZE</div>

            <div class="comments-list" id="commentsList" aria-label="Lista komentarzy"></div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form class="comment-form" id="commentForm">
                    <input class="comment-input comment-input--wide" id="commentText" name="text" type="text" placeholder="DODAJ KOMENTARZ..." required />
                    <button class="comment-btn" type="submit">DODAJ</button>
                </form>
            <?php else: ?>
                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">Zaloguj się, aby dodać komentarz.</div>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<div class="lightbox" id="devlog-lightbox">
    <button class="lightbox-close" id="dlb-close">&times;</button>
    <div class="lightbox-content">
        <img class="lightbox-img" id="dlb-img" src="" alt="" style="display: none;">
        <video class="lightbox-img" id="dlb-video" controls style="display: none;"></video>
    </div>
</div>

<script defer src="js/devlog.js"></script>
</body>
</html>