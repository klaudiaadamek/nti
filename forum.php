<?php
session_start();
$pageTitle = 'Forum — Catnetic Storm';
$active = 'forum';

require __DIR__ . '/includes/db.php';

$errors = [];
$successMessage = '';

// 1. Obsługa dodawania nowego posta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $userId = (int)$_SESSION['user_id'];

    if ($title === '') $errors['title'] = 'Podaj tytuł posta.';
    if ($content === '') $errors['content'] = 'Treść posta nie może być pusta.';

    $mediaPath = null;
    $mediaType = 'none';

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileSize = $_FILES['media']['size'];
        $fileMime = mime_content_type($fileTmp);

        $allowedImages = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/webm'];
        $maxSize = 50 * 1024 * 1024; // 50 MB

        if ($fileSize > $maxSize) {
            $errors['media'] = 'Plik jest za duży (maks. 50MB).';
        } else {
            if (in_array($fileMime, $allowedImages)) {
                $mediaType = 'image';
            } elseif (in_array($fileMime, $allowedVideos)) {
                $mediaType = 'video';
            } else {
                $errors['media'] = 'Niedozwolony format. Akceptowane: JPG, PNG, GIF, WEBP, MP4, WEBM.';
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('forum_', true) . '.' . $ext;
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    $mediaPath = 'uploads/' . $newFileName;
                } else {
                    $errors['media'] = 'Błąd zapisu pliku.';
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO forum_posts (user_id, title, content, media_path, media_type) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$userId, $title, $content, $mediaPath, $mediaType])) {
            header("Location: forum.php");
            exit;
        } else {
            $errors['general'] = 'Wystąpił błąd przy zapisie do bazy.';
        }
    }
}

