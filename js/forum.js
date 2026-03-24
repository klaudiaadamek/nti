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