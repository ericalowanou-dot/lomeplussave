document.addEventListener('click', function (e) {
    const button = e.target.closest?.('.like-button, .like-button-inline, .like-button-detail');
    if (!button) return;

    e.preventDefault();

    const articleId = button.getAttribute('data-article-id');
    if (!articleId) return;

    const likeIcon = button.querySelector('.like-icon, .like-icon-inline') || button.querySelector('i');
    const likeCount = document.getElementById(`count-js-${articleId}`);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(`/articles/${articleId}/like`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf || '',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ article_id: articleId }),
    })
        .then((response) => {
            const contentType = response.headers.get('content-type') || '';
            if (response.status === 401 || contentType.includes('text/html')) {
                const modalAuth = document.getElementById('modal-auth');
                if (modalAuth) {
                    modalAuth.style.display = 'flex';
                } else {
                    alert('Vous devez être connecté pour liker un article.');
                }
                return null;
            }
            return response.json();
        })
        .then((data) => {
            if (!data || !likeIcon) return;

            if (data.liked) {
                likeIcon.classList.add('liked');
                likeIcon.classList.remove('bi-heart');
                likeIcon.classList.add('bi-heart-fill');
            } else {
                likeIcon.classList.remove('liked');
                likeIcon.classList.add('bi-heart');
                likeIcon.classList.remove('bi-heart-fill');
            }

            if (likeCount) {
                const likeNumberElement = likeCount.querySelector('.like-number');
                if (likeNumberElement) {
                    likeNumberElement.textContent = `${data.likeCount}`;
                } else {
                    likeCount.textContent = `${data.likeCount}`;
                }
            }
        })
        .catch((error) => {
            console.error('Erreur lors de la requête AJAX :', error);
        });
});