// 2. Pobieranie postów dla widoku głównego
$stmt = $pdo->query("
    SELECT f.post_id, f.title, f.content, f.media_path, f.media_type, f.created_at, u.username 
    FROM forum_posts f
    JOIN users u ON f.user_id = u.user_id
    ORDER BY f.created_at DESC
");
$posts = $stmt->fetchAll();

// 3. Pobieranie liczby postów zalogowanego użytkownika
$userPostCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM forum_posts WHERE user_id = ?");
    $stmtCount->execute([$_SESSION['user_id']]);
    $userPostCount = (int)$stmtCount->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="forum-main-wrapper">
    <div class="forum-grid">

        <div class="forum-main">

            <section id="forum-list" class="devlog">
                <?php if (!$posts): ?>
                    <article class="post-card" tabindex="0">
                        <div class="post-left">
                            <h2 class="post-title">PUSTO!</h2>
                            <p class="post-body">Bądź pierwszą osobą, która coś tu napisze.</p>
                        </div>
                        <div class="post-right"></div>
                    </article>
                <?php endif; ?>

                <?php foreach ($posts as $p): ?>
                    <?php
                    $id = (int)$p['post_id'];
                    $title = (string)$p['title'];
                    $body = (string)$p['content'];
                    $media = (string)$p['media_path'];
                    $type = (string)$p['media_type'];
                    $author = (string)$p['username'];
                    $date = (string)$p['created_at'];
                    ?>
                    <article class="post-card forum-item" tabindex="0"
                             data-post-id="<?= $id ?>"
                             data-title="<?= htmlspecialchars($title) ?>"
                             data-body="<?= htmlspecialchars($body) ?>"
                             data-media="<?= htmlspecialchars($media) ?>"
                             data-type="<?= htmlspecialchars($type) ?>"
                             data-meta="<?= htmlspecialchars($author . ' • ' . $date) ?>"
                    >
                        <div class="post-left">
                            <span class="forum-meta">Napisał(a): <strong><?= htmlspecialchars($author) ?></strong> • <?= htmlspecialchars($date) ?></span>
                            <h2 class="post-title"><?= htmlspecialchars($title) ?></h2>
                            <p class="post-body">
                                <?= nl2br(htmlspecialchars($body)) ?>
                            </p>
                        </div>

                        <div class="post-right">
                            <?php if ($type === 'image' && trim($media) !== ''): ?>
                                <img class="post-img" src="<?= htmlspecialchars($media) ?>" alt="Załącznik" loading="lazy" />
                            <?php elseif ($type === 'video' && trim($media) !== ''): ?>
                                <video class="forum-media-video" loading="lazy">
                                    <source src="<?= htmlspecialchars($media) ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <section id="forum-post-details" class="devlog is-hidden" aria-label="Szczegóły posta forum">
                <button class="back-btn" type="button" id="forumBackBtn"> ← WRÓĆ </button>

                <article class="post-card post-card--details">
                    <div class="post-left">
                        <span class="forum-meta" id="f-detailsMeta"></span>
                        <h2 class="post-title" id="f-detailsTitle">TYTUŁ</h2>
                        <p class="post-body" id="f-detailsBody">...</p>
                    </div>

                    <div class="post-right" id="f-detailsMediaContainer">
                        <img class="post-img" id="f-detailsImg" src="" alt="Załącznik" hidden />
                        <video class="forum-media-video" id="f-detailsVideo" controls hidden>
                            <source src="" type="video/mp4">
                        </video>
                    </div>
                </article>

                <section class="comments">
                    <div class="comments-head">KOMENTARZE</div>
                    <div class="comments-list" id="f-commentsList" aria-label="Lista komentarzy"></div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form class="comment-form" id="f-commentForm">
                            <input class="comment-input comment-input--wide" type="text" placeholder="DODAJ KOMENTARZ..." required />
                            <button class="comment-btn" type="submit">DODAJ</button>
                        </form>
                    <?php endif; ?>
                </section>
            </section>

        </div>

        <aside class="forum-sidebar">

            <div class="welcome-box">
                <p class="welcome-text">
                    Witamy na naszym forum! Tutaj możesz się dzielić swoimi przemyśleniami na temat naszej gry. Zachęcamy również do wstawiania zdjęć z ZTGK z naszego stoiska. Jeśli grałeś w naszą grę - będziemy wdzięczni za wszelkie opinie i rady! &lt;3
                </p>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form class="auth-card" action="forum.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; width: 100%; max-width: none;">
                    <div class="auth-header" style="font-size: 1.1rem;">DODAJ POST</div>
                    <?php if (!empty($errors['general'])): ?><div class="auth-error" style="display:block; margin-bottom: 10px;"><?= htmlspecialchars($errors['general']) ?></div><?php endif; ?>

                    <div class="auth-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
                        <input class="auth-input" name="title" type="text" placeholder="TYTUŁ POSTA" required />
                        <div class="auth-error"><?= htmlspecialchars($errors['title'] ?? '') ?></div>
                    </div>

                    <div class="auth-field <?= isset($errors['content']) ? 'has-error' : '' ?>">
                        <textarea class="auth-input" name="content" placeholder="Treść..." rows="8" style="resize:vertical; min-height: 150px;" required></textarea>
                        <div class="auth-error"><?= htmlspecialchars($errors['content'] ?? '') ?></div>
                    </div>

                    <div class="auth-field <?= isset($errors['media']) ? 'has-error' : '' ?>">
                        <label class="auth-label" for="postMedia" style="font-size:0.75rem;">ZDJĘCIE / WIDEO (OPCJ.)</label>
                        <input type="file" id="postMedia" name="media" accept="image/*,video/mp4,video/webm" style="color: #fff; margin-top: 5px; font-family: inherit; width: 100%; font-size: 0.8rem;" />
                        <div class="auth-error"><?= htmlspecialchars($errors['media'] ?? '') ?></div>
                    </div>

                    <div class="auth-actions" style="margin-top: 10px;">
                        <button class="auth-btn" type="submit">OPUBLIKUJ</button>
                    </div>
                </form>

                <div class="sidebar-box post-stats" style="padding: 0;">
                    <a href="forum_manage.php" class="post-stats-link" style="padding: 20px;" title="Kliknij, aby zarządzać">
                        Liczba Twoich postów:
                        <span class="post-stats-number"><?= $userPostCount ?></span>
                        <span style="font-size: 0.8rem; color: #d8b4fe; text-decoration: underline; margin-top: 5px; display: block;">Zarządzaj swoimi postami</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="auth-card" style="text-align: center; width: 100%; max-width: none;">
                    <p>Zaloguj się, aby brać udział w dyskusjach.</p>
                    <a class="auth-btn auth-btn--link" href="login.php" style="display:inline-block; margin-top:10px;">ZALOGUJ SIĘ</a>
                </div>
            <?php endif; ?>

        </aside>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<div class="lightbox" id="forum-lightbox">
    <button class="lightbox-close" id="flb-close">&times;</button>
    <div class="lightbox-content">
        <img class="lightbox-img" id="flb-img" src="" alt="">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const viewList = document.getElementById('forum-list');
        const viewDetails = document.getElementById('forum-post-details');
        const btnBack = document.getElementById('forumBackBtn');
        const posts = document.querySelectorAll('.forum-item');

        const dMeta = document.getElementById('f-detailsMeta');
        const dTitle = document.getElementById('f-detailsTitle');
        const dBody = document.getElementById('f-detailsBody');
        const dImg = document.getElementById('f-detailsImg');
        const dVideo = document.getElementById('f-detailsVideo');

        let currentPostId = null;
        const commentsList = document.getElementById('f-commentsList');
        const commentForm = document.getElementById('f-commentForm');

        const lightbox = document.getElementById('forum-lightbox');
        const lbImg = document.getElementById('flb-img');
        const btnClose = document.getElementById('flb-close');

        async function loadComments(postId) {
            commentsList.innerHTML = '<div style="opacity: 0.5; padding: 10px;">Ładowanie komentarzy...</div>';
            try {
                const res = await fetch(`api/forum_comments.php?post_id=${postId}`);
                const data = await res.json();

                if (data.error) {
                    commentsList.innerHTML = `<div style="color: #ff6b6b; padding: 10px;">${data.error}</div>`;
                    return;
                }

                if (data.length === 0) {
                    commentsList.innerHTML = '<div style="opacity: 0.5; padding: 10px;">Brak komentarzy. Bądź pierwszy!</div>';
                    return;
                }

                commentsList.innerHTML = '';
                data.forEach(c => {
                    const div = document.createElement('div');
                    div.style.padding = '12px 0';
                    div.style.borderBottom = '1px solid rgba(255,255,255,0.1)';

                    const safeContent = c.content.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    const heartStyle = c.user_liked > 0 ? 'color: #ff4757; opacity: 1;' : 'color: #fff; opacity: 0.5;';

                    div.innerHTML = `
                          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                              <div style="flex-grow: 1; padding-right: 15px;">
                                  <div style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 6px;">
                                      <strong>${c.username}</strong> • ${c.created_at}
                                  </div>
                                  <div style="line-height: 1.4;">${safeContent}</div>
                              </div>
                              <button class="forum-like-btn" data-id="${c.comment_id}" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; gap: 5px; transition: all 0.2s; ${heartStyle}">
                                  ❤ <span class="like-count" style="font-size: 1rem;">${c.likes || 0}</span>
                              </button>
                          </div>
                      `;
                    commentsList.appendChild(div);
                });
            } catch (err) {
                commentsList.innerHTML = '<div style="color: #ff6b6b; padding: 10px;">Błąd łączenia z serwerem.</div>';
            }
        }

        if (commentForm) {
            commentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!currentPostId) return;

                const input = commentForm.querySelector('.comment-input');
                const text = input.value.trim();
                if (!text) return;

                const formData = new FormData();
                formData.append('post_id', currentPostId);
                formData.append('content', text);

                const btn = commentForm.querySelector('.comment-btn');
                btn.disabled = true;
                btn.textContent = '...';

                try {
                    const res = await fetch('api/forum_comments.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        input.value = '';
                        await loadComments(currentPostId);
                    } else {
                        alert(data.error || 'Wystąpił błąd podczas dodawania komentarza.');
                    }
                } catch (err) {
                    alert('Błąd połączenia z serwerem.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'DODAJ';
                }
            });
        }

        commentsList.addEventListener('click', async (e) => {
            const btn = e.target.closest('.forum-like-btn');
            if (!btn) return;

            const commentId = btn.getAttribute('data-id');
            try {
                const res = await fetch('api/like_forum_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ comment_id: commentId })
                });
                const data = await res.json();

                if (data.error) {
                    alert(data.error);
                    return;
                }

                if (data.success) {
                    const countSpan = btn.querySelector('.like-count');
                    countSpan.textContent = data.likes;
                    if (data.action === 'liked') {
                        btn.style.color = '#ff4757';
                        btn.style.opacity = '1';
                    } else {
                        btn.style.color = '#fff';
                        btn.style.opacity = '0.5';
                    }
                }
            } catch (err) {}
        });

        posts.forEach(post => {
            post.addEventListener('click', (e) => {
                if (e.target.tagName === 'IMG' || e.target.tagName === 'VIDEO') return;

                currentPostId = post.getAttribute('data-post-id');
                const title = post.getAttribute('data-title');
                const body = post.getAttribute('data-body');
                const media = post.getAttribute('data-media');
                const type = post.getAttribute('data-type');
                const meta = post.getAttribute('data-meta');

                dTitle.textContent = title;
                dBody.innerHTML = body.replace(/\n/g, '<br>');
                dMeta.innerHTML = meta;

                dImg.hidden = true;
                dVideo.hidden = true;
                dVideo.pause();

                if (type === 'image' && media) {
                    dImg.src = media;
                    dImg.hidden = false;
                } else if (type === 'video' && media) {
                    dVideo.querySelector('source').src = media;
                    dVideo.load();
                    dVideo.hidden = false;
                }

                loadComments(currentPostId);

                viewList.classList.add('is-hidden');
                viewDetails.classList.remove('is-hidden');

                document.querySelector('.forum-grid').scrollIntoView({ behavior: 'smooth' });
            });
        });

        btnBack.addEventListener('click', () => {
            viewDetails.classList.add('is-hidden');
            viewList.classList.remove('is-hidden');
            dVideo.pause();
            currentPostId = null;
        });

        document.body.addEventListener('click', (e) => {
            if (e.target.classList.contains('post-img')) {
                e.stopPropagation();
                lbImg.src = e.target.src;
                lbImg.alt = e.target.alt || 'Powiększone zdjęcie';
                lightbox.classList.add('active');
            }
        });

        function closeLightbox() {
            lightbox.classList.remove('active');
        }

        btnClose.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.classList.contains('lightbox-content')) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.classList.contains('active')) closeLightbox();
        });
    });
</script>
</body>
</html>