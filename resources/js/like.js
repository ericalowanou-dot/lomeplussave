document.addEventListener("DOMContentLoaded", function () {
    // Sélectionne tous les boutons "like" (ancien style et nouveau style inline)
    const likeButtons = document.querySelectorAll(".like-button, .like-button-inline");

    likeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const articleId = this.getAttribute("data-article-id");
            // Cherche l'icône dans les deux formats possibles
            const likeIcon = this.querySelector(".like-icon, .like-icon-inline");
            const likeCount = document.getElementById(`count-js-${articleId}`);

            // Envoie la requête AJAX
            fetch(`/articles/${articleId}/like`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ article_id: articleId }),
            })
                .then((response) => {
                    // Vérifie si la réponse est du JSON ou du HTML (redirection login)
                    const contentType = response.headers.get("content-type") || "";
                    if (response.status === 401 || contentType.includes("text/html")) {
                        const modalAuth = document.getElementById("modal-auth");
                        if (modalAuth) {
                            modalAuth.style.display = "flex";
                        } else {
                            alert("Vous devez être connecté pour liker un article.");
                        }
                        return null;
                    }
                    return response.json();
                })
                .then((data) => {
                    if (!data) return; // Si data est null (redirection login), on arrête
                    
                    if (data.liked) {
                        likeIcon.classList.add("liked");
                        likeIcon.classList.remove("bi-heart");
                        likeIcon.classList.add("bi-heart-fill");
                    } else {
                        likeIcon.classList.remove("liked");
                        likeIcon.classList.add("bi-heart");
                        likeIcon.classList.remove("bi-heart-fill");
                    }

                    if (likeCount) {
                        // Si le compteur a un élément .like-number (structure inline), on met à jour celui-ci
                        const likeNumberElement = likeCount.querySelector('.like-number');
                        if (likeNumberElement) {
                            likeNumberElement.textContent = `${data.likeCount}`;
                        } else {
                            likeCount.textContent = `${data.likeCount}`;
                        }
                    }
                })
                .catch((error) => {
                    console.error("Erreur lors de la requête AJAX :", error);
                });
        });
    });
});